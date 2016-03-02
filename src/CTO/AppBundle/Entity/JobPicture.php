<?php

namespace CTO\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class JobPicture
 *
 * @ORM\Table(name="jobPictures")
 * @ORM\Entity()
 */
class JobPicture
{
    use CreateUpdateTrait;

    /**
     * @ORM\Column(name="path", type="string")
     */
    protected $path;
    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\CarJob", inversedBy="pictures")
     */
    protected $job;

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     * @return JobPicture
     */
    public function setPath($path)
    {
        $this->path = $path;

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
     * @return JobPicture
     */
    public function setJob(CarJob $job)
    {
        $this->job = $job;

        return $this;
    }
}
