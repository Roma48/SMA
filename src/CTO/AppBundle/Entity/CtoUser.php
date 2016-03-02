<?php

namespace CTO\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class CtoUser
 *
 * @ORM\Entity(repositoryClass="CTO\AppBundle\Entity\Repository\CtoUserRepository")
 */
class CtoUser extends BaseUser
{

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"firstName", "lastName"})
     * @ORM\Column(name="slug", type="string", length=255)
     */
    protected $slug;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Обов'язкове поле")
     * @ORM\Column(name="ctoName", type="string", length=255)
     */
    protected $ctoName;

    /**
     * @var bool
     *
     * @ORM\Column(name="blocked", type="boolean")
     */
    protected $blocked;

    /**
     * @var bool
     *
     * @ORM\Column(name="publicProfile", type="boolean")
     */
    protected $publicProfile;

    /**
     * @ORM\OneToMany(targetEntity="CTO\AppBundle\Entity\CtoClient", mappedBy="cto")
     */
    protected $clients;

    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\City", inversedBy="ctoUsers")
     */
    protected $city;

    /**
     * @ORM\OneToMany(targetEntity="CTO\AppBundle\Entity\CarJob", mappedBy="cto", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $jobs;

    /**
     * @ORM\OneToMany(targetEntity="CTO\AppBundle\Entity\Master", mappedBy="cto", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $masters;

    public function __construct()
    {
        parent::__construct();
        $this->setRoles([self::ROLE_CTO_USER]);
        $this->setBlocked(false);
        $this->clients = new ArrayCollection();
        $this->masters = new ArrayCollection();
        $this->jobs = new ArrayCollection();
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
     * @return CtoUser
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getCtoName()
    {
        return $this->ctoName;
    }

    /**
     * @param string $ctoName
     * @return CtoUser
     */
    public function setCtoName($ctoName)
    {
        $this->ctoName = $ctoName;

        return $this;
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
     * @return CtoUser
     */
    public function setCity(City $city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBlocked()
    {
        return $this->blocked;
    }

    /**
     * @param bool $blocked
     * @return CtoUser
     */
    public function setBlocked($blocked)
    {
        $this->blocked = $blocked;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isPublicProfile()
    {
        return $this->publicProfile;
    }

    /**
     * @param boolean $publicProfile
     * @return CtoUser
     */
    public function setPublicProfile($publicProfile)
    {
        $this->publicProfile = $publicProfile;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getClients()
    {
        return $this->clients;
    }

    public function addClient(CtoClient $client)
    {
        $client->setCto($this);
        $this->clients->add($client);
    }

    public function removeClient(CtoClient $client)
    {
        $this->clients->removeElement($client);
    }

    /**
     * @return Collection
     */
    public function getMasters()
    {
        return $this->masters;
    }

    /**
     * @param Master $master
     * @return CtoUser
     */
    public function addMaster(Master $master)
    {
        if (!$this->getMasters()->contains($master)) {
            $master->setCto($this);
            $this->masters->add($master);
        }

        return $this;
    }

    /**
     * @param Master $master
     */
    public function removeMaster(Master $master)
    {
        $this->masters->removeElement($master);
    }

    /**
     * @return Collection
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * @param CarJob $job
     * @return CtoUser
     */
    public function addJob(CarJob $job)
    {
        if (!$this->getJobs()->contains($job)) {
            $job->setCto($this);
            $this->jobs->add($job);
        }

        return $this;
    }

    /**
     * @param CarJob $job
     */
    public function removeJob(CarJob $job)
    {
        $this->jobs->removeElement($job);
    }
}
