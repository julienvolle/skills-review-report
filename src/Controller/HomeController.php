<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Framework;
use App\Entity\Interview;
use App\Repository\FrameworkRepository;
use App\Repository\InterviewRepository;
use App\Security\Voter\AbstractVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route(path="/", name="home", methods={"GET"})
     */
    public function __invoke(
        FrameworkRepository $frameworkRepository,
        InterviewRepository $interviewRepository
    ): Response {
        $reports = $interviewRepository->findAll(AbstractVoter::ACCESS);
        $frameworks = $frameworkRepository->findAll(AbstractVoter::UPDATE);
        $interviews = \array_filter($reports, function (Interview $interview) {
            return $this->isGranted(AbstractVoter::UPDATE, $interview);
        });

        return $this->render('page/home.html.twig', [
            'frameworks' => $frameworks,
            'interviews' => $interviews,
            'reports'    => $reports,
            'isGranted'  => [
                'frameworks' => [
                    AbstractVoter::ACCESS => $this->isGranted(AbstractVoter::ACCESS, Framework::class),
                    AbstractVoter::CREATE => $this->isGranted(AbstractVoter::CREATE, Framework::class),
                    AbstractVoter::UPDATE => $this->isGranted(AbstractVoter::UPDATE, Framework::class),
                    AbstractVoter::IMPORT => $this->isGranted(AbstractVoter::IMPORT, Framework::class),
                ],
                'interviews' => [
                    AbstractVoter::ACCESS => $this->isGranted(AbstractVoter::ACCESS, Interview::class),
                    AbstractVoter::CREATE => $this->isGranted(AbstractVoter::CREATE, Interview::class),
                    AbstractVoter::UPDATE => $this->isGranted(AbstractVoter::UPDATE, Interview::class),
                    AbstractVoter::IMPORT => $this->isGranted(AbstractVoter::IMPORT, Interview::class),
                ],
            ],
        ]);
    }
}
