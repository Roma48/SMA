<?php

namespace CTO\AppBundle\Controller\DashboardControllers\CTO;

use Carbon\Carbon;
use CTO\AppBundle\Entity\CarJob;
use CTO\AppBundle\Entity\CtoUser;
use CTO\AppBundle\Entity\Notification;
use CTO\AppBundle\Entity\NotificationReport;
use CTO\AppBundle\Form\BroadcastType;
use CTO\AppBundle\Form\JobNotificationReminderType;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/notifications" )
 */
class NotificationsController extends Controller
{
    /**
     * @Route("/{tabName}", name="cto_notification_home", defaults={"tabName"="current"}, requirements={"tabName" = "current|planned|sentout|last"})
     * @Method("GET")
     * @Template()
     */
    public function homeAction($tabName)
    {
        $now = Carbon::now();
        $from = $now->copy()->startOfDay();
        $to = $now->copy()->endOfDay();

        /** @var CtoUser $user */
        $user = $this->getUser();
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $currentsR = $em->getRepository('CTOAppBundle:Notification')->getCurrents($user, $from, $to);
        $plannedR = $em->getRepository('CTOAppBundle:Notification')->getPlanned($user, $to);
        $sentOutR = $em->getRepository('CTOAppBundle:Notification')->getSentOut($user);
        $lastsR  = $em->getRepository('CTOAppBundle:Notification')->getLast($user, $from);

        $paginator = $this->get('knp_paginator');

        $currents = $paginator->paginate(
            $currentsR,
            $this->get('request')->query->get('page', 1),
            $this->container->getParameter('pagination')
        );
        $planned = $paginator->paginate(
            $plannedR,
            $this->get('request')->query->get('page', 1),
            $this->container->getParameter('pagination')
        );
        $sentOut = $paginator->paginate(
            $sentOutR,
            $this->get('request')->query->get('page', 1),
            $this->container->getParameter('pagination')
        );
        $lasts = $paginator->paginate(
            $lastsR,
            $this->get('request')->query->get('page', 1),
            $this->container->getParameter('pagination')
        );

        return [
            'tabName' => $tabName,
            'currents' => $currents,
            'planned' => $planned,
            'lasts' => $lasts,
            'sentout' => $sentOut
        ];
    }

    /**
     * @param Notification $notification
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/skip/{id}", name="cto_notification_skip");
     * @Method("GET")
     */
    public function skip(Notification $notification)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        if ($notification->getResqueJobDescription()) {
            $this->get('cto.sms.sender')->stop($notification->getResqueJobDescription());
        }

        $notification
            ->setStatus(Notification::STATUS_ABORTED);
        $em->flush();

        $this->addFlash('success', 'Нагадування успішно відмінено.');

        return $this->redirectToRoute('cto_notification_home');
    }

    /**
     * @param Notification $notification
     * @param Request $request
     * @return array
     *
     * @Route("/edit/{id}", name="cto_notification_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Notification $notification, Request $request)
    {
        /** @var CarJob $carJob */
        $carJob = $notification->getCarJob();
        $form = $this->createForm(new JobNotificationReminderType(), $notification);

        if ($request->getMethod() == Request::METHOD_POST) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $now = Carbon::now();

                if ($notification->getWhenSend() < $now) {
                    if ($notification->isAutoSending() and $notification->isSendNow()) {
                        $notification->setAutoSending(true);
                    } else {
                        $notification->setAutoSending(false);
                    }
                }

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $this->get('cto.sms.sender')->stop($notification->getResqueJobDescription());

                if ($notification->isAutoSending()) {
                    $senderSrv = $this->get('cto.sms.sender');
                    /** @var CtoUser $admin */
                    $admin = $this->getUser();

                    if ($notification->isSendNow()) {
//                        $senderSrv->sendNow($notification, $admin);

                        $senderSrv->getResqueManager()->put('cto.sms.sender', [
                            'notificationId' => $notification->getId(),
                            'broadcast' => false
                        ]);

                    } else {
                        $jobDescription = $senderSrv->getResqueManager()->put('cto.sms.sender', [
                            'notificationId' => $notification->getId(),
                            'broadcast' => false
                        ], $this->getParameter('queue_name'), $notification->getWhenSend());
                        $notification->setResqueJobDescription($jobDescription);
                    }
                }
                $em->flush();
                $this->addFlash('success', 'Нагадування успішно відредаговано.');

                return $this->redirectToRoute('cto_notification_home');
            }
        }

        return [
            'client' => $carJob->getClient()->getFullName(),
            'auto' => $carJob->getCar()->getCarModel(),
            'form' => $form->createView()
        ];
    }

    /**
     * @param Notification $notification
     * @param Request $request
     * @return array
     *
     * @Route("/copy/{id}", name="cto_notification_copy")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function copyAction(Notification $notification, Request $request)
    {
        /** @var CtoUser $admin */
        $admin = $this->getUser();

        $newNotification = new Notification();
        $newNotification
            ->setStatus(Notification::STATUS_SEND_IN_PROGRESS)
            ->setAdminCopy($notification->isAdminCopy())
            ->setAutoSending($notification->isAutoSending())
            ->setCarJob($notification->getCarJob())
            ->setJobCategory($notification->getJobCategory())
            ->setClientCto($notification->getClientCto())
            ->setWhenSend($notification->getWhenSend())
            ->setType($notification->getType())
            ->setUserCto($admin)
            ->setDescription($notification->getDescription());

        /** @var CarJob $carJob */
        $carJob = $notification->getCarJob();
        $form = $this->createForm(new JobNotificationReminderType(), $newNotification);

        if ($request->getMethod() == Request::METHOD_POST) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $now = Carbon::now();

                if ($notification->getWhenSend() < $now) {
                    if ($notification->isAutoSending() and $notification->isSendNow()) {
                        $notification->setAutoSending(true);
                    } else {
                        $notification->setAutoSending(false);
                    }
                }

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $em->persist($newNotification);
                $em->flush();

                if ($newNotification->isAutoSending()) {
                    $senderSrv = $this->get('cto.sms.sender');

                    if ($newNotification->isSendNow()) {
//                        $senderSrv->sendNow($newNotification, $admin);

                        $senderSrv->getResqueManager()->put('cto.sms.sender', [
                            'notificationId' => $newNotification->getId(),
                            'broadcast' => false
                        ]);

                    } else {
                        $jobDescription = $senderSrv->getResqueManager()->put('cto.sms.sender', [
                            'notificationId' => $newNotification->getId(),
                            'broadcast' => false
                        ], $this->getParameter('queue_name'), $newNotification->getWhenSend());
                        $newNotification->setResqueJobDescription($jobDescription);
                    }
                }
                $em->flush();
                $this->addFlash('success', 'Нагадування успішно створено.');

                return $this->redirectToRoute('cto_notification_home');
            }
        }

        return [
            'client' => $carJob->getClient()->getFullName(),
            'auto' => $carJob->getCar()->getCarModel(),
            'form' => $form->createView()
        ];
    }

    /**
     * @param Request $request
     * @return array
     *
     * @Route("/broadcast", name="cto_notification_broadcast")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function broadcastAction(Request $request)
    {
        /** @var CtoUser $admin */
        $admin = $this->getUser();

        $notification = new Notification();
        $notification
            ->setStatus(Notification::STATUS_SEND_IN_PROGRESS)
            ->setUserCto($admin)
            ->setType(Notification::TYPE_BROADCAST);

        $form = $this->createForm(new BroadcastType(), $notification);

        if ($request->getMethod() == Request::METHOD_POST) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $now = Carbon::now();

                if ($notification->getWhenSend() < $now) {
                    if ($notification->isAutoSending() and $notification->isSendNow()) {
                        $notification->setAutoSending(true);
                    } else {
                        $notification->setAutoSending(false);
                    }
                }

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $em->persist($notification);
                $em->flush();

                if ($notification->isAutoSending()) {
                    $senderSrv = $this->get('cto.sms.sender');

                    if ($notification->isSendNow()) {
//                        $senderSrv->sendNow($notification, $admin, true);

//                        $senderSrv->getResqueManager()->put('cto.sms.sender', [
//                            'notificationId' => $notification->getId(),
//                            'broadcast' => true,
//                        ]);

                        $senderSrv->execute([
                            'notificationId' => $notification->getId(),
                            'broadcast' => true,
                        ]);

                    } else {
                        $jobDescription = $senderSrv->getResqueManager()->put('cto.sms.sender', [
                            'notificationId' => $notification->getId(),
                            'broadcast' => true
                        ], $this->getParameter('queue_name'), $notification->getWhenSend());
                        $notification->setResqueJobDescription($jobDescription);

                        $em->flush();
                    }
                }

                $this->addFlash('success', 'Розсилка успішно створена.');

                return $this->redirectToRoute('cto_notification_home');
            }
        }

        return [
            'form' => $form->createView(),
            'method' => 'Створити'
        ];
    }

    /**
     * @param Request $request
     * @param Notification $notification
     * @return array
     *
     * @Route("/broadcast/edit/{id}", name="cto_notification_broadcastEdit")
     * @Method({"GET", "POST"})
     * @Template("@CTOApp/DashboardControllers/CTO/Notifications/broadcast.html.twig")
     */
    public function broadcastEditAction(Request $request, Notification $notification)
    {
        /** @var CtoUser $admin */
        $admin = $this->getUser();
        $form = $this->createForm(new BroadcastType(), $notification);

        if ($request->getMethod() == Request::METHOD_POST) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $now = Carbon::now();

                if ($notification->getWhenSend() < $now) {
                    if ($notification->isAutoSending() and $notification->isSendNow()) {
                        $notification->setAutoSending(true);
                    } else {
                        $notification->setAutoSending(false);
                    }
                }

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                if ($notification->getResqueJobDescription()) {
                    $this->get('cto.sms.sender')->stop($notification->getResqueJobDescription());
                }

                if ($notification->isAutoSending()) {
                    $senderSrv = $this->get('cto.sms.sender');

                    if ($notification->isSendNow()) {
//                        $senderSrv->sendNow($notification, $admin, true);

                        $senderSrv->getResqueManager()->put('cto.sms.sender', [
                            'notificationId' => $notification->getId(),
                            'broadcast' => true
                        ]);

                    } else {
                        $jobDescription = $senderSrv->getResqueManager()->put('cto.sms.sender', [
                            'notificationId' => $notification->getId(),
                            'broadcast' => true
                        ], $this->getParameter('queue_name'), $notification->getWhenSend());
                        $notification->setResqueJobDescription($jobDescription);
                    }
                }

                $em->flush();
                $this->addFlash('success', 'Нагадування успішно відредаговано.');

                return $this->redirectToRoute('cto_notification_home');
            }
        }

        return [
            'form' => $form->createView(),
            'method' => 'Редагувати'
        ];
    }

    /**
     * @param Notification $notification
     * @return JsonResponse
     *
     * @Route("/report/{id}/show", name="admin_cto_reports")
     * @Method("GET")
     * @Template()
     */
    public function showRepostAction(Notification $notification)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var NotificationReport[] $notifications */
        $notifications = $em->getRepository("CTOAppBundle:NotificationReport")->findBySomeNotification($notification);

        return [
            "notifications" => $notifications
        ];
    }

    /**
     * @param Request $request
     * @param Notification $notification
     * @return array
     *
     * @Route("/broadcast/copy/{id}", name="cto_notification_broadcastCopy")
     * @Method({"GET", "POST"})
     * @Template("@CTOApp/DashboardControllers/CTO/Notifications/broadcast.html.twig")
     */
    public function broadcastCopyAction(Request $request, Notification $notification)
    {
        /** @var CtoUser $admin */
        $admin = $this->getUser();

        $newNotification = new Notification();
        $newNotification
            ->setStatus(Notification::STATUS_SEND_IN_PROGRESS)
            ->setAdminCopy($notification->isAdminCopy())
            ->setAutoSending($notification->isAutoSending())
            ->setWhenSend($notification->getWhenSend())
            ->setBroadcastTo($notification->getBroadcastTo())
            ->setType(Notification::TYPE_BROADCAST)
            ->setUserCto($admin)
            ->setDescription($notification->getDescription());


        $form = $this->createForm(new BroadcastType(), $newNotification);

        if ($request->getMethod() == Request::METHOD_POST) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $now = Carbon::now();

                if ($notification->getWhenSend() < $now) {
                    if ($notification->isAutoSending() and $notification->isSendNow()) {
                        $notification->setAutoSending(true);
                    } else {
                        $notification->setAutoSending(false);
                    }
                }

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $em->persist($newNotification);
                $em->flush();

                if ($notification->isAutoSending()) {
                    $senderSrv = $this->get('cto.sms.sender');

                    if ($notification->isSendNow()) {
                        $senderSrv->sendNow($newNotification, $admin, true);
                    } else {
                        $jobDescription = $senderSrv->getResqueManager()->put('cto.sms.sender', [
                            'notificationId' => $newNotification->getId(),
                            'broadcast' => true
                        ], $this->getParameter('queue_name'), $newNotification->getWhenSend());
                        $newNotification->setResqueJobDescription($jobDescription);
                    }
                }

                $em->flush();
                $this->addFlash('success', 'Нагадування успішно створено.');

                return $this->redirectToRoute('cto_notification_home');
            }
        }

        return [
            'form' => $form->createView(),
            'method' => 'Копіювати'
        ];
    }
}
