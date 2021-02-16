<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\TagRepository;
use App\Service\SpreadsheetGeneratorService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CurrentStateController extends AbstractController
{
    /**
     * @Route("/estado/{tags}", name="current_state")
     */
    public function accessAction(bool $forceUserForCode, EventRepository $eventRepository, TagRepository $tagRepository, array $tags = null): Response
    {
        if ($forceUserForCode && !$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login');
        }
        $tagsCollection = [];
        if (null === $tags) {
            $data = $eventRepository->listWorkersWithLastEventByData(['in', 'out'], true);
        } else {
            $tagIdsCollection = explode(',', $tags);
            if (is_array($tagIdsCollection)) {
                $tagsCollection = $tagRepository->findByIds($tagIdsCollection);
                $data = $eventRepository->listWorkersWithLastEventByDataAndTags(['in', 'out'], $tagsCollection, true);
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
     * @Security("is_granted('ROLE_REPORTER')")
     */
    public function downloadAction(SpreadsheetGeneratorService $currentStateSpreadsheetGenerator): Response
    {
        return $currentStateSpreadsheetGenerator->getCurrentStateResponse();
    }
}
