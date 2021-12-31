<?php

declare(strict_types=1);

namespace App\Service;

use App\Constant\SecurityConstant;
use App\Constant\SerializerConstant;
use App\Entity\Interview;
use App\Entity\UserInterview;
use App\Exception\Interview\InterviewExportException;
use App\Exception\Interview\InterviewImportException;
use App\Exception\Interview\InterviewRemoveException;
use App\Exception\Interview\InterviewReplaceException;
use App\Exception\Interview\InterviewSaveException;
use App\Exception\Interview\InterviewSearchException;
use App\Model\Export\InterviewExport;
use App\Repository\InterviewRepository;
use App\Request\FlashBagAwareInterface;
use App\Request\FlashBagAwareTrait;
use App\Security\SecurityAwareInterface;
use App\Security\SecurityAwareTrait;
use App\Security\Voter\AbstractVoter;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class InterviewService implements FlashBagAwareInterface, SecurityAwareInterface
{
    use FlashBagAwareTrait;
    use SecurityAwareTrait;

    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private FrameworkService $frameworkService;
    private SemverService $semverService;
    private TranslatorInterface $translator;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        FrameworkService $frameworkService,
        SemverService $semverService,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->frameworkService = $frameworkService;
        $this->semverService = $semverService;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Export interview
     *
     * @throws InterviewExportException
     */
    public function export(Interview $interview): string
    {
        try {
            $this->denyAccessUnlessGranted(AbstractVoter::EXPORT, $interview);

            $export = (new InterviewExport())
                ->setAppVersion($this->semverService->getVersion())
                ->setExportedAt((new DateTime()))
                ->setInterview($interview)
            ;

            return $this->serializer->serialize($export, SerializerConstant::FORMAT_EXPORT, [
                'groups' => [
                    SerializerConstant::GROUP_EXPORT,
                    SerializerConstant::GROUP_EXPORT_FRAMEWORK,
                    SerializerConstant::GROUP_EXPORT_INTERVIEW,
                ],
            ]);
        } catch (Exception $e) {
            throw new InterviewExportException($e->getMessage(), [], $e);
        }
    }

    /**
     * Import interview
     *
     * @param Interview $interview Interview to import
     * @param bool      $force     To replace current data with imported data
     *
     * @throws InterviewImportException
     */
    public function import(Interview $interview, bool $force = false): Interview
    {
        try {
            $this->denyAccessUnlessGranted(AbstractVoter::IMPORT, Interview::class);

            $interview->setFramework($this->frameworkService->import($interview->getFramework(), $force));

            if (!$currentInterview = $this->search($interview->getGuid())) {
                $this->save($interview);
                $this->addFlash('success', $this->translator->trans('flash.interview.imported', [], 'alerts'));

                return $interview;
            }

            $this->denyAccessUnlessGranted(AbstractVoter::IMPORT, $currentInterview);
        } catch (Exception $e) {
            throw new InterviewImportException($e->getMessage(), [], $e);
        }

        if (!$this->equals($currentInterview, $interview)) {
            if ($force) {
                try {
                    $this->replace($currentInterview, $interview);
                    $this->addFlash('success', $this->translator->trans('flash.interview.updated', [], 'alerts'));

                    return $interview;
                } catch (Exception $e) {
                    throw new InterviewImportException($e->getMessage(), [], $e);
                }
            }
            throw new InterviewImportException(
                $this->translator->trans('exception.interview.import.already_exist', [], 'errors')
            );
        }

        $this->addFlash('info', $this->translator->trans('flash.interview.up_to_date', [], 'alerts'));

        return $currentInterview;
    }

    /**
     * Deserialize upload file to interview entity
     *
     * @throws InterviewImportException
     */
    public function handleUploadedFile(UploadedFile $file): Interview
    {
        $fileExtension = $file->getClientOriginalExtension();
        if (SerializerConstant::FORMAT_EXPORT !== strtolower($fileExtension)) {
            throw new InterviewImportException(
                $this->translator->trans('exception.interview.import.upload_file.extension', [
                    '%extension%' => $fileExtension,
                ], 'errors')
            );
        }

        $fileMimeType = $file->getMimeType();
        $mimeTypes = (new MimeTypes())->getMimeTypes(SerializerConstant::FORMAT_EXPORT);
        if (!\in_array($fileMimeType, $mimeTypes, true)) {
            throw new InterviewImportException(
                $this->translator->trans('exception.interview.import.upload_file.type', [
                    '%type%' => $fileMimeType,
                ], 'errors')
            );
        }

        $export = $this->serializer->deserialize(
            $file->getContent(),
            InterviewExport::class,
            SerializerConstant::FORMAT_EXPORT
        );
        if (!$export instanceof InterviewExport || !$export->getInterview()) {
            throw new InterviewImportException(
                $this->translator->trans('exception.interview.import.upload_file.invalid', [], 'errors')
            );
        }

        return $export->getInterview();
    }

    /**
     * Search an interview by GUID
     *
     * @throws InterviewSearchException
     */
    public function search(string $guid): ?Interview
    {
        try {
            /** @var InterviewRepository $interviewRepository */
            $interviewRepository = $this->entityManager->getRepository(Interview::class);
            $interview = $interviewRepository->findOneByGuid($guid);
        } catch (Exception $e) {
            throw new InterviewSearchException($e->getMessage(), [], $e);
        }

        return $interview;
    }

    /**
     * Replace an interview by another interview in the database
     *
     * @throws InterviewReplaceException
     *
     * @return Interview Interview replaced in the database
     */
    public function replace(Interview $interviewToDelete, Interview $interviewToCreate): Interview
    {
        try {
            $this->remove($interviewToDelete);
            $this->save($interviewToCreate);
        } catch (Exception $e) {
            throw new InterviewReplaceException($e->getMessage(), [], $e);
        }

        return $interviewToCreate;
    }

    /**
     * Persist an interview in the database
     *
     * @throws InterviewSaveException
     *
     * @return Interview Interview persisted in the database
     */
    public function save(Interview $interview): Interview
    {
        try {
            if (!$interview->getId()) {
                $this->denyAccessUnlessGranted(AbstractVoter::CREATE, Interview::class);
                $this->setUserRoles($interview, $this->security->getUser(), [SecurityConstant::ROLE_ADMIN]);
            } else {
                $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $interview);
            }
            // dispatch prePersist event to force encryption on interview without change
            $this->eventDispatcher->dispatch(new PrePersistEventArgs($interview, $this->entityManager));
            $this->entityManager->persist($interview);
            $this->entityManager->flush();
        } catch (Exception $e) {
            throw new InterviewSaveException($e->getMessage(), [], $e);
        }

        return $interview;
    }

    /**
     * Delete an interview in the database
     *
     * @throws InterviewRemoveException
     *
     * @return Interview Interview deleted in the database
     */
    public function remove(Interview $interview): Interview
    {
        try {
            $this->denyAccessUnlessGranted(AbstractVoter::DELETE, $interview);
            $this->entityManager->remove($interview);
            $this->entityManager->flush();
        } catch (Exception $e) {
            throw new InterviewRemoveException($e->getMessage(), [], $e);
        }

        return $interview;
    }

    /**
     * Check if two interview are equals
     */
    public function equals(Interview $a, Interview $b): bool
    {
        return $this->hash($a) === $this->hash($b);
    }

    /**
     * Get the hash from an interview
     */
    public function hash(Interview $interview): string
    {
        $context = [
            'groups' => [
                SerializerConstant::GROUP_HASH,
            ],
        ];

        $data = $this->serializer->serialize($interview, SerializerConstant::FORMAT_EXPORT, $context);

        return \hash('sha256', $data);
    }

    /**
     * Set user roles for an interview
     *
     * @throws Exception
     */
    public function setUserRoles(Interview $interview, UserInterface $user, array $roles = []): Interview
    {
        $matched = $interview->getUserInterviews()->matching(
            (new Criteria())
                ->where(new Comparison('user', '=', $user))
                ->andWhere(new Comparison('interview', '=', $interview))
        );

        $userInterview = $matched->count() ? $matched->first() : (new UserInterview())->setUser($user);
        $userInterview->addRoles($roles);

        return $interview->addUserInterview($userInterview);
    }
}
