<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Framework;
use App\Entity\Interview;
use App\Form\FrameworkType;
use App\Form\ImportType;
use App\Security\Voter\AbstractVoter;
use App\Service\FrameworkService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FrameworkController extends AbstractController
{
    private FrameworkService $frameworkService;
    private TranslatorInterface $translator;
    private ValidatorInterface $validator;

    public function __construct(
        FrameworkService $frameworkService,
        TranslatorInterface $translator,
        ValidatorInterface $validator
    ) {
        $this->frameworkService = $frameworkService;
        $this->translator = $translator;
        $this->validator = $validator;
    }

    /**
     * @Route(path="/framework/create", name="framework_create", methods={"GET","POST"})
     */
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted(AbstractVoter::CREATE, Framework::class);

        $form = $this->createForm(FrameworkType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Framework $framework */
            $framework = $form->getData();
            $this->validator->validate($framework);
            try {
                $this->frameworkService->save($framework);
                $this->addFlash('success', $this->translator->trans('flash.framework.saved', [], 'alerts'));

                return $this->redirectToRoute('framework_update', [
                    'framework_id' => $framework->getGuid(),
                ]);
            } catch (Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('page/framework.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(path="/framework/update/{framework_id}", name="framework_update", methods={"GET","POST"})
     */
    public function update(Request $request, Framework $framework): Response
    {
        $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $framework);

        $form = $this->createForm(FrameworkType::class, $framework);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Framework $framework */
            $framework = $form->getData();
            $this->validator->validate($framework);
            try {
                $this->frameworkService->save($framework);
                $this->addFlash('success', $this->translator->trans('flash.framework.saved', [], 'alerts'));
            } catch (Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('page/framework.html.twig', [
            'form'      => $form->createView(),
            'framework' => $framework,
            'isGranted' => [
                'frameworks' => [
                    AbstractVoter::DELETE => $this->isGranted(AbstractVoter::DELETE, $framework),
                    AbstractVoter::EXPORT => $this->isGranted(AbstractVoter::EXPORT, $framework),
                ],
                'interviews' => [
                    AbstractVoter::CREATE => $this->isGranted(AbstractVoter::CREATE, Interview::class),
                ],
            ],
        ]);
    }

    /**
     * @Route(path="/framework/delete/{framework_id}", name="framework_delete", methods={"GET"})
     */
    public function delete(Framework $framework): Response
    {
        $this->denyAccessUnlessGranted(AbstractVoter::DELETE, $framework);

        try {
            $this->frameworkService->remove($framework);
            $this->addFlash('success', $this->translator->trans('flash.framework.removed', [], 'alerts'));

            return $this->redirectToRoute('home');
        } catch (Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('framework_update', [
            'framework_id' => $framework->getGuid(),
        ]);
    }

    /**
     * @Route(path="/framework/import", name="framework_import", methods={"GET","POST"})
     */
    public function import(Request $request): Response
    {
        $this->denyAccessUnlessGranted(AbstractVoter::IMPORT, Framework::class);

        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $framework = $this->frameworkService->handleUploadedFile($form->get('file')->getData());
                $this->frameworkService->import($framework, $form->get('delete')->getData());

                return $this->redirectToRoute('framework_update', [
                    'framework_id' => $framework->getGuid(),
                ]);
            } catch (Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('page/import.html.twig', [
            'form'  => $form->createView(),
            'title' => $this->translator->trans('page.import.title_framework'),
        ]);
    }

    /**
     * @Route(path="/framework/export/{framework_id}", name="framework_export", methods={"GET"})
     */
    public function export(Framework $framework): Response
    {
        $this->denyAccessUnlessGranted(AbstractVoter::EXPORT, $framework);

        try {
            $data = $this->frameworkService->export($framework);

            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                \sprintf('framework_%s.json', $framework->getGuid())
            );

            $response = JsonResponse::fromJsonString($data);
            $response->headers->set('Content-Disposition', $disposition);
            $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

            return $response;
        } catch (Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('home');
    }
}
