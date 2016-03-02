<?php

namespace CTO\AppBundle\Controller\DashboardControllers\Admin;
use CTO\AppBundle\Entity\CtoUser;
use CTO\AppBundle\Form\CtoUserType;
use DateTime;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HomeController
 * @Route("/cto")
 */
class CtoController extends Controller
{
    /**
     * @Route("/clients", name="admin_ctoUsers_list")
     * @Method("GET")
     * @Template()
     */
    public function ctoClientsAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $ctoUsers = $em->getRepository("CTOAppBundle:CtoUser")->findAll();
        $paginator = $this->get('knp_paginator');
        $clients = $paginator->paginate(
            $ctoUsers,
            $this->get('request')->query->get('page', 1),   /* page number */
            $this->container->getParameter('pagination')    /* limit per page */
        );

        return [
            "clients" => $clients
        ];
    }

    /**
     * @Route("/clients/money", name="admin_ctoUsers_money_secured")
     * @Method("GET")
     * @Template()
     */
    public function infoWithMoneyAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $ctoUsersResult = $em->getRepository("CTOAppBundle:CtoUser")->listCTOUsersWithMoneySorted();
        $paginator = $this->get('knp_paginator');
        $ctoUsers = $paginator->paginate(
            $ctoUsersResult,
            $this->get('request')->query->get('page', 1),   /* page number */
            $this->container->getParameter('pagination')    /* limit per page */
        );

        return [
            'ctoUsers' => $ctoUsers
        ];
    }

    /**
     * @Route("/new", name="admin_ctoUser_create")
     * @Method({"GET", "POST"})
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction(Request $request)
    {
        $ctoUser = new CtoUser();
        $form = $this->createForm(new CtoUserType(true), $ctoUser);

        if ($request->getMethod() == "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();

                $encoder = $this->get('security.password_encoder');
                $ctoUser->setPassword($encoder->encodePassword($ctoUser, $ctoUser->getPlainPassword()));
                $ctoUser->setLastLogin(new DateTime('now'));

                $em->persist($ctoUser);
                $em->flush();

                $this->addFlash('success', "{$ctoUser->getCtoName()} успішно створено.");

                return $this->redirectToRoute('admin_ctoUsers_list');
            }
        }

        return [
            'form' => $form->createView(),
            'title' => 'Створити'
        ];
    }

    /**
     * @Route("/edit/{slug}", name="admin_ctoUser_edit")
     * @Method({"GET", "POST"})
     * @Template("@CTOApp/DashboardControllers/Admin/Cto/new.html.twig")
     * @ParamConverter("ctoUser", class="CTOAppBundle:CtoUser", options={"slug"="slug"})
     * @param CtoUser $ctoUser
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(CtoUser $ctoUser, Request $request)
    {
        $form = $this->createForm(new CtoUserType(), $ctoUser);

        if ($request->getMethod() == "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();

                if ($ctoUser->getPlainPassword()) {
                    $encoder = $this->get('security.password_encoder');
                    $ctoUser->setPassword($encoder->encodePassword($ctoUser, $ctoUser->getPlainPassword()));
                }

                $em->flush();

                $this->addFlash('success', "{$ctoUser->getCtoName()} успішно відредаговано.");

                return $this->redirectToRoute('admin_ctoUsers_list');
            }
        }

        return [
            'form' => $form->createView(),
            'title' => 'Редагувати'
        ];
    }

}
