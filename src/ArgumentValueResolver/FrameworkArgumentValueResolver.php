<?php

declare(strict_types=1);

namespace App\ArgumentValueResolver;

use App\Entity\Framework;
use App\Repository\FrameworkRepository;
use Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use Traversable;

class FrameworkArgumentValueResolver implements ArgumentValueResolverInterface
{
    private FrameworkRepository $repository;
    private TranslatorInterface $translator;

    public function __construct(FrameworkRepository $repository, TranslatorInterface $translator)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): Traversable
    {
        $guid = $request->get('framework_id', '');
        if (!Uuid::isValid($guid)) {
            throw new BadRequestException($this->translator->trans('exception.framework.400.wrong_guid', [], 'errors'));
        }

        try {
            /** @var Framework|null $framework */
            $framework = $this->repository->findOneByGuid($guid);
            if (!$framework) {
                throw new NotFoundHttpException($this->translator->trans('exception.framework.404', [], 'errors'));
            }

            $request->attributes->set($argument->getName(), $framework);

            yield $framework;
        } catch (Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return Framework::class === $argument->getType() && $request->get('framework_id');
    }
}
