<?php

namespace CTO\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CarCategory
 *
 * @ORM\Table(name="carCategory")
 * @ORM\Entity()
 */
class CarCategory implements \JsonSerializable
{
    use CreateUpdateTrait;

    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\JobCategory", inversedBy="carJobs")
     */
    protected $jobCategory;

    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\Master")
     */
    protected $master;

    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\CarJob", inversedBy="carCategories")
     */
    protected $carJob;

    /**
     * @ORM\OneToMany(targetEntity="CTO\AppBundle\Entity\CategoryJobDescription", mappedBy="carCategory", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $jobDescriptions;

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
            "jobCategory" => (string) $this->getJobCategory()->getId(),
            "master" => (string) $this->getMaster()->getId(),
            "jobDescriptions" => $this->getJobDescriptions()->getValues()
        ];
    }

    public function __construct()
    {
        $this->jobDescriptions = new ArrayCollection();
    }

    /**
     * @return JobCategory
     */
    public function getJobCategory()
    {
        return $this->jobCategory;
    }

    /**
     * @param JobCategory $jobCategory
     * @return CarCategory
     */
    public function setJobCategory(JobCategory $jobCategory)
    {
        $this->jobCategory = $jobCategory;

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
     * @return CarCategory
     */
    public function setCarJob(CarJob $carJob)
    {
        $this->carJob = $carJob;
    }

    /**
     * @return Collection
     */
    public function getJobDescriptions()
    {
        return $this->jobDescriptions;
    }

    /**
     * @param CategoryJobDescription $categoryJobDescription
     * @return CarCategory
     */
    public function addJobDescription(CategoryJobDescription $categoryJobDescription)
    {
        $categoryJobDescription->setCarCategory($this);
        $this->jobDescriptions->add($categoryJobDescription);

        return $this;
    }

    /**
     * @param CategoryJobDescription $categoryJobDescription
     */
    public function removeJobDescription(CategoryJobDescription $categoryJobDescription)
    {
        $this->jobDescriptions->removeElement($categoryJobDescription);
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
     * @return CarCategory
     */
    public function setMaster(Master $master)
    {
        $this->master = $master;

        return $this;
    }

    public function __toString()
    {
        return $this->getJobCategory()->getName();
    }
}
