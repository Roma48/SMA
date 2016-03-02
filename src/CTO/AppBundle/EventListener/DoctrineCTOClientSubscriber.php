<?php

namespace CTO\AppBundle\EventListener;

use CTO\AppBundle\Entity\CarCategory;
use CTO\AppBundle\Entity\CarJob;
use CTO\AppBundle\Entity\CategoryJobDescription;
use CTO\AppBundle\Entity\CtoClient;
use CTO\AppBundle\Entity\PaidSalaryJob;
use CTO\AppBundle\Entity\SpendingJob;
use CTO\AppBundle\Entity\UsedMaterialsJob;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;

class DoctrineCTOClientSubscriber implements EventSubscriber
{
    public function onFlush(OnFlushEventArgs $event)
    {
        /** @var EntityManager $em */
        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            /** @var CtoClient $entity */
            if ($entity instanceof CtoClient) {
                $entity->setFullName($entity->getFirstName()." ".$entity->getLastName());
            }

            /** @var CarJob $entity */
            if ($entity instanceof CarJob) {
                $totalCost = 0;
                /** @var CarCategory $category */
                foreach($entity->getCarCategories() as $category) {
                    /** @var CategoryJobDescription $description */
                    foreach($category->getJobDescriptions() as $description) {
                        $totalCost += $description->getPrice();
                    }
                }
                $entity->setTotalCost($totalCost);
                $entity->getClient()->setTotalCost($entity->getClient()->getTotalCost() + $totalCost);

                $totalSpend = 0;
                /** @var PaidSalaryJob $paidItem */
                foreach($entity->getPaidSalaryJob() as $paidItem) {
                    $totalSpend += $paidItem->getPrice();
                }
                $entity->setTotalSpend($totalSpend);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            /** @var CtoClient $entity */
            if ($entity instanceof CtoClient) {
                $entity->setFullName($entity->getFirstName()." ".$entity->getLastName());
            }

            /** @var CarJob $entity */
            if ($entity instanceof CarJob) {
//                $client = $entity->get
                $totalCost = 0;
                /** @var CarCategory $category */
                foreach($entity->getCarCategories() as $category) {
                    /** @var CategoryJobDescription $description */
                    foreach($category->getJobDescriptions() as $description) {
                        $totalCost += $description->getPrice();
                    }
                }
                $entity->setTotalCost($totalCost);
                $entity->getClient()->setTotalCost($entity->getClient()->getTotalCost() + $totalCost);

                $totalSpend = 0;
                /** @var PaidSalaryJob $paidItem */
                foreach($entity->getPaidSalaryJob() as $paidItem) {
                    $totalSpend += $paidItem->getPrice();
                }
                $entity->setTotalSpend($totalSpend);
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {

        }

        foreach ($uow->getScheduledCollectionDeletions() as $col) {

        }

        foreach ($uow->getScheduledCollectionUpdates() as $col) {

        }
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush'
        ];
    }
}
