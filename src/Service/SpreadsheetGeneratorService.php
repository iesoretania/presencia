<?php

namespace App\Service;

use App\Entity\Presence\Record;
use App\Entity\Worker;
use App\Repository\EventRepository;
use App\Repository\Presence\RecordRepository;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Yectep\PhpSpreadsheetBundle\Factory;

class SpreadsheetGeneratorService
{
    private $eventRepository;
    private $spreadSheetFactory;
    private $recordRepository;

    public function __construct(
        EventRepository $eventRepository,
        Factory $spreadSheetFactory,
        RecordRepository $recordRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->spreadSheetFactory = $spreadSheetFactory;
        $this->recordRepository = $recordRepository;
    }

    /**
     * @return Response
     * @throws Exception
     */
    public function getCurrentStateResponse(): Response
    {
        $data = $this->eventRepository->listWorkersWithLastEventByData(['in', 'out']);
        $timeString = (new \DateTime())->format('Y-m-d H_i');

        $spreadsheet = $this->spreadSheetFactory->createSpreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle($timeString);

        $num = 1;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $num, $item[0]->getLastName());
            $sheet->setCellValue('B' . $num, $item[0]->getFirstName());

            if ($item[1]) {
                $sheet->setCellValue('C' . $num, $item[1]->getData());
            }

            $num++;
        }

        $response = $this->spreadSheetFactory->createStreamedResponse($spreadsheet, 'Xlsx');

        $response->headers->set(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $fileName = $timeString . '.xlsx';

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return Response
     * @throws Exception
     */
    public function getRecordResponseByDateRange(\DateTime $startDate, \DateTime $endDate): Response
    {
        $spreadsheet = $this->spreadSheetFactory->createSpreadsheet();

        $date = clone $startDate;
        $date->setTime(0, 0, 0);

        $lastDate = clone $endDate;
        $lastDate->setTime(0, 0, 0);

        $fileName = $date->format('Y-m-d');
        $fileName2 = $lastDate->format('Y-m-d');

        if ($fileName !== $fileName2) {
            $fileName .= '_' . $fileName2;
        }

        $dateInterval = new \DateInterval('P1D');

        $first = true;

        while ($date <= $lastDate) {
            $data = $this->recordRepository->findByDate($date);

            $timeString = $date->format('Y-m-d');

            if ($first) {
                $sheet = $spreadsheet->getActiveSheet();
                $first = false;
            } else {
                $sheet = new Worksheet();
                $spreadsheet->addSheet($sheet);
            }
            $sheet->setTitle($timeString);

            $pageSetup = $sheet->getPageSetup();

            $pageSetup
                ->setPaperSize(PageSetup::PAPERSIZE_A4);

            $pageSetup->setFitToWidth(1);
            $pageSetup->setFitToHeight(0);

            $row = 0;
            $col = 3;
            foreach ($data as $item) {
                if ($item instanceof Worker) {
                    $row++;
                    $sheet->setCellValue('A' . $row, $item->getLastName());
                    $sheet->setCellValue('B' . $row, $item->getFirstName());
                    $sheet->setCellValue('C' . $row, Date::PHPToExcel($date));
                    $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode(
                        NumberFormat::FORMAT_DATE_DDMMYYYY
                    );
                    $col = 4;
                }
                if ($item instanceof Record) {
                    $sheet->setCellValueByColumnAndRow($col, $row, $item->getInTimestamp()->format('H:i'));
                    if ($item->getOutTimestamp()) {
                        $sheet->setCellValueByColumnAndRow($col + 1, $row, $item->getOutTimestamp()->format('H:i'));
                    }
                    $col += 2;
                }
            }

            $column = $sheet->getColumnDimension('A');
            if ($column) {
                $column->setAutoSize(true);
            }
            $column = $sheet->getColumnDimension('B');
            if ($column) {
                $column->setAutoSize(true);
            }
            $column = $sheet->getColumnDimension('C');
            if ($column) {
                $column->setAutoSize(true);
            }

            $date->add($dateInterval);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $response = $this->spreadSheetFactory->createStreamedResponse($spreadsheet, 'Xlsx');

        $response->headers->set(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $fileName .= '.xlsx';

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @param \DateTime $date
     * @return Response
     * @throws Exception
     */
    public function getRecordResponseByDate(\DateTime $date): Response
    {
        return $this->getRecordResponseByDateRange($date, $date);
    }
}
