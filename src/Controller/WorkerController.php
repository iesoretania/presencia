<?php

namespace App\Controller;

use App\Entity\Presence\AccessCode;
use App\Entity\Worker;
use App\Form\AccessCodeEditType;
use App\Form\AccessCodeNewType;
use App\Form\Model\FileImport;
use App\Form\TeacherImportType;
use App\Form\WorkerEditType;
use App\Repository\Presence\AccessCodeRepository;
use App\Repository\Presence\RecordRepository;
use App\Repository\WorkerRepository;
use App\Service\TeacherImportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class WorkerController extends AbstractController
{
    /**
     * @Route("/personal", name="worker_list")
     */
    public function workerListAction(RecordRepository $recordRepository): Response
    {
        $data = $recordRepository->findByDate(new \DateTime());

        return $this->render('worker/list.html.twig', [
            'data' => $data
        ]);
    }

    /**
     * @Route("/personal/nuevo", name="worker_new")
     */
    public function workerNewAction(
        Request $request,
        WorkerRepository $workerRepository,
        AccessCodeRepository $accessCodeRepository,
        TranslatorInterface $translator
    ): Response
    {
        $worker = new Worker();

        return $this->workerFormAction(
            $request,
            $workerRepository,
            $accessCodeRepository,
            $translator,
            $worker
        );
    }

    /**
     * @Route("/personal/{id}", name="worker_form", requirements={"id":"\d+"})
     */
    public function workerFormAction(
        Request $request,
        WorkerRepository $workerRepository,
        AccessCodeRepository $accessCodeRepository,
        TranslatorInterface $translator,
        Worker $worker
    ): Response
    {
        $form = $this->createForm(WorkerEditType::class, $worker);
        $form->handleRequest($request);

        if ($worker->getId()) {
            $next = $workerRepository->findNextOrNull($worker);
        } else {
            $next = null;
        }

        $newAccessCode = new AccessCode();
        $newAccessCode
            ->setWorker($worker);

        $newAccessCodeForm = $this->createForm(AccessCodeNewType::class, $newAccessCode);
        $newAccessCodeForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $workerRepository->save($worker);
                $this->addFlash('success', $translator->trans('message.saved', [], 'worker'));
                return $this->redirectToRoute('worker_list');
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.save_error', [], 'worker'));
            }
        }

        if ($newAccessCodeForm->isSubmitted() && $newAccessCodeForm->isValid()) {
            try {
                $accessCodeRepository->save($newAccessCode);
                $this->addFlash('success', $translator->trans('message.code_saved', [], 'worker'));
                return $this->redirectToRoute('worker_form', ['id' => $worker->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.save_error', [], 'worker'));
            }
        }

        if ($worker->getId()) {
            $accessCodes = $accessCodeRepository->findByWorker($worker);
        } else {
            $accessCodes = [];
        }

        return $this->render('worker/form.html.twig', [
            'form' => $form->createView(),
            'form_code' => $newAccessCodeForm->createView(),
            'worker' => $worker,
            'next' => $next,
            'access_codes' => $accessCodes
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
                'worker_list'
            );
        }

        return $this->render('worker/delete.html.twig', [
            'worker' => $worker
        ]);
    }

    /**
     * @Route("/personal/importar", name="worker_teacher_import")
     */
    public function workerTeacherImportAction(
        Request $request,
        TranslatorInterface $translator,
        TeacherImportService $teacherImportService
    ): Response
    {
        $csvFile = new FileImport();

        $form = $this->createForm(TeacherImportType::class, $csvFile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $teacherImportService->importFromFile($csvFile->file->getPathname());

                return $this->redirectToRoute('worker_list');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $translator->trans('message.bad_format', [], 'worker'));
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.save_error', [], 'worker'));
            }
        }

        return $this->render('worker/teacher_import_form.html.twig', [
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
