<?php

namespace CTO\AppBundle\Form\DTO;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatisticFilterDTOType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', EntityType::class, [
                "class" => 'CTOAppBundle:JobCategory',
                "choice_label" => "name",
                "empty_value" => "Виберіть категорію"
            ])
            ->add('dateFrom', TextType::class, [
                'attr' => ['placeholder' => 'Дата замовлення З', 'class' => 'form-control date-picker-cto']
            ])
            ->add('dateTo', TextType::class, [
                'attr' => ['placeholder' => 'Дата замовлення ПО', 'class' => 'form-control date-picker-cto']
            ])
            ->add("masters", EntityType::class, [
                "class" => 'CTOAppBundle:Master',
                "choice_label" => "fullName",
                "multiple" => true,
                "expanded" => false
            ])
        ;

    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'CTO\AppBundle\Entity\DTO\StatisticFilterDTO',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "stat_filter";
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }
}
