<?php

namespace App\Controller;

use App\Form\DateRangeType;
use App\Form\Model\DateRange;
use App\Repository\Presence\RecordRepository;
use App\Service\SpreadsheetGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
