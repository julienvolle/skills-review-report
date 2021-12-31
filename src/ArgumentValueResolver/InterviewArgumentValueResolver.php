<?php

declare(strict_types=1);

namespace App\ArgumentValueResolver;

use App\Entity\Interview;
use App\Repository\InterviewRepository;
use Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use Traversable;

class InterviewArgumentValueResolver implements ArgumentValueResolverInterface
{
    private InterviewRepository $repository;
    private TranslatorInterface $translator;

    public function __construct(InterviewRepository $repository, TranslatorInterface $translator)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): Traversable
    {
        $guid = $request->get('interview_id', '');
        if (!Uuid::isValid($guid)) {
            throw new BadRequestException($this->translator->trans('exception.interview.400.wrong_guid', [], 'errors'));
        }

        try {
            /** @var Interview|null $interview */
            $interview = $this->repository->findOneByGuid($guid);
            if (!$interview) {
                throw new NotFoundHttpException($this->translator->trans('exception.interview.404', [], 'errors'));
            }

            $request->attributes->set($argument->getName(), $interview);

            yield $interview;
        } catch (Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return Interview::class === $argument->getType() && $request->get('interview_id');
    }
}
