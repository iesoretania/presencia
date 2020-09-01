<?php

namespace App\Controller;

use App\Entity\Worker;
use App\Form\Model\FileImport;
use App\Form\TeacherImportType;
use App\Form\WorkerEditType;
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
        $data = $recordRepository->listWorkersWithLastRecord();

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
        TranslatorInterface $translator
    ): Response
    {
        $worker = new Worker();

        return $this->workerFormAction($request, $workerRepository, $translator, $worker);
    }

    /**
     * @Route("/personal/{id}", name="worker_form", requirements={"id":"\d+"})
     */
    public function workerFormAction(
        Request $request,
        WorkerRepository $workerRepository,
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
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $workerRepository->save($worker);
                $this->addFlash('success', $translator->trans('message.saved', [], 'worker'));
                if ($next && $request->request->has('next')) {
                    return $this->redirectToRoute('worker_form', ['id' => $next->getId()]);
                }
                return $this->redirectToRoute('worker_list');
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.save_error', [], 'worker'));
            }
        }

        return $this->render('worker/form.html.twig', [
            'form' => $form->createView(),
            'worker' => $worker,
            'next' => $next
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
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.save_error', [], 'worker'));
            }
        }

        return $this->render('worker/teacher_import_form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
