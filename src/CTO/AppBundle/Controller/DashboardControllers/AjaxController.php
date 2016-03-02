<?php

namespace CTO\AppBundle\Controller\DashboardControllers;

use Carbon\Carbon;
use CTO\AppBundle\Entity\AdminUser;
use CTO\AppBundle\Entity\CarJob;
use CTO\AppBundle\Entity\CtoClient;
use CTO\AppBundle\Entity\CtoUser;
use CTO\AppBundle\Entity\JobPicture;
use CTO\AppBundle\Entity\Notification;
use Doctrine\ORM\EntityManager;
use finfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AjaxController extends Controller
{
    /**
     * @Route("/cto/ajax/getctoclients", name="ajax_cto_get_clients", options={"expose" = true})
     * @Method("GET")
     */
    public function getAllCtoClientsAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $ctoClients = $em->getRepository("CTOAppBundle:CtoClient")->findBy(['cto' => $this->getUser()]);

        return new JsonResponse(["clients" => $ctoClients]);
    }

    /**
     * @return JsonResponse
     * @Route("/cto/ajax/getctomasters", name="ajax_cto_get_masters", options={"expose" = true})
     * @Method("GET")
     */
    public function getAllMastersAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $masters = $em->getRepository("CTOAppBundle:Master")->findBy(['cto' => $this->getUser()]);

        return new JsonResponse(["masters" => $masters]);
    }

    /**
     * @Route("/cto/ajax/getjobcategories", name="ajax_cto_get_jobCategories", options={"expose" = true})
     * @Method("GET")
     */
    public function getAllJobCategoriesAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository("CTOAppBundle:JobCategory")->findAll();
        $masters = $em->getRepository("CTOAppBundle:Master")->findBy(['cto' => $this->getUser()]);

        return new JsonResponse([
            "categories" => $categories,
            "masters" => $masters,
        ]);
    }

    /**
     * @param CarJob $carJob
     * @return JsonResponse
     * @Route("/cto/ajax/getJob/{id}", name="ajax_cto_getJobById", options={"expose" = true})
     * @Method("GET")
     */
    public function getJob(CarJob $carJob)
    {
        return new JsonResponse(["job" => $carJob]);
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/cto/ajax/addedPicturesToJob/{id}", name="ajax_cto_addedPicturesToJob", options={"expose" = true})
     * @Method("POST")
     */
    public function addPicturesToJobAction($id, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $job = $em->getRepository("CTOAppBundle:CarJob")->find((int)$id);
        $files = $request->files->all();

        if ($job and $job instanceof CarJob) {
            $ctoId = $job->getClient()->getCto()->getId();
            $s3 = $this->get("cto.aws.s3");

            /** @var UploadedFile $file */
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {

                    $filePath = $file->getRealPath();
                    $fileName = $file->getClientOriginalName();
                    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $fileInfo->file($filePath);
                    $name = "pri4a_id_{$ctoId}/job_id_{$job->getId()}" . "__" . $fileName . "__" . uniqid() . ".jpg";

                    if ($s3->upload($name, $filePath, $mimeType)) {
                        $picture = new JobPicture();
                        $picture
                            ->setPath($name)
                            ->setJob($job);
                        $em->persist($picture);
                        $job->addPicture($picture);
                    }
                }
            }
            $em->flush();

            return new JsonResponse(["status" => "ok"], 200);
        }

        return new JsonResponse(["status" => "fail"], 400);
    }

    /**
     * @param Notification $notification
     * @return JsonResponse
     *
     * @Route("/cto/ajax/report/{id}/show", name="ajax_cto_getReports", options={"expose" = true})
     * @Method("POST")
     */
    public function showRepostAction(Notification $notification)
    {
        return new JsonResponse([
            "reports" => $notification->getReports()->getValues()
        ]);
    }

    /**
     * @param JobPicture $picture
     * @return JsonResponse
     *
     * @Route("/cto/ajax/removePictureFromJob/{id}", name="ajax_cto_removePictureFromJob", options={"expose" = true})
     * @Method("POST")
     */
    public function removePictureFromJobAction(JobPicture $picture)
    {
        $job = $picture->getJob();
        $s3 = $this->get("cto.aws.s3");
        if ($s3->remove($picture->getPath())) {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $job->removePicture($picture);
            $em->remove($picture);
            $em->flush();

            return new JsonResponse(["status" => "ok"], 200);
        }

        return new JsonResponse(["status" => "fail"], 400);
    }

    /**
     * @Route("/getAllModels", name="cto_models_getAllListForModal", options={"expose"=true})
     * @Method("POST")
     */
    public function getModelsAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $models = $em->getRepository("CTOAppBundle:Model")->findAll();

        return new JsonResponse(['models' => $models]);
    }

    /**
     * @Route("/getBadgeNotificationForLastThreeDays", name="ajax_notification_badgeLast3days", options={"expose"=true})
     * @Method("GET")
     */
    public function getBadgeNotificationForLastThreeDaysAction()
    {
        $now = Carbon::now();
        $from = $now->copy();
        $to = $now->copy()->endOfDay();

        /** @var CtoUser $user */
        $user = $this->getUser();

        if ($user instanceof AdminUser) {

            return new JsonResponse(['nitificationsCount' => 0]);
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $notificationCount = $em->getRepository("CTOAppBundle:Notification")->getNotificationCountForLast3day($user, $from, $to);

        return new JsonResponse(['nitificationsCount' => (int)$notificationCount['NotifCount']]);
    }

    /**
     * @Route("/notifications/users", name="cto_notification_broadcastGetUsersAjax", options={"expose"=true})
     * @Method("GET")
     */
    public function getUsersForBroadcastAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var CtoUser $user */
        $user = $this->getUser();
        $users = $em->getRepository('CTOAppBundle:CtoClient')->clientFilter([], $user);

        return new JsonResponse(['users' => $users]);
    }
}
