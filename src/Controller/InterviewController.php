<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Framework;
use App\Entity\Interview;
use App\Form\ImportType;
use App\Form\InterviewType;
use App\Security\Voter\AbstractVoter;
use App\Service\InterviewService;
use App\Service\ReportService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class InterviewController extends AbstractController
{
    private InterviewService $interviewService;
    private ReportService $reportService;
    private TranslatorInterface $translator;
    private ValidatorInterface $validator;

    public function __construct(
        InterviewService $interviewService,
        ReportService $reportService,
        TranslatorInterface $translator,
        ValidatorInterface $validator
    ) {
        $this->interviewService = $interviewService;
        $this->reportService = $reportService;
        $this->translator = $translator;
        $this->validator = $validator;
    }

    /**
     * @Route(path="/interview/create/{framework_id}", name="interview_create", methods={"GET","POST"})
     */
    public function create(Request $request, Framework $framework): Response
    {
        $this->denyAccessUnlessGranted(AbstractVoter::CREATE, Interview::class);

        $form = $this->createForm(InterviewType::class, (new Interview())->setFramework($framework));
        $form->handleRequest($request);

        return $this->handleForm($form);
    }

    /**
     * @Route(path="/interview/update/{interview_id}", name="interview_update", methods={"GET","POST"})
     */
    public function update(Request $request, Interview $interview): Response
    {
        $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $interview);

        $form = $this->createForm(InterviewType::class, $interview);
        $form->handleRequest($request);

        return $this->handleForm($form);
    }

    private function handleForm(FormInterface $form): Response
    {
        /** @var Interview $interview */
        $interview = $form->getNormData();

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Interview $interview */
            $interview = $form->getData();
            $this->validator->validate($interview);
            try {
                $this->interviewService->save($interview);
                $this->addFlash('success', $this->translator->trans('flash.interview.saved', [], 'alerts'));

                return $this->redirectToRoute('interview_update', [
                    'interview_id' => $interview->getGuid(),
                ]);
            } catch (Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('page/interview.html.twig', [
            'form'      => $form->createView(),
            'interview' => $interview,
            'isGranted' => [
                'interviews' => [
                    AbstractVoter::DELETE => $this->isGranted(AbstractVoter::DELETE, $interview),
                    AbstractVoter::EXPORT => $this->isGranted(AbstractVoter::EXPORT, $interview),
                ],
            ],
        ]);
    }

    /**
     * @Route(path="/interview/delete/{interview_id}", name="interview_delete", methods={"GET"})
     */
    public function delete(Interview $interview): Response
    {
        $this->denyAccessUnlessGranted(AbstractVoter::DELETE, $interview);

        try {
            $this->interviewService->remove($interview);
            $this->addFlash('success', $this->translator->trans('flash.interview.removed', [], 'alerts'));

            return $this->redirectToRoute('home');
        } catch (Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('interview_update', [
            'interview_id' => $interview->getGuid(),
        ]);
    }

    /**
     * @Route(path="/interview/report/{interview_id}", name="interview_report", methods={"GET"})
     */
    public function report(Interview $interview): Response
    {
        $this->denyAccessUnlessGranted(AbstractVoter::ACCESS, $interview);

        try {
            return $this->render('page/interview_report.html.twig', $this->reportService->getReportData($interview));
        } catch (Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('home');
    }

    /**
     * @Route(path="/interview/import", name="interview_import", methods={"GET","POST"})
     */
    public function import(Request $request): Response
    {
        $this->denyAccessUnlessGranted(AbstractVoter::IMPORT, Interview::class);

        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $interview = $this->interviewService->handleUploadedFile($form->get('file')->getData());
                $this->interviewService->import($interview, $form->get('delete')->getData());

                return $this->redirectToRoute('interview_update', [
                    'interview_id' => $interview->getGuid(),
                ]);
            } catch (Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('page/import.html.twig', [
            'form'  => $form->createView(),
            'title' => $this->translator->trans('page.import.title_interview'),
        ]);
    }

    /**
     * @Route(path="/interview/export/{interview_id}", name="interview_export", methods={"GET"})
     */
    public function export(Interview $interview): Response
    {
        $this->denyAccessUnlessGranted(AbstractVoter::EXPORT, $interview);

        try {
            $data = $this->interviewService->export($interview);

            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                \sprintf('interview_%s.json', $interview->getGuid())
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
