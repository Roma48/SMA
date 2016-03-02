<?php

namespace CTO\AppBundle\Controller\DashboardControllers\CTO;

use CTO\AppBundle\Entity\CarJob;
use CTO\AppBundle\Entity\CtoUser;
use CTO\AppBundle\Entity\DTO\StatisticFilterDTO;
use CTO\AppBundle\Entity\DTO\StatisticsMastersFilterDTO;
use CTO\AppBundle\Entity\Master;
use CTO\AppBundle\Entity\PaidSalaryJob;
use CTO\AppBundle\Form\DTO\StatisticFilterDTOType;
use CTO\AppBundle\Form\DTO\StatisticsMastersFilterDTOType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class StatisticsController
 * @package CTO\AppBundle\Controller\DashboardControllers\CTO
 *
 * @Route("/statistics")
 */
class StatisticsController extends Controller
{
    /**
     * @param Request $request
     * @param $tabName
     * @return array
     *
     * @Route("/{tabName}", name="cto_statistics_filter", defaults={"tabName" = "general"}, requirements={"tabName" = "general|masters"})
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function filterAction(Request $request, $tabName)
    {
        /** @var CtoUser $ctoUser */
        $ctoUser = $this->getUser();
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        // General
        $filterDTO = new StatisticFilterDTO();
        $formGeneral = $this->createForm(StatisticFilterDTOType::class, $filterDTO);
        $formGeneral->handleRequest($request);
        /** @var CarJob[] $jobs */
        $jobs = $tabName == "general" ? $em->getRepository("CTOAppBundle:CarJob")->getStatisticsWithFilters($ctoUser, $filterDTO) : [];

        // Masters
        $filterMastersDTO = new StatisticsMastersFilterDTO();
        $formMasters = $this->createForm(StatisticsMastersFilterDTOType::class, $filterMastersDTO);
        $formMasters->handleRequest($request);
        /** @var PaidSalaryJob[] $masters */
        $masters = $tabName == "masters" ? $em->getRepository("CTOAppBundle:PaidSalaryJob")->getStatisticsWithMastersFilters($ctoUser, $filterMastersDTO) : [];
        $getJobCostSpentSumm = $em->getRepository("CTOAppBundle:PaidSalaryJob")->getStatisticsWithMastersFilters($ctoUser, $filterMastersDTO);

        $array = new ArrayCollection();

        /** @var PaidSalaryJob $paid */
        foreach ($getJobCostSpentSumm as $paid) {
            if (!$array->contains($paid->getCarJob())) {
                $array->add($paid->getCarJob());
            }
        }

        $result = [
            "cost" => 0,
            "spend" => 0
        ];

        /** @var CarJob $job */
        foreach ($array as $job) {
            $result['cost'] += $job->getTotalCost();
            $result['spend'] += $job->getTotalSpend();
        }

        $forMastersStatistics = [];
        if ($filterMastersDTO->getMasters()) {
            /** @var Master $master */
            foreach ($filterMastersDTO->getMasters() as $master) {
                $forMastersStatistics[$master->getId()] = [
                    "name" => $master->getFullName(),
                    "sum" => 0,
                ];
            }
            foreach ($masters as $paid) {
                if (array_key_exists($paid->getMaster()->getId(), $forMastersStatistics)) {
                    $forMastersStatistics[$paid->getMaster()->getId()]['sum'] = $forMastersStatistics[$paid->getMaster()->getId()]['sum'] + $paid->getPrice();
                }
            }
        }

        return [
            "filterForm" => $formGeneral->createView(),
            "filterMastersForm" => $formMasters->createView(),
            "jobs" => $jobs,
            "masters" => $masters,
            "selectedMasters" => $forMastersStatistics,
            "totalSums" => $result,
            "tabName" => $tabName,
        ];
    }
}
