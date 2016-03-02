<?php

namespace CTO\AppBundle\Controller\DashboardControllers\CTO;

use CTO\AppBundle\Entity\CtoClient;
use CTO\AppBundle\Entity\CtoClientNotes;
use CTO\AppBundle\Entity\CtoUser;
use CTO\AppBundle\Form\ClientCtoNotesType;
use CTO\AppBundle\Form\CtoClientFilterType;
use CTO\AppBundle\Form\CtoClientForModalType;
use CTO\AppBundle\Form\CtoClientType;
use DateTime;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/clients")
 */
class ClientsController extends Controller
{
    /**
     * @Route("/", name="cto_client_home")
     * @Method("GET")
     * @Template()
     */
    public function homeAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var CtoUser $cto */
        $cto = $this->getUser();
        $usersResult = $em->getRepository("CTOAppBundle:CtoClient")->listClientwWithSorting($cto);

        $paginator = $this->get('knp_paginator');
        $clients = $paginator->paginate(
            $usersResult,
            $this->get('request')->query->get('page', 1),   /* page number */
            $this->container->getParameter('pagination')    /* limit per page */
        );

        $filterForm = $this->createForm(new CtoClientFilterType());

        return [
            'clients' => $clients,
            'filterForm' => $filterForm->createView()
        ];
    }

    /**
     * @Route("/new", name="cto_client_new")
     * @Method({"GET", "POST"})
     * @Template()
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function newAction(Request $request)
    {
        $client = new CtoClient();
        $form = $this->createForm(new CtoClientType(), $client);
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var CtoUser $user */
        $user = $this->getUser();

        if ($request->getMethod() == "POST") {
            $form->handleRequest($request);

            $telCheck = $em->getRepository("CTOAppBundle:CtoClient")->findOneBy(['phone' => $client->getPhone()]);

            if ($telCheck) {
                $formPhoneError = new FormError("Цей телефон вже використовується іншим клієнтом");
                $form->get("phone")->addError($formPhoneError);
            }

            if ($form->isValid()) {
                $client
                    ->setLastVisitDate(new DateTime('now'))
                    ->setCity($user->getCity());

                $user->addClient($client);
                $em->persist($client);
                $em->flush();

                $this->addFlash('success', "Клієнт {$client->getFirstName()} {$client->getLastName()} успішно створено.");

                return $this->redirect($this->generateUrl('cto_client_home'));
            }
        }

        return [
            'info' => 'Новий клієнт:',
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/edit/{slug}", name="cto_client_edit")
     * @Method({"GET", "POST"})
     * @Template("@CTOApp/DashboardControllers/CTO/Clients/new.html.twig")
     * @ParamConverter("ctoClient", class="CTOAppBundle:CtoClient", options={"slug" = "slug"})
     * @param CtoClient $ctoClient
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(CtoClient $ctoClient, Request $request)
    {
        $form = $this->createForm(new CtoClientType(), $ctoClient);

        if ($request->getMethod() == "POST") {
            $form->handleRequest($request);
            if ($form->isValid()) {

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $em->flush();

                $this->addFlash('success', "Клієнт {$ctoClient->getFirstName()} {$ctoClient->getLastName()} успішно відредаговано.");

                return $this->redirect($this->generateUrl('cto_client_home'));
            }
        }

        return [
            'info' => "{$ctoClient->getFirstName()} {$ctoClient->getLastName()}: редагування",
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/filter", name="cto_client_filter")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function filterAction(Request $request)
    {
        /** @var array $filterFormData */
        $filterFormData = $request->get('cto_client_filter', null);
        if ($filterFormData) {
            $filterFormData = array_filter($filterFormData);
        }

        /** @var CtoUser $ctoUser */
        $ctoUser = $this->getUser();

        $withPaginator = false;

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        if ($filterFormData) {
            $usersResult = $em->getRepository('CTOAppBundle:CtoClient')->clientFilter($filterFormData, $ctoUser);
        } else {
            $withPaginator = true;
            $usersResult = $em->getRepository("CTOAppBundle:CtoClient")->listClientwWithSorting($ctoUser);

            $paginator = $this->get('knp_paginator');
            $clients = $paginator->paginate(
                $usersResult,
                $this->get('request')->query->get('page', 1),   /* page number */
                $this->container->getParameter('pagination')    /* limit per page */
            );
        }

        $filterForm = $this->createForm(new CtoClientFilterType(), $filterFormData);

        return [
            'clients' => $withPaginator ? $clients : $usersResult,
            'paginator' => $withPaginator,
            'filterForm' => $filterForm->createView()
        ];
    }

    /**
     * @Route("/note/rm/{id}", name="cto_client_removeNote", requirements={"id"="\d+"})
     * @param CtoClientNotes $note
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeCtoClientNoteAction(CtoClientNotes $note)
    {
        $ctoClient = $note->getClientCto();
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $em->remove($note);
        $em->flush();

        return $this->redirectToRoute("cto_client_show", ["slug" => $ctoClient->getSlug()]);
    }

    /**
     * @param CtoClient $ctoClient
     * @param $tabName
     * @param Request $request
     * @return array
     *
     * @Route("/show/{slug}/{tabName}", name="cto_client_show", defaults={"tabName" = "info"}, requirements={"tabName" = "info|jobs"})
     * @Method({"GET", "POST"})
     * @ParamConverter("ctoClient", class="CTOAppBundle:CtoClient", options={"slug" = "slug"})
     * @Template()
     */
    public function showAction(CtoClient $ctoClient, $tabName, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $visitsCount = $em->getRepository("CTOAppBundle:CarJob")->countForMonthByClient($this->getUser(), $ctoClient);
        $notesRes = $em->getRepository("CTOAppBundle:CtoClientNotes")->findBy(["clientCto" => $ctoClient], ["createdAt" => "DESC"]);

        $paginator = $this->get('knp_paginator');
        $notes = $paginator->paginate(
            $notesRes,
            $this->get('request')->query->get('page', 1),   /* page number */
            5                                               /* limit per page */
        );

        $note = new CtoClientNotes();
        $form = $this->createForm(new ClientCtoNotesType(), $note);

        if ($request->getMethod() == Request::METHOD_POST) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->persist($note);
                $ctoClient->addNote($note);
                $em->flush();

                return $this->redirectToRoute("cto_client_show", ["tabName" => $tabName, "slug" => $ctoClient->getSlug()]);
            }
        }

        return [
            "client" => $ctoClient,
            "notes" => $notes,
            "form" => $form->createView(),
            'visits' => $visitsCount['jobs'],
            "tabName" => $tabName
        ];
    }

    /**
     * @Route("/new/modal/{back}", name="cto_client_new_from_modal", requirements={"back"="\d+|new"})
     * @Method({"GET", "POST"})
     * @Template()
     * @param $back
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addClientFromModalAction($back, Request $request)
    {
        $client = new CtoClient();
        $form = $this->createForm(new CtoClientForModalType(), $client);
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var CtoUser $user */
        $user = $this->getUser();

        if ($request->getMethod() == "POST") {
            $form->handleRequest($request);

            $telCheck = $em->getRepository("CTOAppBundle:CtoClient")->findOneBy(['phone' => $client->getPhone()]);

            if ($telCheck) {
                $formPhoneError = new FormError("Цей телефон вже використовується іншим клієнтом");
                $form->get("phone")->addError($formPhoneError);
            }
            if ($form->isValid()) {

                $client
                    ->setLastVisitDate(new DateTime('now'))
                    ->setCity($user->getCity());

                $user->addClient($client);
                $em->persist($client);
                $em->flush();

                if ($back == "new") {

                    return $this->redirectToRoute("cto_jobs_new");
                } else {

                    return$this->redirectToRoute('cto_jobs_edit', ['id' => $back]);
                }
            }
            if ($back == "new") {

                return $this->redirectToRoute("cto_jobs_new");
            } else {

                return$this->redirectToRoute('cto_jobs_edit', ['id' => $back]);
            }
        }

        return [
            'info' => 'Новий клієнт:',
            'form' => $form->createView(),
            'back' => $back
        ];
    }
}
