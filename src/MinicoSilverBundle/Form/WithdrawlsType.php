<?php

namespace MinicoSilverBundle\Form;

use Doctrine\ORM\EntityRepository;
use MinicoSilverBundle\Entity\ProductsRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class WithdrawlsType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity')
            ->add('productId')
            ->add(
                'storage',
                'entity',
                array(
                    'class' => 'MinicoSilverBundle:Storage',
                    'query_builder' => function(EntityRepository $er) {
                        return $er
                            ->createQueryBuilder('s')
                            ->where('s.mainStorage=:mainStorage')
                            ->setParameter('mainStorage', 1)
                            ->orderBy('s.name', 'ASC');
                    },
                )
            );
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MinicoSilverBundle\Entity\Withdrawls'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'minico_silverbundle_withdrawls';
    }
}
