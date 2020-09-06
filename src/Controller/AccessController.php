<?php

namespace App\Controller;

use App\Form\ManualCodeType;
use App\Form\Model\ManualCode;
use App\Service\ProcessManualCodeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AccessController extends AbstractController
{
    /**
     * @Route("/", name="frontpage")
     * @Route("/acceso", name="access_code")
     */
    public function accessAction(Request $request, ProcessManualCodeService $processManualCodeService, $forceUserForCode)
    {
        if ($forceUserForCode && !$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('login');
        }

        $code = new ManualCode();
        $form = $this->createForm(ManualCodeType::class, $code);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $processManualCodeService->processCode($code->code, new \DateTime());

            $this->addFlash('code', $result);

            return $this->redirectToRoute('access_code');
        }

        return $this->render('access/enter_code.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/code", name="access_remote_code")
     */
    public function remoteAccessAction(Request $request, ProcessManualCodeService $processManualCodeService)
    {
        if ($request->get('q') !== null) {
            $data = $processManualCodeService->processCode($request->get('q'), new \DateTime(), $request->get('o'));

            $result = [
                'result' => $data['result']
            ];

            if (isset($data['worker'])) {
                $result['worker'] = $data['worker']->getFirstName() . ' ' . $data['worker']->getLastName();
            }
            return new JsonResponse($result);
        }

        throw new BadRequestHttpException();
    }
}
