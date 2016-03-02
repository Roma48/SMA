<?php

namespace CTO\AppBundle\Controller\DashboardControllers\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class HomeController
 * @Route("/")
 */
class HomeController extends Controller
{
    /**
     * @Route("/", name="adminUser_home")
     * @Method("GET")
     * @Template()
     */
    public function homeAction()
    {
        return [];
    }
}
