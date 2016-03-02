<?php

namespace CTO\AppBundle\Controller\DashboardControllers\CTO;

use CTO\AppBundle\Entity\Master;
use CTO\AppBundle\Form\CtoMasterType;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CtoMasterController
 * @package CTO\AppBundle\Controller\DashboardControllers\CTO
 *
 * @Route("/masters")
 */
class CtoMasterController extends Controller
{
    /**
     * @Route("/list", name="cto_masters_list")
     * @Method("GET")
     * @Template()
     */
    public function listAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $mastersResult = $em->getRepository("CTOAppBundle:Master")->findBy(['cto' => $this->getUser()]);
        $paginator = $this->get('knp_paginator');
        $masters = $paginator->paginate(
            $mastersResult,
            $this->get('request')->query->get('page', 1),   /* page number */
            $this->container->getParameter('pagination')    /* limit per page */
        );

        return [
            "masters" => $masters
        ];
    }

    /**
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/new", name="cto_masters_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $master = new Master();
        $form = $this->createForm(CtoMasterType::class, $master);

        if ($request->getMethod() == Request::METHOD_POST) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $master->setCto($this->getUser());
                $em->persist($master);
                $em->flush();

                $this->addFlash('success', "Нового майстра успішно створено.");

                return $this->redirectToRoute("cto_masters_list");
            }
        }

        return [
            'form' => $form->createView(),
            'title' => 'Створити'
        ];
    }

    /**
     * @param Master $master
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/edit/{id}", name="cto_masters_edit", requirements={"id" = "\d+"})
     * @Template("@CTOApp/DashboardControllers/CTO/CtoMaster/new.html.twig")
     */
    public function editAction(Master $master, Request $request)
    {
        $form = $this->createForm(CtoMasterType::class, $master);

        if ($request->getMethod() == Request::METHOD_POST) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $em->flush();

                $this->addFlash('success', "Майстра успішно відредаговано.");

                return $this->redirectToRoute("cto_masters_list");
            }
        }

        return [
            'form' => $form->createView(),
            'title' => 'Редагувати'
        ];
    }
}
