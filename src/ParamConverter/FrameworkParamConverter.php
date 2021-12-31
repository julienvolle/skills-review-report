<?php

declare(strict_types=1);

namespace App\ParamConverter;

use App\Entity\Framework;
use App\Repository\FrameworkRepository;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class FrameworkParamConverter implements ParamConverterInterface
{
    /** @var FrameworkRepository */
    protected $repository;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param FrameworkRepository $repository
     * @param TranslatorInterface $translator
     */
    public function __construct(FrameworkRepository $repository, TranslatorInterface $translator)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    /**
     * @param Request        $request
     * @param ParamConverter $configuration
     *
     * @return bool
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $guid = $request->get('framework_id');
        if (!$guid || !Uuid::isValid($guid)) {
            return false;
        }

        if ($configuration->getClass() !== Framework::class) {
            return false;
        }

        try {
            /** @var Framework|null $framework */
            $framework = $this->repository->findOneByGuid($guid);
            if (!$framework) {
                throw new NotFoundHttpException($this->translator->trans('exception.framework.404', [], 'errors'));
            }

            $request->attributes->set($configuration->getName(), $framework);
        } catch (Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return true;
    }

    /**
     * @param ParamConverter $configuration
     *
     * @return bool
     */
    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getName() === 'framework';
    }
}
