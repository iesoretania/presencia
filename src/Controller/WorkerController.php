<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Service\SpreadsheetGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class WorkerController extends AbstractController
{
    /**
     * @Route("/estado", name="worker_current_state")
     */
    public function accessAction(EventRepository $eventRepository)
    {
        $data = $eventRepository->listWorkersWithLastEventByData(['in', 'out']);
        return $this->render('worker/current_state.html.twig', [
            'data' => $data
        ]);
    }

    /**
     * @Route("/estado/descargar", name="worker_current_state_spreadsheet")
     */
    public function downloadAction(SpreadsheetGeneratorService $currentStateSpreadsheetGenerator)
    {
        return $currentStateSpreadsheetGenerator->getCurrentStateResponse();
    }

    /**
     * @Route("/resumen/descargar", name="worker_record_spreadsheet")
     */
    public function recordSummaryDownloadAction(SpreadsheetGeneratorService $currentStateSpreadsheetGenerator)
    {
        return $currentStateSpreadsheetGenerator->getRecordResponseByDate(new \DateTime());
    }
}
