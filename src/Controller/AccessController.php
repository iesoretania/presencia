<?php

namespace App\Controller;

use App\Form\ManualCodeType;
use App\Form\Model\ManualCode;
use App\Service\ProcessManualCodeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AccessController extends AbstractController
{
    /**
     * @Route("/acceso", name="access_code")
     */
    public function accessAction(Request $request, ProcessManualCodeService $processManualCodeService)
    {
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
}
