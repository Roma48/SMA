<?php

namespace CTO\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CtoClientFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullName', 'text', [
                'attr' => ['placeholder' => 'Клієнт', 'class' => 'form-control']
            ])
            ->add('phone', 'text', [
                'attr' => ['placeholder' => 'Телефон', 'class' => 'form-control']
            ])
            ->add('dateFrom', 'text', [
                'attr' => ['placeholder' => 'Дата замовлення З', 'class' => 'form-control date-picker-cto']
            ])
            ->add('dateTo', 'text', [
                'attr' => ['placeholder' => 'Дата замовлення ПО', 'class' => 'form-control date-picker-cto']
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return "cto_client_filter";
    }
}
