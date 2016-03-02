<?php

namespace CTO\AppBundle\Entity\Repository;

use CTO\AppBundle\Entity\CtoUser;
use DateTime;
use Doctrine\ORM\EntityRepository;

class ClientCarsRepository extends EntityRepository
{
    public function listAllCarsWithSorting(CtoUser $user)
    {
        return $this->getEntityManager()
            ->createQuery('select car from CTOAppBundle:ClientCar car JOIN car.ctoClient cl JOIN car.model mod WHERE cl.cto = :ctoUser ORDER BY cl.lastVisitDate ASC')->setParameter('ctoUser', $user);
    }

    public function allCarsByCTO(CtoUser $user)
    {
       return $this->createQueryBuilder('c')
           ->leftJoin('c.ctoClient', 'cl')
           ->leftJoin('cl.cto', 'cto')
           ->where('cto = :ctoUser')
           ->setParameter('ctoUser', $user)
           ->getQuery()
           ->getResult();
    }

    public function carFilter($filterData, CtoUser $user) {
        $qb = $this->createQueryBuilder('c')
            ->join('c.ctoClient', 'cl')
            ->andWhere('cl.cto = :ctoUser')
            ->setParameter('ctoUser', $user);

        if (array_key_exists('fullName', $filterData)) {
            $qb->andWhere('cl.fullName like :fullName')
                ->setParameter('fullName', '%' . $filterData['fullName'] . '%');
        }
        if (array_key_exists('model', $filterData)) {
            $qb->join('c.model', 'mo')
                ->andWhere('mo.id = :model')
                ->setParameter('model', $filterData['model']);
        }
        if (array_key_exists('dateFrom', $filterData)) {
            $qb->join('c.ctoClient', 'clq')
                ->andWhere('clq.lastVisitDate >= :dateFrom')
                ->setParameter('dateFrom', new DateTime($filterData['dateFrom']));
        }
        if (array_key_exists('dateTo', $filterData)) {
            $qb->join('c.ctoClient', 'clz')
                ->andWhere('clz.lastVisitDate <= :dateTo')
                ->setParameter('dateTo', new DateTime($filterData['dateTo']));
        }
        $qb
            ->orderBy('cl.lastVisitDate', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
