<?php

namespace CTO\AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class CtoUserRepository extends EntityRepository
{
    public function listCTOUsersWithMoneySorted()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT c.ctoName, c.firstName, c.lastName, (sum(jobs.totalCost) - SUM(jobs.totalSpend)) as tspend, sum(jobs.totalCost) as tcost from CTOAppBundle:CtoUser c join c.clients cl join cl.carJobs jobs group by c ');
    }
}
