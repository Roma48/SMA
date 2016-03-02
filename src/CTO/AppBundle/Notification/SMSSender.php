<?php

namespace CTO\AppBundle\Notification;

use CTO\AppBundle\Entity\CtoClient;
use CTO\AppBundle\Entity\CtoUser;
use CTO\AppBundle\Entity\Notification;
use CTO\AppBundle\Entity\NotificationReport;
use Doctrine\ORM\EntityManager;
use Mcfedr\ResqueBundle\Manager\ResqueManager;
use Mcfedr\ResqueBundle\Worker\WorkerInterface;

class SMSSender implements WorkerInterface
{
    /** @var EntityManager $em */
    protected $em;
    protected $queueName;

    /** @var  ResqueManager */
    protected $resqueManager;

    protected $alfa_sms_name;
    protected $alfa_sms_ID;
    protected $alfa_sms_password;
    protected $alfa_sms_api_key;

    /** @var SMSClient $clientSMS */
    protected $clientSMS;

    public function __construct(
        EntityManager $entityManager,
        ResqueManager $resqueManager,
        $queue_name,
        $alfa_sms_name,
        $alfa_sms_ID,
        $alfa_sms_password,
        $alfa_sms_api_key
    ) {
        $this->em = $entityManager;
        $this->resqueManager = $resqueManager;
        $this->queueName = $queue_name;

        $this->clientSMS = new SMSClient($alfa_sms_ID, $alfa_sms_password, $alfa_sms_api_key);
        $this->alfa_sms_name = $alfa_sms_name;
    }

    public function getResqueManager()
    {
        return $this->resqueManager;
    }

    public function getBalance()
    {
        return $this->clientSMS->getBalance();
    }

    public function sendNow(Notification $notification, CtoUser $admin, $broadcast = false)
    {
        if ($broadcast) {

            //   Broadcast

            $iDs = explode(',', $notification->getBroadcastTo());

            if (in_array('-1', $iDs)) {
                $admin = $notification->getUserCto();
                $users = $this->em->getRepository('CTOAppBundle:CtoClient')->clientFilter([], $admin);
                $notification->setBroadcastTo(-1);
            } else {
                $users = $this->em->getRepository('CTOAppBundle:CtoClient')->findBy(['id' => $iDs]);
            }

            /** @var CtoClient $user */
            foreach ($users as $user) {
                try {
                    $this->clientSMS->sendSMS($this->alfa_sms_name, '+38'.$user->getPhone(), $notification->getDescription());
                    $report = new NotificationReport($user);
                    $report
                        ->setPhone($user->getPhone())
                        ->setStatus(NotificationReport::REPORT_STATUS_SENDED);
                    $this->em->persist($report);
                    $notification->addReport($report);
                    $notification->setStatus(Notification::STATUS_SEND_OK);
                } catch (\Exception $e) {
                    $report = new NotificationReport($user);
                    $report
                        ->setPhone($user->getPhone())
                        ->setStatus(NotificationReport::REPORT_STATUS_FAILED);
                    $this->em->persist($report);
                    $notification->addReport($report);
                    $notification->setStatus(Notification::STATUS_SEND_FAIL);
                }
            }
            if ($notification->isAdminCopy()) {
                try {
                    $this->clientSMS->sendSMS($this->alfa_sms_name, '+38'.$admin->getPhone(), $notification->getDescription());
                    $report = new NotificationReport($notification->getClientCto());
                    $report
                        ->setPhone($admin->getPhone())
                        ->setStatus(NotificationReport::REPORT_STATUS_SENDED);
                    $this->em->persist($report);
                    $notification->addReport($report);
                } catch (\Exception $e) {
                    $report = new NotificationReport($notification->getClientCto());
                    $report
                        ->setPhone($admin->getPhone())
                        ->setSendToAdmin(true)
                        ->setStatus(NotificationReport::REPORT_STATUS_SENDED);
                    $this->em->persist($report);
                }
            }

            $this->em->flush();
        } else {

            //  Normal

            try {
                $this->clientSMS->sendSMS($this->alfa_sms_name, '+38'.$notification->getClientCto()->getPhone(), $notification->getDescription());
                $report = new NotificationReport($notification->getClientCto());
                $report
                    ->setPhone($notification->getClientCto()->getPhone())
                    ->setStatus(NotificationReport::REPORT_STATUS_SENDED);
                $this->em->persist($report);
                $notification->addReport($report);
                $notification->setStatus(Notification::STATUS_SEND_OK);
            } catch (\Exception $e) {
                $report = new NotificationReport($notification->getClientCto());
                $report
                    ->setPhone($notification->getClientCto()->getPhone())
                    ->setStatus(NotificationReport::REPORT_STATUS_FAILED);
                $this->em->persist($report);
                $notification->addReport($report);
                $notification->setStatus(Notification::STATUS_SEND_FAIL);
            }
            if ($notification->isAdminCopy()) {
                try {
                    $this->clientSMS->sendSMS($this->alfa_sms_name, '+38'.$admin->getPhone(), $notification->getDescription());
                    $report = new NotificationReport($notification->getClientCto());
                    $report
                        ->setPhone($admin->getPhone())
                        ->setSendToAdmin(true)
                        ->setStatus(NotificationReport::REPORT_STATUS_SENDED);
                    $this->em->persist($report);
                    $notification->addReport($report);
                } catch (\Exception $e) {
                    $report = new NotificationReport($notification->getClientCto());
                    $report
                        ->setPhone($admin->getPhone())
                        ->setSendToAdmin(true)
                        ->setStatus(NotificationReport::REPORT_STATUS_FAILED);
                    $this->em->persist($report);
                }
            }
            $this->em->flush();
        }
    }

    public function stop($jobDescription)
    {
        try {
            $this->resqueManager->delete($jobDescription);
        } catch(\Exception $e) {}
    }

    /**
     * Called to start the queued task
     *
     * @param array $options
     * @throws \Exception
     */
    public function execute(array $options = null)
    {
        $notificationId = $options['notificationId'];
        $broadcast = $options['broadcast'];

        $notification = $this->em->getRepository('CTOAppBundle:Notification')->find($notificationId);
        $ctoClient = $notification->getClientCto();
        $admin = $notification->getUserCto();

        if ($broadcast) {

            $iDs = explode(',', $notification->getBroadcastTo());

            if (in_array('-1', $iDs)) {
                $admin = $notification->getUserCto();
                $users = $this->em->getRepository('CTOAppBundle:CtoClient')->clientFilter([], $admin);
                $notification->setBroadcastTo(-1);
            } else {
                $users = $this->em->getRepository('CTOAppBundle:CtoClient')->findBy(['id' => $iDs]);
            }

            /** @var CtoClient $user */
            foreach ($users as $user) {
                try {
                    $this->clientSMS->sendSMS($this->alfa_sms_name, '+38'.$user->getPhone(), $notification->getDescription());

                    $report = new NotificationReport($user);
                    $report
                        ->setPhone($user->getPhone())
                        ->setStatus(NotificationReport::REPORT_STATUS_SENDED);
                    $this->em->persist($report);
                    $notification->addReport($report);

                    $notification->setStatus(Notification::STATUS_SEND_OK);
                    $this->em->flush();
                } catch (\Exception $e) {

                    $report = new NotificationReport($user);
                    $report
                        ->setPhone($user->getPhone())
                        ->setStatus(NotificationReport::REPORT_STATUS_FAILED);
                    $this->em->persist($report);
                    $notification->addReport($report);

                    $notification->setStatus(Notification::STATUS_SEND_FAIL);
                    $this->em->flush();
                }
            }
            if ($notification->isAdminCopy()) {
                try {
                    $this->clientSMS->sendSMS($this->alfa_sms_name, '+38'.$admin->getPhone(), $notification->getDescription());
                    $report = new NotificationReport($user);
                    $report
                        ->setPhone($admin->getPhone())
                        ->setSendToAdmin(true)
                        ->setStatus(NotificationReport::REPORT_STATUS_SENDED);
                    $this->em->persist($report);
                    $notification->addReport($report);
                } catch (\Exception $e) {
                    $report = new NotificationReport($user);
                    $report
                        ->setPhone($admin->getPhone())
                        ->setSendToAdmin(true)
                        ->setStatus(NotificationReport::REPORT_STATUS_FAILED);
                    $this->em->persist($report);
                    $notification->addReport($report);

                }
            }
            $this->em->flush();

            return;
        }

        try {
            $this->clientSMS->sendSMS($this->alfa_sms_name, '+38'.$ctoClient->getPhone(), $notification->getDescription());
            if ($notification->isAdminCopy()) {
                try {
                    $this->clientSMS->sendSMS($this->alfa_sms_name, '+38'.$admin->getPhone(), $notification->getDescription());
                } catch(\Exception $e) {

                    $report = new NotificationReport($ctoClient);
                    $report
                        ->setPhone($admin->getPhone())
                        ->setSendToAdmin(true)
                        ->setStatus(NotificationReport::REPORT_STATUS_FAILED);
                    $this->em->persist($report);
                    $notification->addReport($report);

                    $notification->setStatus(Notification::STATUS_SEND_FAIL);
                    $this->em->flush();

                    return;
                }

                $report = new NotificationReport($ctoClient);
                $report
                    ->setPhone($admin->getPhone())
                    ->setSendToAdmin(true)
                    ->setStatus(NotificationReport::REPORT_STATUS_SENDED);
                $this->em->persist($report);
                $notification->addReport($report);
            }

            $report = new NotificationReport($ctoClient);
            $report
                ->setPhone($ctoClient->getPhone())
                ->setStatus(NotificationReport::REPORT_STATUS_SENDED);
            $this->em->persist($report);
            $notification->addReport($report);
            $notification->setStatus(Notification::STATUS_SEND_OK);
            $this->em->flush();
        } catch (\Exception $e) {

            $report = new NotificationReport($ctoClient);
            $report
                ->setPhone($ctoClient->getPhone())
                ->setStatus(NotificationReport::REPORT_STATUS_FAILED);
            $this->em->persist($report);
            $notification->addReport($report);

            $notification->setStatus(Notification::STATUS_SEND_FAIL);
            $this->em->flush();
        }
    }
}
