<?php

namespace CTO\AppBundle\Entity\Repository;

use Carbon\Carbon;
use CTO\AppBundle\Entity\CtoClient;
use CTO\AppBundle\Entity\CtoUser;
use CTO\AppBundle\Entity\DTO\ExportFilterDTO;
use CTO\AppBundle\Entity\DTO\StatisticFilterDTO;
use CTO\AppBundle\Entity\DTO\StatisticsMastersFilterDTO;
use DateTime;
use Doctrine\ORM\EntityRepository;

class CarJobRepository extends EntityRepository
{
    public function listJobsWithSortings(CtoUser $user)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT j From CTOAppBundle:CarJob j left JOIN j.client cc WHERE cc.cto = :ctoUser ORDER by j.jobDate DESC ')->setParameter('ctoUser', $user);
    }

    /**
     * @param $filterData
     * @param CtoUser $user
     * @return array
     */
    public function jobsFilter($filterData, CtoUser $user)
    {
        $qb = $this->createQueryBuilder('j')
            ->join('j.client', 'cl')
            ->andWhere('cl.cto = :ctoUser')
            ->setParameter('ctoUser', $user);

        if (array_key_exists('fullName', $filterData)) {
            $qb->andWhere('cl.fullName like :fullName')
                ->setParameter('fullName', '%' . $filterData['fullName'] . '%');
        }
        if (array_key_exists('dateFrom', $filterData)) {
            $qb->andWhere('j.jobDate >= :dateFrom')
                ->setParameter('dateFrom', new DateTime($filterData['dateFrom']));
        }
        if (array_key_exists('dateTo', $filterData)) {
            $qb->andWhere('j.jobDate <= :dateTo')
                ->setParameter('dateTo', new DateTime($filterData['dateTo']));
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $start
     * @param $end
     * @param CtoUser $user
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countForMonth($start, $end, CtoUser $user)
    {
        return $this->createQueryBuilder('j')
            ->select('count(j) as jobs')
            ->join('j.client', 'cl')
            ->andWhere('cl.cto = :ctoUser')
            ->setParameter('ctoUser', $user)
            ->andWhere('j.jobDate >= :start')
            ->setParameter('start', $start)
            ->andWhere('j.jobDate <= :end')
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @param CtoUser $user
     * @param CtoClient $client
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countForMonthByClient(CtoUser $user, CtoClient $client)
    {
        return $this->createQueryBuilder('j')
            ->select('count(j) as jobs')
            ->join('j.client', 'cl')
            ->andWhere('j.client = :ctoClient')->setParameter('ctoClient', $client)
            ->andWhere('cl.cto = :ctoUser')->setParameter('ctoUser', $user)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @param $start
     * @param $end
     * @param CtoUser $user
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countDistinctClientCarsForMonth($start, $end, CtoUser $user)
    {
        return $this->createQueryBuilder('j')
            ->select('count(distinct car) as cars')
            ->join('j.client', 'cl')
            ->join('j.car', 'car')
            ->andWhere('cl.cto = :ctoUser')
            ->setParameter('ctoUser', $user)
            ->andWhere('j.jobDate >= :start')
            ->setParameter('start', $start)
            ->andWhere('j.jobDate <= :end')
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleResult();
    }

    public function totalSalaryForMonth($start, $end, CtoUser $user)
    {
        return $this->createQueryBuilder('j')
            ->select('sum(j.totalCost) - sum(j.totalSpend) as money')
            ->join('j.client', 'cl')
            ->andWhere('cl.cto = :ctoUser')
            ->setParameter('ctoUser', $user)
            ->andWhere('j.jobDate >= :start')
            ->setParameter('start', $start)
            ->andWhere('j.jobDate <= :end')
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleResult();
    }

    public function totalFinancialReportForMonth($start, $end, CtoUser $user)
    {
        return $this->createQueryBuilder('j')
            ->select('sum(j.totalCost) - sum(j.totalSpend) as money, count(j) as jobs')
            ->join('j.client', 'cl')
            ->andWhere('cl.cto = :ctoUser')
            ->setParameter('ctoUser', $user)
            ->andWhere('j.jobDate >= :start')
            ->setParameter('start', $start)
            ->andWhere('j.jobDate <= :end')
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleResult();
    }

    public function getOneJobByCTOUser(CtoUser $user, $order)
    {
        return $this->createQueryBuilder('j')
            ->select('j')
            ->join('j.client', 'cl')
            ->andWhere('cl.cto = :ctoUser')
            ->setParameter('ctoUser', $user)
            ->orderBy('j.jobDate', $order)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param CtoUser $ctoUser
     * @param StatisticFilterDTO $filterDTO
     * @return array
     */
    public function getStatisticsWithFilters(CtoUser $ctoUser, StatisticFilterDTO $filterDTO)
    {
        $qb = $this->createQueryBuilder("j")
            ->select("j, carCat, paidSal, catName, masterName, catDescr, paidMaster ")
            ->leftjoin("j.carCategories", "carCat")
            ->leftjoin("j.paidSalaryJob", "paidSal")
            ->leftJoin("carCat.jobCategory", "catName")
            ->leftJoin("carCat.master", "masterName")
            ->leftJoin("carCat.jobDescriptions", "catDescr")
            ->leftJoin("paidSal.master", "paidMaster")
            ->where("j.cto = :cto")->setParameter("cto", $ctoUser);
        if ($filterDTO->getCategory()) {
            $qb
                ->andWhere("carCat.jobCategory = :category")->setParameter("category", $filterDTO->getCategory());
        }
        if (count($filterDTO->getMasters())) {
            $qb
                ->andWhere("carCat.master IN (:masters)")
                ->orWhere("paidSal.master IN (:masters)")->setParameter("masters", array_values($filterDTO->getMasters()->getValues()));
        }
        if ($filterDTO->getDateFrom()) {
            $qb
                ->andWhere("j.jobDate >= :fromDate")->setParameter("fromDate", Carbon::createFromFormat("d.m.Y", $filterDTO->getDateFrom())->startOfDay());
        }
        if ($filterDTO->getDateTo()) {
            $qb
                ->andWhere("j.jobDate <= :toDate")->setParameter("toDate", Carbon::createFromFormat("d.m.Y", $filterDTO->getDateTo())->startOfDay());
        }

        $qb
            ->orderBy("j.jobDate", "DESC")
            ->orderBy("j.id", "DESC");

        return $qb->getQuery()->getResult();
    }

    /**
     * @param CtoUser $ctoUser
     * @param StatisticsMastersFilterDTO $filterMastersDTO
     * @return mixed
     */
    public function getStatisticsCostAndPaidSumByPaidMasters(CtoUser $ctoUser, StatisticsMastersFilterDTO $filterMastersDTO)
    {
        $qb = $this->createQueryBuilder("j")
            ->select("distinct j, sum(j.totalCost) as cost, sum(j.totalSpend) as spend")
            ->where("j.cto = :cto")->setParameter("cto", $ctoUser);
        if (count($filterMastersDTO->getMasters())) {
            $qb
                ->leftJoin("j.paidSalaryJob", "paidSal")
                ->andWhere("paidSal.master IN (:master)")->setParameter("master", array_values($filterMastersDTO->getMasters()->getValues()));
        }
        if ($filterMastersDTO->getDateFrom()) {
            $qb
                ->andWhere("j.jobDate >= :fromDate")->setParameter("fromDate", Carbon::createFromFormat("d.m.Y", $filterMastersDTO->getDateFrom())->startOfDay());
        }
        if ($filterMastersDTO->getDateTo()) {
            $qb
                ->andWhere("j.jobDate <= :toDate")->setParameter("toDate", Carbon::createFromFormat("d.m.Y", $filterMastersDTO->getDateTo())->startOfDay());
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param ExportFilterDTO $exportDTO
     * @return array
     */
    public function getDataForExportToCSV(ExportFilterDTO $exportDTO)
    {
        $qb = $this->createQueryBuilder("cj")
            ->select("cj.jobDate as jdate, cat.name as catName, descr.description as description, descr.price as price, cl.fullName as clientName, master.firstName as masterFName, master.lastName as masterLName ")
            ->leftJoin("cj.client", "cl")
            ->leftJoin("cj.carCategories", "jobCat")
            ->leftJoin("jobCat.jobCategory", "cat")
            ->leftJoin("jobCat.master", "master")
            ->leftJoin("jobCat.jobDescriptions", "descr");
        if ($exportDTO->getDateFrom()) {
            $qb
                ->andWhere("cj.jobDate >= :fromDate")->setParameter(
                    "fromDate",
                    Carbon::createFromFormat("d.m.Y", $exportDTO->getDateFrom())->startOfDay()
                );
        }
        if ($exportDTO->getDateTo()) {
            $qb
                ->andWhere("cj.jobDate <= :toDate")->setParameter(
                    "toDate",
                    Carbon::createFromFormat("d.m.Y", $exportDTO->getDateTo())->startOfDay()
                );
        }
        $qb
            ->orderBy("cj.jobDate", "ASC");

        return $qb->getQuery()->getResult();
    }
}
