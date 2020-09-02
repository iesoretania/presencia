<?php

namespace App\Service;

use App\Entity\Worker;
use App\Repository\WorkerRepository;
use League\Csv\Reader;

class ImportService
{
    /**
     * @var WorkerRepository
     */
    private $workerRepository;

    public function __construct(WorkerRepository $workerRepository)
    {
        $this->workerRepository = $workerRepository;
    }

    public function importFromFile(string $file): void
    {
        $reader = Reader::createFromPath($file, 'r');
        $reader->setHeaderOffset(0);
        $header = $reader->getHeader();

        // comprobar que tenemos las dos columnas que necesitamos
        if (in_array('Usuario IdEA', $header) === false ||
            in_array('Empleado/a', $header) === false) {
            throw new \RuntimeException('Missing fields in file');
        }

        $records = $reader->getRecords();
        $worker = null;

        foreach ($records as $offset => $record) {
            $idEA = $record['Usuario IdEA'];
            $workerName = iconv('ISO-8859-1', 'UTF-8', $record['Empleado/a']);

            $worker = $this->workerRepository->findOneByInternalCode($idEA);

            if (null === $worker) {
                $worker = new Worker();
                $fullName = explode(', ', $workerName);
                $worker
                    ->setLastName($fullName[0])
                    ->setFirstName($fullName[1])
                    ->setInternalCode($record['Usuario IdEA']);
                $this->workerRepository->persist($worker);
            }
        }

        if ($worker) {
            $this->workerRepository->flush();
        }
    }
}
