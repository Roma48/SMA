<?php

namespace CTO\AppBundle\Controller\DashboardControllers\CTO;

use Carbon\Carbon;
use CTO\AppBundle\Entity\CtoUser;
use CTO\AppBundle\Entity\DTO\FinancialArchive;
use CTO\AppBundle\Form\CtoUserType;
use CTO\AppBundle\Form\DTO\FinancialArchiveYearType;
use DateTime;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HomeController
 */
class HomeController extends Controller
{
    /**
     * @Route("/", name="ctoUser_home")
     * @Method("GET")
     * @Template()
     */
    public function homeAction()
    {
        return [];
    }

    /**
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/settings", name="ctoUser_settings_edit")
     * @Method({"POST", "GET"})
     * @Template("@CTOApp/DashboardControllers/Admin/Cto/new.html.twig")
     */
    public function settingsAction(Request $request)
    {
        /** @var CtoUser $user */
        $user = $this->getUser();
        $form = $this->createForm(new CtoUserType(), $user);

        if ($request->getMethod() == Request::METHOD_POST) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();

                if ($user->getPlainPassword()) {
                    $encoder = $this->get('security.password_encoder');
                    $user->setPassword($encoder->encodePassword($user, $user->getPlainPassword()));
                }

                $em->flush();

                $this->addFlash('success', "{$user->getCtoName()} успішно відредаговано.");

                return $this->redirectToRoute('cto_jobs_home');
            }
        }

        return [
            'form' => $form->createView(),
            'title' => 'Редагувати'
        ];
    }

    /**
     * @param $year
     * @param Request $request
     * @return array
     *
     * @Route("/finance/{year}", name="cto_finance_archive")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_PREVIOUS_ADMIN')")
     * @Template()
     */
    public function financialReportByYearAction($year, Request $request)
    {
        $yearObj = new FinancialArchive();
        $yearObj->setYear($year);
        $form = $this->createForm(new FinancialArchiveYearType(), $yearObj);
        $result = [];

        $form->handleRequest($request);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var CtoUser $user */
        $user = $this->getUser();

        $startYear = Carbon::createFromDate((int)$yearObj->getYear())->startOfYear();
        $endYear = $startYear->copy()->endOfYear();

        while ($startYear <= $endYear) {
            $month = $this->getStartAndFinishMonth($startYear);
            $finReport = $em->getRepository("CTOAppBundle:CarJob")->totalFinancialReportForMonth($month['start'], $month['finish'], $user);
            $uniqClients = $em->getRepository("CTOAppBundle:CtoClient")->getUniqueClients($month['start'], $month['finish'], $user);
            $result[] = [
                'date' => $month['start'],
                'client' => $s=$uniqClients['clnts'],
                'jobsCount' => $finReport['jobs'],
                'money' => $finReport['money']
            ];

            $startYear->addMonth();
        }

        return [
            'form' => $form->createView(),
            'results' => $result
        ];
    }

    /**
     * @param DateTime $date
     * @return array
     */
    private function getStartAndFinishMonth(DateTime $date)
    {
        $now = Carbon::createFromFormat('Y-m-d', $date->format('Y-m-d'));

        $startMonth = $now->copy();
        $startMonth->startOfMonth();
        $endMonth = $now->copy();
        $endMonth->endOfMonth();

        return [
            'start' => $startMonth,
            'finish' => $endMonth
        ];
    }
}
