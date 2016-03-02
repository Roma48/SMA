<?php

namespace CTO\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CtoClientNotes
 *
 * @ORM\Table(name="ctoClientNotes")
 * @ORM\Entity()
 */
class CtoClientNotes
{
    use CreateUpdateTrait;

    /**
     * @Assert\NotBlank(message="Це поле не повинне бути пустим")
     * @ORM\Column(name="text", type="text")
     */
    protected $text;

    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\CtoClient", inversedBy="notes")
     */
    protected $clientCto;

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     * @return CtoClientNotes
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return CtoClient
     */
    public function getClientCto()
    {
        return $this->clientCto;
    }

    /**
     * @param CtoClient $clientCto
     * @return CtoClientNotes
     */
    public function setClientCto(CtoClient $clientCto)
    {
        $this->clientCto = $clientCto;

        return $this;
    }
}
