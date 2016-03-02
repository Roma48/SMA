<?php

namespace CTO\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Master
 *
 * @ORM\Table(name="masters")
 * @ORM\Entity(repositoryClass="CTO\AppBundle\Entity\Repository\MasterRepository")
 */
class Master implements \JsonSerializable
{
    use CreateUpdateTrait;

    /**
     * @Assert\NotBlank(message="Ім'я не повинно бути пустим")
     * @ORM\Column(name="firstName", type="string")
     */
    protected $firstName;
    /**
     * @ORM\Column(name="lastName", type="string", nullable=true)
     */
    protected $lastName;

    protected $fullName;

    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\CtoUser", inversedBy="masters")
     */
    protected $cto;

    function jsonSerialize()
    {
        return [
            "id" => (string) $this->getId(),
            "name" => $this->getFullName()
        ];
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->firstName . " " . $this->lastName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     * @return Master
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     * @return Master
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
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
     * @return Master
     */
    public function setCto(CtoUser $cto)
    {
        $this->cto = $cto;

        return $this;
    }
}
