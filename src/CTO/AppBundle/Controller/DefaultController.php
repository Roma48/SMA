<?php

namespace CTO\AppBundle\Controller;

use CTO\AppBundle\Entity\AdminUser;
use CTO\AppBundle\Entity\CtoUser;
use CTO\AppBundle\Form\LoginType;
use DateTime;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="login_home")
     * @Template()
     */
    public function indexAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {

            $user = $this->getUser();
            if ($user instanceof AdminUser) {

                return $this->redirectToRoute('adminUser_home');
            } elseif ($user instanceof CtoUser) {
                /** @var CtoUser $user */
                if ($user->isBlocked()) {

                    $this->addFlash('success', 'Ваш акаунт заблоковано.');
                    return $this->redirectToRoute('logout');
                }

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $user->setLastLogin(new DateTime('now'));
                $em->flush();
                return $this->redirectToRoute('cto_jobs_home');
            }
        }

        return [];
    }

    /**
     * @Route("/cto/", name="after_login_switch_route")
     */
    public function defaultAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {

            return $this->redirectToRoute("cto_jobs_home");
        }

        return $this->redirect('/logout');
    }

    /**
     * @Route("/login", name="login")
     * @Template()
     * @param Request $request
     * @return array
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();
        $errorMessage = '';

        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                Security::AUTHENTICATION_ERROR
            );
        } elseif (null !== $session && $session->has(Security::AUTHENTICATION_ERROR)) {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $errorMessage = ($error->getMessage() == "Bad credentials.") ? "Неправильна ел. адреса або пароль. Спробуйте ще раз" : "Bad credentials. 2";
            $session->remove(Security::AUTHENTICATION_ERROR);
        } else {
            $errorMessage = '';
        }

        $form = $this->createForm(new LoginType(),
            ['email' => $session->get(Security::LAST_USERNAME)],
            ['action' => $this->generateUrl('login_check'), 'attr' => ['novalidate' => 'novalidate']]);

        return array(
            'form' => $form->createView(),
            'error' => $errorMessage,
        );
    }
}
