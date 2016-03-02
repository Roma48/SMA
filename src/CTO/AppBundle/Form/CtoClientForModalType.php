<?php

namespace CTO\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CtoClientForModalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $option)
    {
        $builder
            ->add("firstName", 'text', [
                'label' => "Ім'я: *",
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
            ->add('lastName', 'text', [
                'label' => 'Прізвище:',
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
            ->add('phone', 'text', [
                'label' => 'Телефон: *',
                'attr' => ['class' => 'form-control', 'placeholder' => '(050) 123-45-67'],
                'required' => false
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'CTO\AppBundle\Entity\CtoClient'
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return "client_modal";
    }
}
