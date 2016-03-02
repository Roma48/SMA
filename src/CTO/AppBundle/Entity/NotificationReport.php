<?php

namespace CTO\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class NotificationReport
 *
 * @ORM\Table(name="notificationReports")
 * @ORM\Entity(repositoryClass="CTO\AppBundle\Entity\Repository\NotificationReportRepository")
 */
class NotificationReport implements \JsonSerializable
{
    const REPORT_STATUS_SENDED = "success";
    const REPORT_STATUS_FAILED = "fail";

    use CreateUpdateTrait;

    /**
     * @ORM\Column(name="phone", type="string", length=20)
     */
    protected $phone;
    /**
     * @ORM\Column(name="toAdmin", type="boolean")
     */
    protected $sendToAdmin;
    /**
     * @ORM\Column(name="status", type="string", length=20)
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\CtoClient")
     */
    protected $client;
    /**
     * @ORM\ManyToOne(targetEntity="CTO\AppBundle\Entity\Notification", inversedBy="reports")
     */
    protected $notification;

    function jsonSerialize()
    {
        return [
            "client" => $this->getClient()->getFullName(),
            "phone" => $this->getPhone(),
            "isAdmin" => $this->isSendToAdmin(),
            "status" => $this->getStatus(),
            "time" => $this->getCreatedAt()->format("d.m.Y H:i")
        ];
    }

    public function __construct(CtoClient $client)
    {
        $this->setClient($client);
        $this->setSendToAdmin(false);
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     * @return NotificationReport
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSendToAdmin()
    {
        return $this->sendToAdmin;
    }

    /**
     * @param bool $sendToAdmin
     * @return NotificationReport
     */
    public function setSendToAdmin($sendToAdmin)
    {
        $this->sendToAdmin = $sendToAdmin;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return NotificationReport
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
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
     * @return NotificationReport
     */
    public function setClient(CtoClient $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @param Notification $notification
     * @return NotificationReport
     */
    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;

        return $this;
    }
}
