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

        // llevar la cuenta de los recién generados
        $generated = [];

        foreach ($records as $offset => $record) {
            $idEA = $record['Usuario IdEA'];
            $workerName = iconv('ISO-8859-1', 'UTF-8', $record['Empleado/a']);

            $worker = $this->workerRepository->findOneByInternalCode($idEA);

            // si no existía previamente a la importación y no ha sido importado
            // en este fichero, añadir el trabajador
            if (null === $worker && !isset($generated[$idEA])) {
                $fullName = explode(', ', $workerName);
                $worker = $this->workerRepository->findOneByFirstAndLastName($fullName[1], $fullName[0]);

                if (null === $worker) {
                    $worker = new Worker();
                    $worker
                        ->setLastName($fullName[0])
                        ->setFirstName($fullName[1]);

                    if ($idEA) {
                        $worker->setInternalCode($record['Usuario IdEA']);
                    }

                    $this->workerRepository->persist($worker);
                }

                // indicar que esta persona ya ha sido generada
                $generated[$idEA] = $worker;
            }
        }

        if ($worker) {
            $this->workerRepository->flush();
        }
    }
}
