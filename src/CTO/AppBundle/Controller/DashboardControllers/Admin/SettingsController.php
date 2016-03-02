<?php

namespace CTO\AppBundle\Controller\DashboardControllers\Admin;

use CTO\AppBundle\Entity\CtoUser;
use CTO\AppBundle\Form\AdminCtoType;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SettingsController
 * @package CTO\AppBundle\Controller\DashboardControllers\Admin]
 *
 * @Route("/settings")
 */
class SettingsController extends Controller
{
    /**
     * @Route("/", name="cto_admin_settings_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction()
    {
        $balance = $this->get('cto.sms.sender')->getBalance();

        /** @var CtoUser $user */
        $user = $this->getUser();

        return [
            'user' => $user,
            'balance' => $balance
        ];
    }

    /**
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/edit", name="cto_admin_settings_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request)
    {
        /** @var CtoUser $user */
        $user = $this->getUser();
        $form = $this->createForm(new AdminCtoType(), $user);

        if ($request->getMethod() == Request::METHOD_POST) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();

                if ($user->getPlainPassword()) {
                    $encoder = $this->get('security.password_encoder');
                    $user->setPassword($encoder->encodePassword($user, $user->getPlainPassword()));
                }
                $em->flush();

                $this->addFlash('success', "{$user->getFirstName()} {$user->getLastName()} успішно відредагований.");

                return $this->redirectToRoute('cto_admin_settings_show');
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

}
