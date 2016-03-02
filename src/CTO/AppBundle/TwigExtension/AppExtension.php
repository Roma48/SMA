<?php

namespace CTO\AppBundle\TwigExtension;

use CTO\AppBundle\Entity\CtoClient;
use Doctrine\ORM\EntityManager;

class AppExtension extends \Twig_Extension
{
    /** @var EntityManager $em */
    protected $em;
    protected $baseUrl;

    public function __construct(EntityManager $manager, $baseUrl)
    {
        $this->em = $manager;
        $this->baseUrl = $baseUrl;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('monthTranslate', array($this, 'monthTranslateFilter')),
            new \Twig_SimpleFilter('userNames', array($this, 'transformIdToUsernames')),
            new \Twig_SimpleFilter('S3Path', array($this, 'S3PathFilter')),
        );
    }

    public function monthTranslateFilter($date)
    {
        $tmpDate = explode(',', $date);

        $month['January'] = "Січень";
        $month['February'] = "Лютий";
        $month['March'] = "Березень";
        $month['April'] = "Квітень";
        $month['May'] = "Травень";
        $month['June'] = "Червень";
        $month['July'] = "Липень";
        $month['August'] = "Серпень";
        $month['September'] = "Вересень";
        $month['October'] = "Жовтень";
        $month['November'] = "Листопад";
        $month['December'] = "Грудень";

        return $month[$tmpDate[0]]. ', ' . $tmpDate[1];
    }

    public function S3PathFilter($str)
    {
        return $this->baseUrl . $str;
    }

    public function transformIdToUsernames($data)
    {
        $iDs = explode(',', $data);

        $result = '';
        if (in_array('-1', $iDs)) {
            $result = "Розіслати всім";
        } else {
            $users = $this->em->getRepository('CTOAppBundle:CtoClient')->findBy(['id' => $iDs]);

            /** @var CtoClient $user */
            foreach ($users as $user) {
                $result .= "<div class='text-left'>" . $user->getFullName() . "</div>";
            }
        }

        return $result;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'app_extension';
    }
}
