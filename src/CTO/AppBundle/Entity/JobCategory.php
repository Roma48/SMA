<?php

namespace CTO\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class JobCategory
 *
 * @ORM\Table(name="jobCategory")
 * @ORM\Entity(repositoryClass="CTO\AppBundle\Entity\Repository\JobCategoryRepository")
 */
class JobCategory implements \JsonSerializable
{
    use CreateUpdateTrait;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Обов'язкове поле")
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="CTO\AppBundle\Entity\CarCategory", mappedBy="jobCategory", cascade={"persist"})
     */
    protected $carJobs;

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
            "id" => (string) $this->getId(),
            "name" => $this->getName()
        ];
    }

    public function __construct()
    {
        $this->carJobs = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return JobCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCarJobs()
    {
        return $this->carJobs;
    }

    /**
     * @param Collection $carJobs
     */
    public function setCarJobs($carJobs)
    {
        $this->carJobs = $carJobs;
    }

    /**
     * @param CarJob $carJob
     * @return JobCategory
     */
    public function addCarJob(CarJob $carJob)
    {
        $this->carJobs->add($carJob);

        return $this;
    }

    public function removeCarJob(CarJob $carJob)
    {
        $this->carJobs->removeElement($carJob);
    }
}
