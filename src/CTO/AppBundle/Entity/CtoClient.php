<?php

namespace CTO\AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class CtoClient
 *
 * @ORM\Table(name="clients")
 * @ORM\Entity(repositoryClass="CTO\AppBundle\Entity\Repository\CtoClientRepository")
 */
class CtoClient implements \JsonSerializable
{
    use CreateUpdateTrait;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Обов'язкове поле")
     * @ORM\Column(name="firstName", type="string", length=255)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     * @ORM\Column(name="fullName", type="string", length=255)
     */
    protected $fullName;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Обов'язкове поле")
     * @Assert\Length(max=20, maxMessage="Не більше {{ limit }} символів")
     * @Assert\Regex(pattern="/^[+]?[0-9+() -]+$/", message="Допустимі тільки цифри")
     * @ORM\Column(name="phone", type="string", length=255)
     */
    protected $phone;

    /**
     * @ORM\Column(name="lastVisitDate", type="datetime")
     */
    protected $lastVisitDate;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"firstName", "lastName"})
     * @ORM\Column(name="slug", type="string", length=255)
     */
    protected $slug;

    /**
     * @ORM\Column(name="totalCost", type="float")
     */
    protected $totalCost;

    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\City", inversedBy="ctoClients")
     */
    protected $city;

    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\CtoUser", inversedBy="clients")
     */
    protected $cto;

    /**
     * @ORM\OneToMany(targetEntity="CTO\AppBundle\Entity\CarJob", mappedBy="client", cascade={"persist"})
     */
    protected $carJobs;

    /**
     * @ORM\OneToMany(targetEntity="CTO\AppBundle\Entity\Notification", mappedBy="clientCto", cascade={"persist"})
     */
    protected $notifications;

    /**
     * @ORM\OneToMany(targetEntity="CTO\AppBundle\Entity\CtoClientNotes", mappedBy="clientCto", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $notes;

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
            "id" => $this->getId(),
            "name" => $this->getFullName()
        ];
    }

    public function __construct()
    {
        $this->totalCost = 0;

        $this->notifications = new ArrayCollection();
        $this->carJobs = new ArrayCollection();
        $this->notes = new ArrayCollection();
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
     * @return CtoClient
     */
    public function setCto(CtoUser $cto)
    {
        $this->cto = $cto;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return CtoClient
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return CtoClient
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
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
     * @return CtoClient
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param mixed $fullName
     * @return CtoClient
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return CtoClient
     */
    public function setPhone($phone)
    {
        $tmp = str_replace(' ', '', str_replace('-', '', str_replace(')','', str_replace('(','', trim($phone)))));
        $this->phone = $phone ? $tmp : null;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getLastVisitDate()
    {
        return $this->lastVisitDate;
    }

    /**
     * @param DateTime $lastVisitDate
     * @return CtoClient
     */
    public function setLastVisitDate(DateTime $lastVisitDate)
    {
        $this->lastVisitDate = $lastVisitDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param City $city
     * @return CtoClient
     */
    public function setCity(City $city)
    {
        $this->city = $city;

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
     * @return CtoClient
     */
    public function addNotification(Notification $notification)
    {
        $notification->setClientCto($this);
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
    public function getCarJobs()
    {
        return $this->carJobs;
    }


    /**
     * @param CarJob $carJob
     * @return CtoClient
     */
    public function addCarJob(CarJob $carJob)
    {
        $carJob->setClient($this);
        $this->carJobs->add($carJob);

        return $this;
    }

    /**
     * @param CarJob $carJob
     */
    public function removeCarJob(CarJob $carJob)
    {
        $this->carJobs->removeElement($carJob);
    }

    /**
     * @return Collection
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param CtoClientNotes $note
     * @return CtoClient
     */
    public function addNote(CtoClientNotes $note)
    {
        $note->setClientCto($this);
        $this->notes->add($note);

        return $this;
    }

    /**
     * @param CtoClientNotes $note
     */
    public function removeNote(CtoClientNotes $note)
    {
        $this->notes->removeElement($note);
    }

    public function __toString()
    {
        return $this->getSlug();
    }
}
