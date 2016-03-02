<?php

namespace CTO\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class PaidSalaryJob
 *
 * @ORM\Table(name="paidSalaryJob")
 * @ORM\Entity(repositoryClass="CTO\AppBundle\Entity\Repository\PaidSalaryJobRepository")
 */
class PaidSalaryJob implements \JsonSerializable
{
    use CreateUpdateTrait;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @Assert\NotBlank(message="Обов'язкове поле")
     * @Assert\Type(
     *   type="numeric",
     *   message="only float allowed"
     * )
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    protected $price;

    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\Master")
     */
    protected $master;

    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\CarJob", inversedBy="paidSalaryJob")
     */
    protected $carJob;

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return [
            "description" => $this->getDescription(),
            "master" => (string) $this->getMaster()->getId(),
            "price" => $this->getPrice()
        ];
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return PaidSalaryJob
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     * @return PaidSalaryJob
     */
    public function setPrice($price)
    {
        $this->price = str_replace(',', '.', $price);

        return $this;
    }

    /**
     * @return CarJob
     */
    public function getCarJob()
    {
        return $this->carJob;
    }

    /**
     * @param CarJob $carJob
     * @return PaidSalaryJob
     */
    public function setCarJob(CarJob $carJob)
    {
        $this->carJob = $carJob;

        return $this;
    }


    /**
     * @return Master
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     * @param Master $master
     * @return PaidSalaryJob
     */
    public function setMaster(Master $master)
    {
        $this->master = $master;

        return $this;
    }
}
