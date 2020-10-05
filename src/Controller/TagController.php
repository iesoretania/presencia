<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("ROLE_MANAGER")
 * @Route("/etiqueta")
 */
class TagController extends AbstractController
{
    /**
     * @Route("/", name="tag")
     */
    public function workerListDateAction(TagRepository $tagRepository): Response
    {
        $tags = $tagRepository->findAllSorted();

        return $this->render('tag/list.html.twig', [
            'tags' => $tags
        ]);
    }


    /**
     * @Route("/nueva", name="tag_new")
     */
    public function userNewAction(
        Request $request,
        TagRepository $tagRepository,
        TranslatorInterface $translator
    ): Response
    {
        $tag = new Tag();

        return $this->tagFormAction(
            $request,
            $tagRepository,
            $translator,
            $tag
        );
    }

    /**
     * @Route("/{id}", name="tag_form", requirements={"id":"\d+"})
     */
    public function tagFormAction(
        Request $request,
        TagRepository $tagRepository,
        TranslatorInterface $translator,
        Tag $tag
    ): Response
    {
        $form = $this->createForm(TagType::class, $tag);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $tagRepository->save($tag);
                $this->addFlash('success', $translator->trans('message.saved', [], 'tag'));
                return $this->redirectToRoute('tag');
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.save_error', [], 'tag'));
            }
        }

        return $this->render('tag/form.html.twig', [
            'form' => $form->createView(),
            'tag' => $tag
        ]);
    }

    /**
     * @Route("/eliminar/{id}", name="tag_delete", requirements={"id":"\d+"})
     */
    public function userDeleteAction(
        Request $request,
        TagRepository $tagRepository,
        TranslatorInterface $translator,
        Tag $tag
    ): Response
    {
        if ($request->get('confirm', '') === 'ok') {
            try {
                $tagRepository->delete($tag);
                $this->addFlash('success', $translator->trans('message.deleted', [], 'tag'));
            } catch (\Exception $e) {
                $this->addFlash('error', $translator->trans('message.delete_error', [], 'tag'));
            }
            return $this->redirectToRoute(
                'tag'
            );
        }

        return $this->render('tag/delete.html.twig', [
            'tag' => $tag
        ]);
    }
}
