<?php

namespace CTO\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CarCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $option)
    {
        $builder
            ->add('jobCategory', 'entity', [
                'class' => 'CTOAppBundle:JobCategory',
                'property' => 'name',
                'label' => 'Категорія *',
                'attr'  => [
                    'class'         => 'form-control',
//                    'class'         => 'selectpicker',
//                    'data-width'    => "100%",
//                    'data-size'     => "15",
//                    'data-live-search' => true
                ],
            ])
            ->add('master', 'entity', [
                'class' => 'CTO\AppBundle\Entity\Master',
                'property' => 'fullName',
                'label' => 'Мастер *',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('jobDescriptions', 'collection', [
                'type' => new JobDescriptionType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'cascade_validation' => true
            ])
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'CTO\AppBundle\Entity\CarCategory',
            'cascade_validation' => true
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'car_cat';
    }
}
