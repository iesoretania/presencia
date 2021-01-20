<?php

namespace App\Controller;

use App\Form\DateRangeType;
use App\Form\Model\DateRange;
use App\Service\SpreadsheetGeneratorService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_REPORTER")
 * @Route("/informe")
 */
class ReportController extends AbstractController
{
    /**
     * @Route("/rango", name="report_record_date_range_form")
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

        return $this->render('report/record_date_range_form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
