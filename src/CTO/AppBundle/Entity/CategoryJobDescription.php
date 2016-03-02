<?php

namespace CTO\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class CategoryJobDescription
 *
 * @ORM\Table(name="jobDescription")
 * @ORM\Entity()
 */
class CategoryJobDescription implements \JsonSerializable
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
    //   pattern="/[0-9]{1,}([,.][0-9]{1,2})?/
    protected $price;

    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\CarCategory", inversedBy="jobDescriptions")
     */
    protected $carCategory;

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
     * @return CarJob
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
     * @return CarJob
     */
    public function setPrice($price)
    {
        $this->price = str_replace(',', '.', $price);

        return $this;
    }

    /**
     * @return CategoryJobDescription
     */
    public function getCarCategory()
    {
        return $this->carCategory;
    }

    /**
     * @param CarCategory $carCategory
     * @return CategoryJobDescription
     */
    public function setCarCategory(CarCategory $carCategory)
    {
        $this->carCategory = $carCategory;

        return $this;
    }
}
