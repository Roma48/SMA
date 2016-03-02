<?php

namespace CTO\AppBundle\Form;

use CTO\AppBundle\Entity\CarJob;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class JobNotificationReminderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $option)
    {
        $builder
            ->add('jobCategory', 'entity', [
                'class' => 'CTO\AppBundle\Entity\JobCategory',
                'label' => 'Категорія ',
                'property' => 'name',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', 'textarea', [
                'attr' => ['class' => 'form-control notification-description'],
                'label' => 'Повідомлення *',
                'constraints' => [
                    new NotBlank(['message' => "Це поле обов'язкове"])
                ]
            ])
            ->add('whenSend', "datetime", [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control datetime-picker-cto'],
                'format' => 'dd.MM.yyyy HH:mm',
                'label' => 'Дата нагадування *',
                'constraints' => [
                    new NotBlank(['message' => "Це поле обов'язкове"])
                ]
            ])
            ->add('autoSending', 'checkbox' ,[
                'attr' => ['class' => ''],
                'label' => 'Відправити автоматично',
                'required' => false
            ])
            ->add('adminCopy', 'checkbox', [
                'attr' => ['class' => ''],
                'label' => 'Відправити копію адміністратору',
                'required' => false
            ])
            ->add('sendNow', 'checkbox', [
                'attr' => ['class' => ''],
                'label' => 'Відправити негайно',
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
            'data_class' => 'CTO\AppBundle\Entity\Notification'
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'job_reminder';
    }
}
