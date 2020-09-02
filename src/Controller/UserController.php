<?php

namespace App\Controller;

use App\Entity\Presence\AccessCode;
use App\Entity\User;
use App\Entity\Worker;
use App\Form\AccessCodeEditType;
use App\Form\ImportType;
use App\Form\Model\FileImport;
use App\Form\UserEditType;
use App\Form\UserNewType;
use App\Repository\Presence\AccessCodeRepository;
use App\Repository\UserRepository;
use App\Repository\WorkerRepository;
use App\Service\ImportService;
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
            $form = $this->createForm(UserEditType::class, $user);
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
     * @Route("/personal/eliminar/{id}", name="worker_delete", requirements={"id":"\d+"})
     */
    public function workerDeleteAction(
        Request $request,
        WorkerRepository $workerRepository,
        TranslatorInterface $translator,
        Worker $worker
    ): Response
    {
        if ($request->get('confirm', '') === 'ok') {
            try {
                $workerRepository->delete($worker);
                $this->addFlash('success', $translator->trans('message.deleted', [], 'worker'));
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.delete_error', [], 'worker'));
            }
            return $this->redirectToRoute(
                'worker'
            );
        }

        return $this->render('worker/delete.html.twig', [
            'worker' => $worker
        ]);
    }

    /**
     * @Route("/personal/importar", name="worker_import")
     */
    public function workerImportAction(
        Request $request,
        TranslatorInterface $translator,
        ImportService $importService
    ): Response
    {
        $csvFile = new FileImport();

        $form = $this->createForm(ImportType::class, $csvFile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $importService->importFromFile($csvFile->file->getPathname());

                return $this->redirectToRoute('worker');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $translator->trans('message.bad_format', [], 'worker'));
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.save_error', [], 'worker'));
            }
        }

        return $this->render('worker/import_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/personal/codigo/{id}", name="worker_access_code_form", requirements={"id":"\d+"})
     */
    public function workerAccessCodeFormAction(
        Request $request,
        AccessCodeRepository $accessCodeRepository,
        TranslatorInterface $translator,
        AccessCode $accessCode
    ): Response
    {
        $form = $this->createForm(AccessCodeEditType::class, $accessCode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $accessCodeRepository->save($accessCode);
                $this->addFlash('success', $translator->trans('message.saved', [], 'worker'));
                return $this->redirectToRoute('worker_form', ['id' => $accessCode->getWorker()->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.save_error', [], 'worker'));
            }
        }

        return $this->render('worker/access_code_form.html.twig', [
            'form' => $form->createView(),
            'access_code' => $accessCode
        ]);
    }

    /**
     * @Route("/personal/codigo/eliminar/{id}", name="worker_access_code_delete", requirements={"id":"\d+"})
     */
    public function workerAccessCodeDeleteAction(
        Request $request,
        AccessCodeRepository $accessCodeRepository,
        TranslatorInterface $translator,
        AccessCode $accessCode
    ): Response
    {
        if ($request->get('confirm', '') === 'ok') {
            try {
                $accessCodeRepository->delete($accessCode);
                $this->addFlash('success', $translator->trans('message.access_code_deleted', [], 'worker'));
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.access_code_delete_error', [], 'worker'));
            }
            return $this->redirectToRoute(
                'worker_form',
                ['id' => $accessCode->getWorker()->getId()]
            );
        }

        return $this->render('worker/access_code_delete.html.twig', [
            'access_code' => $accessCode
        ]);
    }
}
