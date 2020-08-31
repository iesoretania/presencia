<?php

namespace App\Controller;

use App\Entity\Worker;
use App\Form\DateRangeType;
use App\Form\Model\DateRange;
use App\Form\WorkerEditType;
use App\Repository\Presence\RecordRepository;
use App\Repository\WorkerRepository;
use App\Service\SpreadsheetGeneratorService;
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
     * @Route("/personal/{id}", name="worker_form")
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
     * @Route("/resumen/descargar", name="worker_record_spreadsheet")
     */
    public function recordSummaryDownloadAction(SpreadsheetGeneratorService $currentStateSpreadsheetGenerator): Response
    {
        return $currentStateSpreadsheetGenerator->getRecordResponseByDate(new \DateTime());
    }

    /**
     * @Route("/resumen/rango", name="worker_record_date_range_form")
     */
    public function recordRangeDownloadAction(
        Request $request,
        SpreadsheetGeneratorService $currentStateSpreadsheetGenerator
    ): Response
    {
        $dateRange = new DateRange();
        $dateRange->firstDate = new \DateTime();
        $dateRange->lastDate = new \DateTime();

        $form = $this->createForm(DateRangeType::class, $dateRange);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $currentStateSpreadsheetGenerator->getRecordResponseByDateRange(
                $dateRange->firstDate,
                $dateRange->lastDate
            );
        }

        return $this->render('worker/record_date_range_form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
