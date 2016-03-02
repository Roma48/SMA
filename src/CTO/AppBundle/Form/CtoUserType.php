<?php

namespace CTO\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CtoUserType extends AbstractType
{
    /** @var bool $type */
    protected $type;

    public function __construct($type = false)
    {
        $this->type = $type;
    }

    public function buildForm(FormBuilderInterface $builder, array $option)
    {
        // type === true -> new || type === false -> edit
        $constraints = $this->type ? [
            new NotBlank(['message' => 'Обов\'язкове поле.']),
            new Length(['min' => 3, 'minMessage' => 'Мінімална довжина паролю {{ limit }} символів.'])
        ] : [
            new Length(['min' => 3, 'minMessage' => 'Мінімална довжина паролю {{ limit }} символів.'])
        ];
        $builder
            ->add('firstName', 'text', [
                'label' => 'Імя відповідальної особи студії:*',
                'attr' => ["class" => "form-control"],
                'required' => false
            ])
            ->add('lastName', 'text', [
                'label' => 'Прізвище відповідальної особи студії:',
                'attr' => ["class" => "form-control"],
                'required' => false
            ])
            ->add('email', 'email', [
                'label' => 'e-mail студії:*',
                'attr' => ["class" => "form-control"],
                'disabled' => $this->type ? false : true,
                'required' => false
            ])
            ->add('phone', 'text', [
                'label' => 'Телефон студії:*',
                'attr' => ["class" => "form-control", 'placeholder' => '(050) 123-45-67'],
                'required' => false
            ])
            ->add('ctoName', 'text', [
                'label' => 'Назва студії:*',
                'attr' => ["class" => "form-control"],
                'required' => false
            ])
            ->add('blocked', 'checkbox', [
                'label' => 'Заблоковано:',
                'required' => false
            ])
            ->add('publicProfile', 'checkbox', [
                'label' => 'Публічний профайл:',
                'required' => false
            ])
            ->add('city', 'entity', [
                'class' => 'CTOAppBundle:City',
                'label' => 'Місто',
                'property' => 'name',
                'attr'  => [
                    'class'         => 'selectpicker',
                    'data-width'    => "100%",
                    'data-size'     => "15",
                    'data-live-search' => true
                ],
            ])
            ->add('plain_password', 'repeated', [
                'label' => 'Пароль:*',
                'type' => 'password',
                'invalid_message' => 'Введені паролі не співпадають.',
                'options' => ['attr' => ['class' => 'form-control margin-bottom-15']],
                'required' => false,
                'first_options'  => ['label' => 'Пароль:*'],
                'second_options' => ['label' => 'Повторіть пароль:*'],
                'constraints' => $constraints
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'CTO\AppBundle\Entity\CtoUser'
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return "cto_user";
    }
}
