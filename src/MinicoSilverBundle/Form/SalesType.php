<?php

namespace MinicoSilverBundle\Form;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use MinicoSilverBundle\Entity\ProductsRepository;
use MinicoSilverBundle\Entity\Sales;
use MinicoSilverBundle\Entity\Storage;
use MinicoSilverBundle\Service\StorageService;
use Proxies\__CG__\MinicoSilverBundle\Entity\Products;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
//use Genemu\Bundle\FormBundle\GenemuFormBundle;

class SalesType extends AbstractType
{
    /** @var  EntityManager */
    private $em;
    /** @var  StorageService */
    private $storageService;

    public function __construct(EntityManager $em, StorageService $storageService){
        $this->em = $em;
        $this->storageService = $storageService;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'date',
                'genemu_jquerydate',
                array(
                    'widget' => 'single_text'
                )
            )
            ->add(
                'fromStorage',
                'entity',
                array(
                    'class' => 'MinicoSilverBundle:Storage',
                    'query_builder' => function(EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('s')
                            ->where('s.sellingStorage=:sellingStorage')
                            ->setParameter('sellingStorage', 1)
                            ->orderBy('s.name', 'ASC');
                    },
                    'empty_value' => 'Choose an option',
                    'property' => 'name',
                )
            );

        $builder
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) {
                    $form = $event->getForm();

                    // this would be your entity, i.e. SportMeetup
                    /** @var Sales $sale */
                    $sale = $event->getData();
                    $em = $this->em;

                    $storageId = $sale['fromStorage'];

                    if (!$storageId) {
                        return;
                    }

                    $products = $this->storageService->getProductsForForm($storageId);

                    $form
                        ->add(
                            'productId',
                            'entity',
                            array(
                                'class' => 'MinicoSilverBundle:Products',
                                'choices'     => $products,
                                'empty_value' => 'Choose an option',
                            )
                        )
                        ->add('quantity');
                }
            );
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MinicoSilverBundle\Entity\Sales'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'minico_silverbundle_sales';
    }
}
