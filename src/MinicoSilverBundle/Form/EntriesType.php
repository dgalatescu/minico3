<?php

namespace MinicoSilverBundle\Form;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class EntriesType extends AbstractType
{

    private $em = null;
    private $productId = null;

//    public function __construct($productId = null)
//    public function __construct()
//    {
//        $this->em = $em;
//        $this->productId = null;
//    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->em = $options['entity_manager'];
        $arr = array(
            'class' => 'MinicoSilverBundle:Products',
            'property'=>'productCode',
            'label' => 'Product',
            'required' => true,
        );

        if ($this->productId != null) {
            $arr ['data'] = $this
                ->em
                ->getReference(
                    "MinicoSilverBundle:Products",
                    $this->productId
                );
        }

        $builder
//            ->add(
//                'productId',
//                EntityType::class
//                $arr
//            )
            ->add(
                'storage',
                EntityType::class,
                array(
                    'class' => 'MinicoSilverBundle:Storage',
                    'query_builder' =>
                        function (EntityRepository $er) {
                            return $er
                                ->createQueryBuilder('s')
                                ->where('s.mainStorage=:mainStorage')
                                ->setParameter('mainStorage', 1)
                                ->orderBy('s.name', 'ASC');
                        },
                    'data' => null
                )
            )
            ->add('quantity')
//            ->add('save', SubmitType::class)
            ->add('submit', SubmitType::class, array('label' => 'Save'));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MinicoSilverBundle\Entity\Entries'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'minico_silverbundle_entries';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setRequired('entity_manager');
    }
}
