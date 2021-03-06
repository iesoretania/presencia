<?php

namespace App\Controller;

use App\Entity\Presence\AccessCode;
use App\Entity\Presence\Record;
use App\Entity\Worker;
use App\Form\AccessCodeEditType;
use App\Form\AccessCodeNewType;
use App\Form\ChangeRecordType;
use App\Form\ImportType;
use App\Form\Model\FileImport;
use App\Form\WorkerEditType;
use App\Repository\Presence\AccessCodeRepository;
use App\Repository\Presence\RecordRepository;
use App\Repository\TagRepository;
use App\Repository\WorkerRepository;
use App\Service\ImportService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("ROLE_MANAGER")
 * @Route("/personal")
 */
class WorkerController extends AbstractController
{
    /**
     * @Route("/", name="worker")
     */
    public function workerListAction(RecordRepository $recordRepository, TagRepository $tagRepository): Response
    {
        return $this->workerListDateAction($recordRepository, $tagRepository, 'now');
    }

    /**
     * @Route("/fecha/{id}/{date}", name="worker_list_date_detail",
     *     requirements={"id":"\d+", "date":"\d{4}-\d{1,2}-\d{1,2}"})
     */
    public function workerListDateDetailAction(
        RecordRepository $recordRepository,
        Worker $worker,
        string $date
    ): Response
    {
        try {
            $queryDate = new \DateTime($date);
        } catch (\Exception $e) {
            throw $this->createNotFoundException();
        }

        $records = $recordRepository->findByDateAndWorker($queryDate, $worker);

        return $this->render('worker/list_date.html.twig', [
            'records' => $records,
            'worker' => $worker,
            'date' => $queryDate
        ]);
    }

    /**
     * @Route("/fecha/{date}/{tags}", name="worker_list_date", requirements={"date":"\d{4}-\d{1,2}-\d{1,2}"})
     */
    public function workerListDateAction(
        RecordRepository $recordRepository,
        TagRepository $tagRepository,
        $date = null,
        $tags = null
    ): Response
    {
        try {
            $queryDate = new \DateTime($date);
        } catch (\Exception $e) {
            throw $this->createNotFoundException();
        }

        $tagsCollection = [];
        if (null === $tags) {
            $data = $recordRepository->findDataByDate($queryDate);
        } else {
            $tagIdsCollection = explode(',', $tags);
            if (is_array($tagIdsCollection)) {
                $tagsCollection = $tagRepository->findByIds($tagIdsCollection);
                $data = $recordRepository->findDataByDateAndTags($queryDate, $tagsCollection);
            } else {
                $data = [];
            }
        }

        return $this->render('worker/list.html.twig', [
            'data' => $data,
            'date' => $queryDate,
            'all_tags' => $tagRepository->findAllSorted(),
            'active_tags' => $tagsCollection
        ]);
    }


    /**
     * @Route("/nuevo", name="worker_new")
     */
    public function workerNewAction(
        Request $request,
        WorkerRepository $workerRepository,
        AccessCodeRepository $accessCodeRepository,
        TranslatorInterface $translator
    ): Response
    {
        $worker = new Worker();

        return $this->workerFormAction(
            $request,
            $workerRepository,
            $accessCodeRepository,
            $translator,
            $worker
        );
    }

    /**
     * @Route("/{id}", name="worker_form", requirements={"id":"\d+"})
     */
    public function workerFormAction(
        Request $request,
        WorkerRepository $workerRepository,
        AccessCodeRepository $accessCodeRepository,
        TranslatorInterface $translator,
        Worker $worker
    ): Response
    {
        $form = $this->createForm(WorkerEditType::class, $worker);
        $form->handleRequest($request);

        if ($worker->getId()) {
            $next = $workerRepository->findNextOrNull($worker);
        } else {
            $next = null;
        }

        $newAccessCode = new AccessCode();
        $newAccessCode
            ->setWorker($worker);

        $newAccessCodeForm = $this->createForm(AccessCodeNewType::class, $newAccessCode);
        $newAccessCodeForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $workerRepository->save($worker);
                $this->addFlash('success', $translator->trans('message.saved', [], 'worker'));
                return $this->redirectToRoute('worker');
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.save_error', [], 'worker'));
            }
        }

        if ($newAccessCodeForm->isSubmitted() && $newAccessCodeForm->isValid()) {
            try {
                $accessCodeRepository->save($newAccessCode);
                $this->addFlash('success', $translator->trans('message.code_saved', [], 'worker'));
                return $this->redirectToRoute('worker_form', ['id' => $worker->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.save_error', [], 'worker'));
            }
        }

        if ($worker->getId()) {
            $accessCodes = $accessCodeRepository->findByWorker($worker);
        } else {
            $accessCodes = [];
        }

        return $this->render('worker/form.html.twig', [
            'form' => $form->createView(),
            'form_code' => $newAccessCodeForm->createView(),
            'worker' => $worker,
            'next' => $next,
            'access_codes' => $accessCodes
        ]);
    }

    /**
     * @Route("/eliminar/{id}", name="worker_delete", requirements={"id":"\d+"})
     */
    public function workerDeleteAction(
        Request $request,
        WorkerRepository $workerRepository,
        TranslatorInterface $translator,
        Worker $worker
    ): Response
    {
        if ($request->get('confirm', '') === 'ok') {
            try {
                $workerRepository->delete($worker);
                $this->addFlash('success', $translator->trans('message.deleted', [], 'worker'));
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.delete_error', [], 'worker'));
            }
            return $this->redirectToRoute(
                'worker'
            );
        }

        return $this->render('worker/delete.html.twig', [
            'worker' => $worker
        ]);
    }

    /**
     * @Route("/importar", name="worker_import")
     */
    public function workerImportAction(
        Request $request,
        TranslatorInterface $translator,
        ImportService $importService
    ): Response
    {
        $csvFile = new FileImport();

        $form = $this->createForm(ImportType::class, $csvFile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $importService->importFromFile($csvFile->file->getPathname());

                return $this->redirectToRoute('worker');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $translator->trans('message.bad_format', [], 'worker'));
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.save_error', [], 'worker'));
            }
        }

        return $this->render('worker/import_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/personal/codigo/{id}", name="worker_access_code_form", requirements={"id":"\d+"})
     */
    public function workerAccessCodeFormAction(
        Request $request,
        AccessCodeRepository $accessCodeRepository,
        TranslatorInterface $translator,
        AccessCode $accessCode
    ): Response
    {
        $form = $this->createForm(AccessCodeEditType::class, $accessCode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $accessCodeRepository->save($accessCode);
                $this->addFlash('success', $translator->trans('message.saved', [], 'worker'));
                return $this->redirectToRoute('worker_form', ['id' => $accessCode->getWorker()->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.save_error', [], 'worker'));
            }
        }

        return $this->render('worker/access_code_form.html.twig', [
            'form' => $form->createView(),
            'access_code' => $accessCode
        ]);
    }

    /**
     * @Route("/codigo/eliminar/{id}", name="worker_access_code_delete", requirements={"id":"\d+"})
     */
    public function workerAccessCodeDeleteAction(
        Request $request,
        AccessCodeRepository $accessCodeRepository,
        TranslatorInterface $translator,
        AccessCode $accessCode
    ): Response
    {
        if ($request->get('confirm', '') === 'ok') {
            try {
                $accessCodeRepository->delete($accessCode);
                $this->addFlash('success', $translator->trans('message.access_code_deleted', [], 'worker'));
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.access_code_delete_error', [], 'worker'));
            }
            return $this->redirectToRoute(
                'worker_form',
                ['id' => $accessCode->getWorker()->getId()]
            );
        }

        return $this->render('worker/access_code_delete.html.twig', [
            'access_code' => $accessCode
        ]);
    }

    /**
     * @Route("/registro/nuevo/{id}/{y}-{m}-{d}", name="worker_record_new",
     *     requirements={"id":"\d+","y":"\d{4}","m":"\d{1,2}","d":"\d{1,2}"})
     */
    public function workerRecordNewAction(
        Request $request,
        RecordRepository $recordRepository,
        TranslatorInterface $translator,
        Worker $worker,
        int $y,
        int $m,
        int $d
    ): Response
    {
        $realDate = new \DateTime();
        $realDate->setDate($y, $m, $d);
        $record = $recordRepository->createNewRecord($worker, $realDate, $this->getUser()->getUsername());
        return $this->workerRecordFormAction($request, $recordRepository, $translator, $record);
    }

    /**
     * @Route("/registro/{id}", name="worker_record_form", requirements={"id":"\d+"})
     */
    public function workerRecordFormAction(
        Request $request,
        RecordRepository $recordRepository,
        TranslatorInterface $translator,
        Record $record
    ): Response
    {
        $form = $this->createForm(ChangeRecordType::class, $record);

        $originalDate = clone $record->getInTimestamp();
        $y = (int) $originalDate->format('Y');
        $m = (int) $originalDate->format('m');
        $d = (int) $originalDate->format('d');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($request->get('save') === '') {
                    $record->getInTimestamp()->setDate($y, $m, $d);
                    if ($record->getOutTimestamp()) {
                        $record->getOutTimestamp()->setDate($y, $m, $d);
                    }

                    $record->setSource($this->getUser()->getUsername());
                    $recordRepository->save($record);

                    $this->addFlash('success', $translator->trans('message.saved', [], 'worker'));
                } else {
                    // eliminar registro
                    $recordRepository->delete($record);
                    $this->addFlash('success', $translator->trans('message.record_deleted', [], 'worker'));
                }
                return $this->redirectToRoute(
                    'worker_list_date_detail',
                    [
                        'id' => $record->getWorker()->getId(),
                        'date' => $originalDate
                            ->format($translator->trans('format.date_parameter', [], 'general'))
                    ]);
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.save_error', [], 'worker'));
            }
        }
        return $this->render('worker/record_form.html.twig', [
            'record' => $record,
            'form' => $form->createView()
        ]);
    }
}
