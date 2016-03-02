<?php

namespace CTO\AppBundle\Form\DTO;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportFilterDTOType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateFrom', TextType::class, [
                'attr' => ['placeholder' => 'Експорт З', 'class' => 'form-control date-picker-cto']
            ])
            ->add('dateTo', TextType::class, [
                'attr' => ['placeholder' => 'Експорт ПО', 'class' => 'form-control date-picker-cto']
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
            'data_class' => 'CTO\AppBundle\Entity\DTO\ExportFilterDTO',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "export_filter";
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }
}
