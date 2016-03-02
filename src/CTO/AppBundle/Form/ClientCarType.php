<?php

namespace CTO\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientCarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $option)
    {
        $builder
            ->add('model', 'entity', [
                'label' => 'Модель автомобіля: *',
                'class' => 'CTOAppBundle:Model',
                'attr'  => [
                    'class'         => 'selectpicker',
                    'data-width'    => "100%",
                    'data-size'     => "15",
                    'data-live-search' => true
                ],
                'property' => 'name'

            ])
            ->add('carNumber', 'text', [
                'label'     => 'Номер автомобіля:',
                'attr'      => ['class' => 'form-control'],
                'required'  => false
            ])
            ->add('carColor', 'text', [
                'label'     => 'Колір автомобіля:',
                'attr'      => ['class' => 'form-control'],
                'required'  => false
            ])
            ->add('engine', 'text', [
                'label'     => 'Об\'єм двигуна (л.):',
                'attr'      => ['class' => 'form-control'],
                'required'  => false
            ])
            ->add('vinCode', 'text', [
                'label'     => 'VIN код:',
                'attr'      => ['class' => 'form-control'],
                'required'  => false
            ])
            ->add('createYear', 'text', [
                'label'     => 'Рік випуску:',
                'attr'      => ['class' => 'form-control'],
                'required'  => false
            ])
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'CTO\AppBundle\Entity\ClientCar'
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'client_car';
    }
}
