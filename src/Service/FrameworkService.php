<?php

declare(strict_types=1);

namespace App\Service;

use App\Constant\SecurityConstant;
use App\Constant\SerializerConstant;
use App\Entity\Framework;
use App\Entity\UserFramework;
use App\Exception\Framework\FrameworkExportException;
use App\Exception\Framework\FrameworkImportException;
use App\Exception\Framework\FrameworkRemoveException;
use App\Exception\Framework\FrameworkReplaceException;
use App\Exception\Framework\FrameworkSaveException;
use App\Exception\Framework\FrameworkSearchException;
use App\Model\Export\FrameworkExport;
use App\Repository\FrameworkRepository;
use App\Request\FlashBagAwareInterface;
use App\Request\FlashBagAwareTrait;
use App\Security\SecurityAwareInterface;
use App\Security\SecurityAwareTrait;
use App\Security\Voter\AbstractVoter;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FrameworkService implements FlashBagAwareInterface, SecurityAwareInterface
{
    use FlashBagAwareTrait;
    use SecurityAwareTrait;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var SerializerInterface */
    private $serializer;

    /** @var SemverService */
    private $semverService;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface    $serializer
     * @param SemverService          $semverService
     * @param TranslatorInterface    $translator
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        SemverService $semverService,
        TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->semverService = $semverService;
        $this->translator = $translator;
    }

    /**
     * Export framework
     *
     * @param Framework $framework
     *
     * @throws FrameworkExportException
     *
     * @return string
     */
    public function export(Framework $framework): string
    {
        try {
            $this->denyAccessUnlessGranted(AbstractVoter::EXPORT, $framework);

            $export = (new FrameworkExport())
                ->setAppVersion($this->semverService->getVersion())
                ->setExportedAt((new DateTime('NOW')))
                ->setFramework($framework)
            ;

            return $this->serializer->serialize($export, SerializerConstant::FORMAT_EXPORT, [
                'groups' => [
                    SerializerConstant::GROUP_EXPORT,
                    SerializerConstant::GROUP_EXPORT_FRAMEWORK,
                ],
            ]);
        } catch (Exception $e) {
            throw new FrameworkExportException($e->getMessage(), [], $e);
        }
    }

    /**
     * Import framework
     *
     * @param Framework $framework Framework to import
     * @param bool      $force     To replace current data with imported data
     *
     * @throws FrameworkImportException
     *
     * @return Framework
     */
    public function import(Framework $framework, bool $force = false): Framework
    {
        try {
            $this->denyAccessUnlessGranted(AbstractVoter::IMPORT, Framework::class);

            if (!$currentFramework = $this->search($framework->getGuid())) {
                $this->save($framework);
                $this->addFlash('success', $this->translator->trans('flash.framework.imported', [], 'alertes'));

                return $framework;
            }

            $this->denyAccessUnlessGranted(AbstractVoter::IMPORT, $currentFramework);
        } catch (Exception $e) {
            throw new FrameworkImportException($e->getMessage(), [], $e);
        }

        if (!$this->equals($currentFramework, $framework)) {
            if ($force) {
                try {
                    $this->replace($currentFramework, $framework);
                    $this->addFlash('success', $this->translator->trans('flash.framework.updated', [], 'alertes'));

                    return $framework;
                } catch (Exception $e) {
                    throw new FrameworkImportException($e->getMessage(), [], $e);
                }
            }
            throw new FrameworkImportException(
                $this->translator->trans('exception.framework.import.already_exist', [], 'errors')
            );
        }

        $this->addFlash('info', $this->translator->trans('flash.framework.up_to_date', [], 'alertes'));

        return $currentFramework;
    }

    /**
     * Deserialize upload file to framework entity
     *
     * @param UploadedFile $file
     *
     * @throws FrameworkImportException
     *
     * @return Framework
     */
    public function handleUploadedFile(UploadedFile $file): Framework
    {
        $fileExtension = $file->getClientOriginalExtension();
        if (SerializerConstant::FORMAT_EXPORT !== strtolower($fileExtension)) {
            throw new FrameworkImportException(
                $this->translator->trans('exception.framework.import.upload_file.extension', [
                    '%extension%' => $fileExtension,
                ], 'errors')
            );
        }

        $fileMimeType = $file->getMimeType();
        $mimeTypes = (new MimeTypes())->getMimeTypes(SerializerConstant::FORMAT_EXPORT);
        if (!in_array($fileMimeType, array_merge($mimeTypes, ['text/plain']), true)) {
            throw new FrameworkImportException(
                $this->translator->trans('exception.framework.import.upload_file.type', [
                    '%type%' => $fileMimeType,
                ], 'errors')
            );
        }

        $export = $this->serializer->deserialize(
            $file->getContent(),
            FrameworkExport::class,
            SerializerConstant::FORMAT_EXPORT
        );
        if (!$export instanceof FrameworkExport || !$export->getFramework()) {
            throw new FrameworkImportException(
                $this->translator->trans('exception.framework.import.upload_file.invalid', [], 'errors')
            );
        }

        return $export->getFramework();
    }

    /**
     * Search a framework by GUID
     *
     * @param string $guid
     *
     * @throws FrameworkSearchException
     *
     * @return Framework|null
     */
    public function search(string $guid): ?Framework
    {
        try {
            /** @var FrameworkRepository $frameworkRepository */
            $frameworkRepository = $this->entityManager->getRepository(Framework::class);
            $framework = $frameworkRepository->findOneByGuid($guid);
        } catch (Exception $e) {
            throw new FrameworkSearchException($e->getMessage(), [], $e);
        }

        return $framework;
    }

    /**
     * Replace a framework by another framework in the database
     *
     * @param Framework $frameworkToDelete
     * @param Framework $frameworkToCreate
     *
     * @throws FrameworkReplaceException
     *
     * @return Framework Framework replaced in the database
     */
    public function replace(Framework $frameworkToDelete, Framework $frameworkToCreate): Framework
    {
        try {
            $this->remove($frameworkToDelete);
            $this->save($frameworkToCreate);
        } catch (Exception $e) {
            throw new FrameworkReplaceException($e->getMessage(), [], $e);
        }

        return $frameworkToCreate;
    }

    /**
     * Persist a framework in the database
     *
     * @param Framework $framework
     *
     * @throws FrameworkSaveException
     *
     * @return Framework Framework persisted in the database
     */
    public function save(Framework $framework): Framework
    {
        try {
            if (!$framework->getId()) {
                $this->denyAccessUnlessGranted(AbstractVoter::CREATE, Framework::class);
                $this->setUserRoles($framework, $this->security->getUser(), [SecurityConstant::ROLE_ADMIN]);
            } else {
                $this->denyAccessUnlessGranted(AbstractVoter::UPDATE, $framework);
            }
            $this->entityManager->persist($framework);
            $this->entityManager->flush();
        } catch (Exception $e) {
            throw new FrameworkSaveException($e->getMessage(), [], $e);
        }

        return $framework;
    }

    /**
     * Delete a framework in the database
     *
     * @param Framework $framework
     *
     * @throws FrameworkRemoveException
     *
     * @return Framework Framework deleted in the database
     */
    public function remove(Framework $framework): Framework
    {
        /** @var FrameworkRepository $frameworkRepository */
        $frameworkRepository = $this->entityManager->getRepository(Framework::class);
        if ($frameworkRepository->isUsed($framework)) {
            throw new FrameworkRemoveException(
                $this->translator->trans('exception.framework.delete.is_used', [], 'errors')
            );
        }

        try {
            $this->denyAccessUnlessGranted(AbstractVoter::DELETE, $framework);
            $this->entityManager->remove($framework);
            $this->entityManager->flush();
        } catch (Exception $e) {
            throw new FrameworkRemoveException($e->getMessage(), [], $e);
        }

        return $framework;
    }

    /**
     * Check if two framework are equals
     *
     * @param Framework $a
     * @param Framework $b
     *
     * @return bool
     */
    public function equals(Framework $a, Framework $b): bool
    {
        return $this->hash($a) === $this->hash($b);
    }

    /**
     * Get the hash from a framework
     *
     * @param Framework $framework
     *
     * @return string
     */
    public function hash(Framework $framework): string
    {
        return hash('sha256', $this->serializer->serialize($framework, SerializerConstant::FORMAT_EXPORT, [
            'groups' => [
                SerializerConstant::GROUP_HASH,
            ],
        ]));
    }

    /**
     * Set user roles for a framework
     *
     * @param Framework     $framework
     * @param UserInterface $user
     * @param array         $roles
     *
     * @throws Exception
     *
     * @return Framework
     */
    public function setUserRoles(Framework $framework, UserInterface $user, array $roles = []): Framework
    {
        $matched = $framework->getUserFrameworks()->matching(
            (new Criteria())
                ->where(new Comparison('user', '=', $user))
                ->andWhere(new Comparison('framework', '=', $framework))
        );

        $userFramework = $matched->count() ? $matched->first() : (new UserFramework())->setUser($user);
        $userFramework->addRoles($roles);

        return $framework->addUserFramework($userFramework);
    }
}
