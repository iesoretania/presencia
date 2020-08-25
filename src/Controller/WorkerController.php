<?php

namespace App\Controller;

use App\Repository\EventRepository;
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
}
