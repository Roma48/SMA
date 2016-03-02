<?php

namespace CTO\AppBundle\Form\DTO;

use Carbon\Carbon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FinancialArchiveYearType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $option
     */
    public function buildForm(FormBuilderInterface $builder, array $option)
    {
        $builder
            ->add('year', 'choice', [
                'label' => 'Звіт за:',
                'attr' => ["class" => "form-control"],
                'choices' => $this->getYears()
            ])
        ;
    }

    /**
     * @return array
     */
    private function getYears()
    {
        $startYear = Carbon::createFromDate(2014);
        $nextYear = Carbon::now()->addYears(3);
        $result = [];

        while ($startYear->year <= $nextYear->year) {
            $result[$startYear->year] = $startYear->year;
            $startYear->addYear();
        }

        return $result;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'finance';
    }
}
