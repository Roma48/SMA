<?php

namespace CTO\AppBundle\Entity\DTO;

class FinancialArchive
{
    protected $year;

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param mixed $year
     * @return FinancialArchive
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }
}
