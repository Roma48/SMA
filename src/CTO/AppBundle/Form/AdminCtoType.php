<?php

namespace CTO\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminCtoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $option)
    {
        $builder
            ->add('firstName', 'text', [
                'label' => 'Імя: *',
                'attr' => ["class" => "form-control"],
                'required' => false
            ])
            ->add('lastName', 'text', [
                'label' => 'Прізвище:',
                'attr' => ["class" => "form-control"],
                'required' => false
            ])
            ->add('phone', 'text', [
                'label' => 'Телефон: *',
                'attr' => ["class" => "form-control", 'placeholder' => '(050) 123-45-67'],
                'required' => false
            ])
            ->add('plain_password', 'repeated', [
                'label' => 'Пароль:',
                'type' => 'password',
                'invalid_message' => 'Введені паролі не співпадають.',
                'options' => ['attr' => ['class' => 'form-control margin-bottom-15']],
                'required' => false,
                'first_options'  => ['label' => 'Пароль:*'],
                'second_options' => ['label' => 'Повторіть пароль:*']
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'CTO\AppBundle\Entity\AdminUser'
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'admin';
    }
}
