<?php

declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use App\Constant\SecurityConstant;
use App\DataFixtures\AppFixtures;
use App\Entity\Framework;
use App\Entity\Interview;
use App\Entity\User;
use App\Entity\UserInterview;
use App\Tests\CustomTestCase;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @group functional
 */
class InterviewMappingCollectionTest extends CustomTestCase
{
    private ?EntityManagerInterface $entityManager = null;

    public function setUp(): void
    {
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    public function tearDown(): void
    {
        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }

    public function testOrmCascade(): void
    {
        // Interview
        $interview = (new Interview())
            ->setTitle('interview')
            ->setLastname('lastname')
            ->setFirstname('firstname');
        self::assertNull($interview->getId());
        self::assertTrue(Uuid::isValid($interview->getGuid()));
        self::assertInstanceOf(ArrayCollection::class, $interview->getUserInterviews());
        self::assertCount(0, $interview->getUserInterviews());
        self::assertFalse($interview->isSecured());
        self::assertNull($interview->getSecuredAt());
        self::assertInstanceOf(DateTime::class, $interview->getCreatedAt());
        self::assertInstanceOf(DateTime::class, $interview->getUpdatedAt());

        // Framework
        $framework = $this->entityManager->getRepository(Framework::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[Framework::class],
        ]);
        self::assertInstanceOf(Framework::class, $framework);
        $count = $framework->getInterviews()->count();
        self::assertCount($count, $framework->getInterviews());
        $framework->addInterview($interview);
        self::assertCount(($count + 1), $framework->getInterviews());
        $framework->removeInterview($interview);
        self::assertCount($count, $framework->getInterviews());

        // User
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_ADMIN],
        ]);
        $userInterview = (new UserInterview())->setUser($user);
        self::assertNull($userInterview->getId());
        $interview->addUserInterview($userInterview);
        self::assertCount(1, $interview->getUserInterviews());

        // Cascade persist
        $this->entityManager->persist($interview);
        $this->entityManager->flush();
        self::assertIsInt($interview->getId());
        self::assertIsInt($userInterview->getId());
        self::assertTrue($interview->isSecured());
        self::assertInstanceOf(DateTime::class, $interview->getSecuredAt());
        self::assertNotSame('lastname', $interview->getLastName());
        self::assertNotSame('firstname', $interview->getFirstName());

        $find = $this->entityManager->getRepository(Interview::class)->find($interview->getId());
        self::assertInstanceOf(Interview::class, $find);
        $find = $this->entityManager->getRepository(UserInterview::class)->find($userInterview->getId());
        self::assertInstanceOf(UserInterview::class, $find);

        // Cascade delete when remove parent entity
        $this->entityManager->remove($interview);
        $this->entityManager->flush();
        self::assertNull($interview->getId());
        self::assertNull($userInterview->getId());

        // Cascade persist (collections have not been purged)
        $this->entityManager->persist($interview);
        $this->entityManager->flush();
        self::assertIsInt($interview->getId());
        self::assertIsInt($userInterview->getId());

        // Purging collections
        $interview->removeUserInterview($userInterview);
        self::assertCount(0, $interview->getUserInterviews());

        // Cascade delete when persist parent entity with empty collection
        $this->entityManager->persist($interview);
        $this->entityManager->flush();
        self::assertIsInt($interview->getId());
        self::assertNull($userInterview->getId());

        // Cleanup
        $this->entityManager->remove($interview);
        $this->entityManager->flush();
        self::assertNull($interview->getId());
        self::assertCount(0, $interview->getUserInterviews());

        $find = $this->entityManager->getRepository(Interview::class)->findOneBy(['guid' => $interview->getGuid()]);
        self::assertNull($find);
        $find = $this->entityManager->getRepository(UserInterview::class)->findOneBy(['interview' => $interview]);
        self::assertNull($find);
    }
}
