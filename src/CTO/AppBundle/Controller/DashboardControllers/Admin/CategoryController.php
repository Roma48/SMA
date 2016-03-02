<?php

namespace CTO\AppBundle\Controller\DashboardControllers\Admin;

use CTO\AppBundle\Entity\JobCategory;
use CTO\AppBundle\Form\JobCategoryType;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CategoryController
 * @package CTO\AppBundle\Controller\DashboardControllers\Admin
 *
 * @Route("/category")
 */
class CategoryController extends Controller
{
    /**
     * @Route("/list", name="admin_ctoCategory_list")
     * @Method("GET")
     * @Template()
     */
    public function listAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $categoriesResult = $em->getRepository("CTOAppBundle:JobCategory")->findAll();
        $paginator = $this->get('knp_paginator');
        $categories = $paginator->paginate(
            $categoriesResult,
            $this->get('request')->query->get('page', 1),   /* page number */
            $this->container->getParameter('pagination')    /* limit per page */
        );

        return [
            "categories" => $categories
        ];
    }

    /**
     * @Route("/new", name="admin_ctoCategory_create")
     * @Method({"GET", "POST"})
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction(Request $request)
    {
        $category = new JobCategory();
        $form = $this->createForm(new JobCategoryType(true), $category);

        if ($request->getMethod() == Request::METHOD_POST) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();

                $em->persist($category);
                $em->flush();

                $this->addFlash('success', "{$category->getName()} успішно створено.");

                return $this->redirectToRoute('admin_ctoCategory_list');
            }
        }

        return [
            'form' => $form->createView(),
            'title' => 'Створити'
        ];
    }

    /**
     * @Route("/edit/{id}", name="admin_ctoCategory_edit", requirements={"id" = "\d+"})
     * @Method({"GET", "POST"})
     * @Template("@CTOApp/DashboardControllers/Admin/Category/new.html.twig")
     * @param JobCategory $category
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(JobCategory $category, Request $request)
    {
        $form = $this->createForm(new JobCategoryType(), $category);

        if ($request->getMethod() == Request::METHOD_POST) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $em->flush();

                $this->addFlash('success', "{$category->getName()} успішно відредаговано.");

                return $this->redirectToRoute('admin_ctoCategory_list');
            }
        }

        return [
            'form' => $form->createView(),
            'title' => 'Редагувати'
        ];
    }

}
