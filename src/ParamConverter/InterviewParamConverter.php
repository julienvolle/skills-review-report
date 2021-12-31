<?php

declare(strict_types=1);

namespace App\ParamConverter;

use App\Entity\Interview;
use App\Repository\InterviewRepository;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class InterviewParamConverter implements ParamConverterInterface
{
    /** @var InterviewRepository */
    protected $repository;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param InterviewRepository $repository
     * @param TranslatorInterface $translator
     */
    public function __construct(InterviewRepository $repository, TranslatorInterface $translator)
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
        $guid = $request->get('interview_id');
        if (!$guid || !Uuid::isValid($guid)) {
            return false;
        }

        if ($configuration->getClass() !== Interview::class) {
            return false;
        }

        try {
            /** @var Interview|null $interview */
            $interview = $this->repository->findOneByGuid($guid);
            if (!$interview) {
                throw new NotFoundHttpException($this->translator->trans('exception.interview.404', [], 'errors'));
            }

            $request->attributes->set($configuration->getName(), $interview);
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
        return $configuration->getName() === 'interview';
    }
}
