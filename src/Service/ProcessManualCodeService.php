<?php

namespace App\Service;

use App\Repository\EventRepository;
use App\Repository\Presence\AccessCodeRepository;
use App\Repository\Presence\RecordRepository;

class ProcessManualCodeService
{
    private $accessCodeRepository;
    private $eventRepository;
    private $recordRepository;

    private $minWaitTime;

    /**
     * ProcessManualCode constructor.
     */
    public function __construct(
        AccessCodeRepository $accessCodeRepository,
        EventRepository $eventRepository,
        RecordRepository $recordRepository,
        int $minWaitTime
    ) {
        $this->accessCodeRepository = $accessCodeRepository;
        $this->eventRepository = $eventRepository;
        $this->recordRepository = $recordRepository;
        $this->minWaitTime = $minWaitTime;
    }

    public function processCode(string $code, \DateTime $timestamp, string $reader = null): array
    {
        $accessCode = $this->accessCodeRepository->findByCode($code);

        if (null === $accessCode) {
            return [
                'result' => 'not_found'
            ];
        }

        $worker = $accessCode->getWorker();

        if (false === $worker->isEnabled()) {
            return [
                'result' => 'not_found'
            ];
        }
        $lastEvent = $this->eventRepository->findLastByWorkerAndData(
            $worker,
            ['in', 'out']
        );

        $record = null;

        if (null === $lastEvent) {
            // primera entrada del trabajador
            $eventDatum = 'in';
            $record = $this->recordRepository->createNewRecord($worker, $timestamp, 'manual', null, $reader);
        } else {
            // si ha transcurrido menos de 5 minutos desde el último evento, ignorar
            if (($timestamp->getTimestamp() - $lastEvent->getTimestamp()->getTimestamp()) < $this->minWaitTime) {
                return [
                    'result' => 'ignore',
                    'last_event' => $lastEvent->getData()
                ];
            }

            if ($lastEvent->getData() === 'in') {
                // último evento: entrada. Comprobar si es del mismo día:
                // - Si lo es, actualizar registro
                // - Si no lo es, crear registro nuevo
                $firstDate = $timestamp->format('Y-m-d');
                $secondDate = $lastEvent->getTimestamp()->format('Y-m-d');

                if ($firstDate === $secondDate) {
                    $eventDatum = 'out';
                    $record = $this->recordRepository->getRecordByWorkerAndInTimestamp($worker, $lastEvent->getTimestamp());
                    if (null === $record) {
                        $record = $this->recordRepository->createNewRecord($worker, $timestamp, 'manual', null, $code, $reader);
                    }
                    $record->setOutTimestamp($timestamp);
                } else {
                    $eventDatum = 'in';
                    $record = $this->recordRepository->createNewRecord($worker, $timestamp, 'manual', null, $code, $reader);
                }
            } else {
                // Último evento: salida. Registrar nuevo
                $eventDatum = 'in';
                $record = $this->recordRepository->createNewRecord($worker, $timestamp, 'manual', null, $code, $reader);
            }
        }

        $event = $this->eventRepository->createNewEvent($worker, $timestamp, 'manual', $eventDatum);
        $this->eventRepository->save($event);
        if ($record) {
            $this->recordRepository->save($record);
        }

        return [
            'result' => $eventDatum,
            'worker' => $worker
        ];
    }
}
