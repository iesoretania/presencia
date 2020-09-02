<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\InitialConfigurationType;
use App\Form\Model\InitialConfiguration;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class ConfigController extends AbstractController
{
    /**
     * @Route("/configuracion", name="config")
     */
    public function configAction(
        Request $request,
        UserRepository $userRepository,
        GuardAuthenticatorHandler $guardAuthenticatorHandler,
        LoginFormAuthenticator $loginFormAuthenticator,
        UserPasswordEncoderInterface $userPasswordEncoder
    ): Response
    {
        if ($userRepository->countAll() > 0) {
            return $this->redirectToRoute('frontpage');
        }

        $config = new InitialConfiguration();

        $form = $this->createForm(InitialConfigurationType::class, $config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();
            $user
                ->setUsername($config->username)
                ->setName($config->name)
                ->setProfile(User::ROLE_MANAGER)
                ->setPassword($userPasswordEncoder->encodePassword($user, $config->password));
            $userRepository->save($user);

            return $guardAuthenticatorHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $loginFormAuthenticator,
                'main'
            );
        }

        return $this->render('config/form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
