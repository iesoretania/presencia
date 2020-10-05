<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\TagRepository;
use App\Service\SpreadsheetGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CurrentStateController extends AbstractController
{
    /**
     * @Route("/estado/{tags}", name="current_state")
     */
    public function accessAction(EventRepository $eventRepository, TagRepository $tagRepository, $tags = null): Response
    {
        $tagsCollection = [];
        if (null === $tags) {
            $data = $eventRepository->listWorkersWithLastEventByData(['in', 'out']);
        } else {
            $tagIdsCollection = explode(',', $tags);
            if (is_array($tagIdsCollection)) {
                $tagsCollection = $tagRepository->findByIds($tagIdsCollection);
                $data = $eventRepository->listWorkersWithLastEventByDataAndTags(['in', 'out'], $tagsCollection);
            } else {
                $data = [];
            }
        }

        return $this->render('current_state/summary.html.twig', [
            'data' => $data,
            'active_tags' => $tagsCollection,
            'all_tags' => $tagRepository->findAllSorted()
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
