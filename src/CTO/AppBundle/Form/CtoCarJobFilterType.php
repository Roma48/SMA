<?php

namespace CTO\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CtoCarJobFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullName', 'text', [
                'attr' => ['placeholder' => 'Клієнт', 'class' => 'form-control']
            ])
//            ->add("jobCategory", 'entity', [
//                'class' => "CTOAppBundle:JobCategory",
//                'empty_value' => "Не вибрано",
//                'property' => 'name',
//                'attr'  => [
//                    'class'         => 'selectpicker',
//                    'data-width'    => "auto",
////                    'data-size'     => "15",
////                    'data-live-search' => true
//                ],
//            ])
            ->add('dateFrom', 'text', [
                'attr' => ['placeholder' => 'Дата З', 'class' => 'form-control date-picker-cto']
            ])
            ->add('dateTo', 'text', [
                'attr' => ['placeholder' => 'Дата ПО', 'class' => 'form-control date-picker-cto']
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
        return "job_filter";
    }
}
