<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/entrar", name="login", methods={"GET"})
     */
    public function loginAction(AuthenticationUtils $authenticationUtils)
    {
        // obtener el error de entrada, si existe alguno
        $error = $authenticationUtils->getLastAuthenticationError();

        // último nombre de usuario introducido
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            array(
                'last_username' => $lastUsername,
                'login_error' => $error,
            )
        );
    }

    /**
     * @Route("/comprobar", name="login_check", methods={"POST", "GET"})
     * @Route("/salir", name="logout", methods={"GET"})
     */
    public function logInOutCheckAction()
    {
        return $this->redirectToRoute('login');
    }
}