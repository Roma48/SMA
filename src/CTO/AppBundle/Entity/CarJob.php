<?php

namespace CTO\AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CarJob
 *
 * @ORM\Table(name="carJobs")
 * @ORM\Entity(repositoryClass="CTO\AppBundle\Entity\Repository\CarJobRepository")
 */
class CarJob implements \JsonSerializable
{
    use CreateUpdateTrait;

    /**
     * @var DateTime
     *
     * @Assert\NotBlank(message="Обов'язкове поле")
     * @ORM\Column(name="jobDate", type="datetime", nullable=true)
     */
    protected $jobDate;

    /**
     * @ORM\Column(name="totalCost", type="float")
     */
    protected $totalCost;

    /**
     * @ORM\Column(name="totalSpend", type="float")
     */
    protected $totalSpend;

    /**
     * @ORM\Column(name="tmp_hash", type="string")
     */
    protected $tmpHash;

    /**
     * @ORM\OneToMany(targetEntity="CTO\AppBundle\Entity\JobPicture", mappedBy="job", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $pictures;

    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\CtoUser", inversedBy="jobs")
     */
    protected $cto;

    /**
     * @Assert\NotBlank(message="Обов'язкове поле")
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\CtoClient", inversedBy="carJobs")
     */
    protected $client;

    /**
     * @ORM\OneToMany(targetEntity="CTO\AppBundle\Entity\CarCategory", mappedBy="carJob", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $carCategories;

    /**
     * @ORM\OneToMany(targetEntity="CTO\AppBundle\Entity\Notification", mappedBy="carJob", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $notifications;

    /**
     * @ORM\OneToMany(targetEntity="CTO\AppBundle\Entity\PaidSalaryJob", mappedBy="carJob", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $paidSalaryJob;

    /**
     * @ORM\OneToMany(targetEntity="CTO\AppBundle\Entity\Recommendation", mappedBy="job", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $recommendations;

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
            "car_job" => [
                "jobDate" => $this->getJobDate()->format("d.m.Y"),
                "client" => (string) $this->getClient()->getId(),
                "carCategories" => $this->getCarCategories()->getValues(),
                "paidSalaryJob" => $this->getPaidSalaryJob()->getValues()
            ]
        ];
    }

    public function __construct()
    {
        $this->notifications = new ArrayCollection();
        $this->carCategories = new ArrayCollection();
        $this->paidSalaryJob = new ArrayCollection();
        $this->recommendations = new ArrayCollection();
        $this->pictures = new ArrayCollection();
        $this->totalCost = 0;
        $this->totalSpend = 0;
        $this->tmpHash = uniqid("", true);
    }

    /**
     * @return DateTime
     */
    public function getJobDate()
    {
        return $this->jobDate;
    }

    /**
     * @param DateTime $jobDate
     * @return CarJob
     */
    public function setJobDate($jobDate)
    {
        $this->jobDate = $jobDate;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param Notification $notification
     * @return CarJob
     */
    public function addNotification(Notification $notification)
    {
        $notification->setCarJob($this);
        $this->notifications->add($notification);

        return $this;
    }

    /**
     * @param Notification $notification
     */
    public function removeNotification(Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * @return Collection
     */
    public function getCarCategories()
    {
        return $this->carCategories;
    }

    /**
     * @param CarCategory $carCategories
     * @return CarJob
     */
    public function addCarCategory(CarCategory $carCategories)
    {
        $carCategories->setCarJob($this);
        $this->carCategories->add($carCategories);

        return $this;
    }

    /**
     * @param CarCategory $carCategory
     */
    public function removeCarCategory(CarCategory $carCategory)
    {
        $this->carCategories->removeElement($carCategory);
    }

    /**
     * @return Collection
     */
    public function getPaidSalaryJob()
    {
        return $this->paidSalaryJob;
    }

    /**
     * @param PaidSalaryJob $paidSalaryJob
     * @return CarJob
     */
    public function addPaidSalaryJob(PaidSalaryJob $paidSalaryJob)
    {
        $paidSalaryJob->setCarJob($this);
        $this->paidSalaryJob->add($paidSalaryJob);

        return $this;
    }

    /**
     * @param PaidSalaryJob $paidSalaryJob
     */
    public function removePaidSalaryJob(PaidSalaryJob $paidSalaryJob)
    {
        $this->paidSalaryJob->removeElement($paidSalaryJob);
    }

    /**
     * @return CtoClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param CtoClient $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return mixed
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }

    /**
     * @param mixed $totalCost
     * @return CarJob
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalSpend()
    {
        return $this->totalSpend;
    }

    /**
     * @param mixed $totalSpend
     * @return CarJob
     */
    public function setTotalSpend($totalSpend)
    {
        $this->totalSpend = $totalSpend;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTmpHash()
    {
        return $this->tmpHash;
    }

    /**
     * @param mixed $tmpHash
     * @return CarJob
     */
    public function setTmpHash($tmpHash)
    {
        $this->tmpHash = $tmpHash;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getRecommendations()
    {
        return $this->recommendations;
    }

    /**
     * @param Recommendation $recommendation
     * @return CarJob
     */
    public function addRecommendation(Recommendation $recommendation)
    {
        $recommendation->setJob($this);
        $this->recommendations->add($recommendation);

        return $this;
    }

    /**
     * @param Recommendation $recommendation
     */
    public function removeRecommendation(Recommendation $recommendation)
    {
        $this->recommendations->removeElement($recommendation);
    }

    /**
     * @return Collection
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    /**
     * @param JobPicture $picture
     * @return CarJob
     */
    public function addPicture(JobPicture $picture)
    {
        $picture->setJob($this);
        $this->pictures->add($picture);

        return $this;
    }

    /**
     * @param JobPicture $picture
     */
    public function removePicture(JobPicture $picture)
    {
        $this->pictures->removeElement($picture);
    }

    /**
     * @return CtoUser
     */
    public function getCto()
    {
        return $this->cto;
    }

    /**
     * @param CtoUser $cto
     * @return CarJob
     */
    public function setCto(CtoUser $cto)
    {
        $this->cto = $cto;

        return $this;
    }
}
