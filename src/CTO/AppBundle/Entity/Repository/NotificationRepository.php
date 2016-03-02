<?php

namespace CTO\AppBundle\Entity\Repository;

use CTO\AppBundle\Entity\CtoUser;
use CTO\AppBundle\Entity\Notification;
use DateTime;
use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{
    public function getNotificationCountForLast3day(CtoUser $user, DateTime $from, DateTime $to)
    {
        return $this->createQueryBuilder('j')
            ->select('count(j) as NotifCount')
            ->andWhere('j.userCto = :cto')->setParameter('cto', $user)
            ->andWhere('j.whenSend >= :from')->setParameter('from', $from)
            ->andWhere('j.whenSend <= :to')->setParameter('to', $to)
            ->andWhere('j.status = :inprogress')->setParameter('inprogress', Notification::STATUS_SEND_IN_PROGRESS)
            ->getQuery()
            ->getSingleResult();
    }

    public function getCurrents(CtoUser $user, $from, $to)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.userCto = :cto')->setParameter('cto', $user)
            ->andWhere('j.whenSend >= :from')->setParameter('from', $from)
            ->andWhere('j.whenSend <= :to')->setParameter('to', $to)
            ->andWhere('j.status = :inprogress')->setParameter('inprogress', Notification::STATUS_SEND_IN_PROGRESS)
            ->orderBy('j.whenSend', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getPlanned(CtoUser $user, $to)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.userCto = :cto')->setParameter('cto', $user)
            ->andWhere('j.whenSend > :to')->setParameter('to', $to)
            ->andWhere('j.status = :inprogress')->setParameter('inprogress', Notification::STATUS_SEND_IN_PROGRESS)
            ->orderBy('j.whenSend', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getSentOut(CtoUser $user)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.userCto = :cto')->setParameter('cto', $user)
            ->andWhere('j.status <> :inprogress')->setParameter('inprogress', Notification::STATUS_SEND_IN_PROGRESS)
            ->orderBy('j.whenSend', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getLast(CtoUser $user, $from)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.userCto = :cto')->setParameter('cto', $user)
            ->andWhere('j.whenSend < :from')->setParameter('from', $from)
            ->andWhere('j.status = :inprogress')->setParameter('inprogress', Notification::STATUS_SEND_IN_PROGRESS)
            ->orderBy('j.whenSend', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
