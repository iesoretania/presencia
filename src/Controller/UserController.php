<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserEditType;
use App\Form\UserNewType;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("ROLE_MANAGER")
 * @Route("/usuario")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user")
     */
    public function workerListDateAction(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAllSorted();

        return $this->render('user/list.html.twig', [
            'users' => $users
        ]);
    }


    /**
     * @Route("/nuevo", name="user_new")
     */
    public function userNewAction(
        Request $request,
        UserRepository $userRepository,
        TranslatorInterface $translator,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response
    {
        $user = new User();

        return $this->userFormAction(
            $request,
            $userRepository,
            $translator,
            $passwordEncoder,
            $user
        );
    }

    /**
     * @Route("/{id}", name="user_form", requirements={"id":"\d+"})
     */
    public function userFormAction(
        Request $request,
        UserRepository $userRepository,
        TranslatorInterface $translator,
        UserPasswordEncoderInterface $passwordEncoder,
        User $user
    ): Response
    {
        if ($user->getId()) {
            $form = $this->createForm(UserEditType::class, $user, [
                'locked_profile' => $user === $this->getUser()
            ]);
        } else {
            $form = $this->createForm(UserNewType::class, $user);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if (!$user->getId()) {
                    $user->setPassword($passwordEncoder->encodePassword($user, $form->get('new_password')->getData()));
                }
                $userRepository->save($user);
                $this->addFlash('success', $translator->trans('message.saved', [], 'user'));
                return $this->redirectToRoute('user');
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.save_error', [], 'user'));
            }
        }

        return $this->render('user/form.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/eliminar/{id}", name="user_delete", requirements={"id":"\d+"})
     */
    public function userDeleteAction(
        Request $request,
        UserRepository $userRepository,
        TranslatorInterface $translator,
        User $user
    ): Response
    {
        if ($user === $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($request->get('confirm', '') === 'ok') {
            try {
                $userRepository->delete($user);
                $this->addFlash('success', $translator->trans('message.deleted', [], 'user'));
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.delete_error', [], 'user'));
            }
            return $this->redirectToRoute(
                'user'
            );
        }

        return $this->render('user/delete.html.twig', [
            'user' => $user
        ]);
    }
}
