<?php

namespace CTO\AppBundle\Entity\DTO;

use Doctrine\Common\Collections\Collection;

class StatisticsMastersFilterDTO
{
    protected $dateFrom;
    protected $dateTo;
    protected $masters;

    /**
     * @return mixed
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @param mixed $dateFrom
     */
    public function setDateFrom($dateFrom)
    {
        $this->dateFrom = $dateFrom;
    }

    /**
     * @return mixed
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * @param mixed $dateTo
     */
    public function setDateTo($dateTo)
    {
        $this->dateTo = $dateTo;
    }

    /**
     * @return Collection
     */
    public function getMasters()
    {
        return $this->masters;
    }

    /**
     * @param mixed $masters
     */
    public function setMasters($masters)
    {
        $this->masters = $masters;
    }


}
