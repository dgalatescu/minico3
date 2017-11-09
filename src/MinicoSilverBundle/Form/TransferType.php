<?php

namespace MinicoSilverBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class TransferType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('creationDate')
            //->add('modified')
            ->add('product')
            ->add(
                'fromStorage',
                null,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
            ->add('qty')
            ->add(
                'toStorage',
                null,
                array(
                    'constraints' => array(
                        new NotBlank(),
                    ),
                )
            )
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MinicoSilverBundle\Entity\Transfer'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'minico_silverbundle_transfer';
    }
}
