<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Service\SpreadsheetGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CurrentStateController extends AbstractController
{
    /**
     * @Route("/estado", name="current_state")
     */
    public function accessAction(EventRepository $eventRepository): Response
    {
        $data = $eventRepository->listWorkersWithLastEventByData(['in', 'out']);
        return $this->render('current_state/summary.html.twig', [
            'data' => $data
        ]);
    }

    /**
     * @Route("/estado/descargar", name="current_state_spreadsheet")
     */
    public function downloadAction(SpreadsheetGeneratorService $currentStateSpreadsheetGenerator): Response
    {
        return $currentStateSpreadsheetGenerator->getCurrentStateResponse();
    }
}
