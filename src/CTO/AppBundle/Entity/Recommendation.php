<?php

namespace CTO\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Recommendation
 * @package CTO\AppBundle\Entity
 *
 * @ORM\Table(name="recommendations")
 * @ORM\Entity(repositoryClass="CTO\AppBundle\Entity\Repository\RecommendationRepository")
 */
class Recommendation
{
    use CreateUpdateTrait;

    /**
     * @Assert\NotBlank(message="Обов'язкове поле")
     * @ORM\Column(name="title", type="string")
     */
    protected $title;

    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\CarJob", inversedBy="recommendations")
     */
    protected $job;

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return Recommendation
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return CarJob
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param CarJob $job
     * @return Recommendation
     */
    public function setJob(CarJob $job)
    {
        $this->job = $job;

        return $this;
    }
}
