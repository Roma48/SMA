<?php

namespace CTO\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class City
 *
 * @ORM\Table(name="city")
 * @ORM\Entity()
 */
class City 
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
     * @var string
     *
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug", type="string", length=255)
     */
    protected $slug;

    /**
     * @ORM\OneToMany(targetEntity="CTO\AppBundle\Entity\CtoUser", mappedBy="city", cascade={"persist"})
     */
    protected $ctoUsers;

    /**
     * @ORM\OneToMany(targetEntity="CTO\AppBundle\Entity\CtoClient", mappedBy="city", cascade={"persist"})
     */
    protected $ctoClients;

    public function __construct()
    {
        $this->ctoUsers = new ArrayCollection();
        $this->ctoClients = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return City
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return Collection
     */
    public function getCtoUsers()
    {
        return $this->ctoUsers;
    }

    /**
     * @param CtoUser $ctoUser
     * @return City
     */
    public function addCtoUser(CtoUser $ctoUser)
    {
        $ctoUser->setCity($this);
        $this->ctoUsers->add($ctoUser);

        return $this;
    }

    /**
     * @param CtoUser $ctoUser
     */
    public function removeCtoUser(CtoUser $ctoUser)
    {
        $this->ctoUsers->removeElement($ctoUser);
    }

    /**
     * @return Collection
     */
    public function getCtoClients()
    {
        return $this->ctoClients;
    }

    /**
     * @param CtoClient $ctoClient
     * @return City
     */
    public function addCtoClient(CtoClient $ctoClient)
    {
        $ctoClient->setCity($this);
        $this->ctoClients->add($ctoClient);

        return $this;
    }

    /**
     * @param CtoClient $ctoClient
     */
    public function removeCtoClient(CtoClient $ctoClient)
    {
        $this->ctoClients->removeElement($ctoClient);
    }
}
