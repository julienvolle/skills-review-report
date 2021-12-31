<?php

declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use App\Constant\SecurityConstant;
use App\DataFixtures\AppFixtures;
use App\Entity\Framework;
use App\Entity\Interview;
use App\Entity\User;
use App\Entity\UserFramework;
use App\Entity\UserInterview;
use App\Tests\CustomTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @group functional
 */
class UserMappingCollectionTest extends CustomTestCase
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
        // User
        $user = (new User())->setPassword('password');
        self::assertNull($user->getId());
        self::assertTrue(Uuid::isValid($user->getGuid()));
        self::assertInstanceOf(ArrayCollection::class, $user->getUserFrameworks());
        self::assertInstanceOf(ArrayCollection::class, $user->getUserInterviews());
        self::assertCount(0, $user->getUserFrameworks());
        self::assertCount(0, $user->getUserInterviews());

        // Username
        self::assertSame($user->getUsername(), '');
        self::assertSame($user->getUserIdentifier(), '');
        $user->setEmail(uniqid() . '@example.com');
        self::assertSame($user->getUsername(), $user->getEmail());
        self::assertSame($user->getUserIdentifier(), $user->getEmail());

        // Role
        self::assertSame($user->getRoles(), [SecurityConstant::ROLE_USER]);

        // Useless salt
        self::assertNull($user->getSalt());

        // UserFramework
        $framework = $this->entityManager->getRepository(Framework::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[Framework::class],
        ]);
        $userFramework = (new UserFramework())->setFramework($framework);
        self::assertNull($userFramework->getId());
        $user->addUserFramework($userFramework);
        self::assertCount(1, $user->getUserFrameworks());

        // UserInterview
        $interview = $this->entityManager->getRepository(Interview::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[Interview::class],
        ]);
        $userInterview = (new UserInterview())->setInterview($interview);
        self::assertNull($userInterview->getId());
        $user->addUserInterview($userInterview);
        self::assertCount(1, $user->getUserInterviews());

        // Cascade persist
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        self::assertIsInt($user->getId());
        self::assertIsInt($userFramework->getId());
        self::assertIsInt($userInterview->getId());

        $find = $this->entityManager->getRepository(User::class)->find($user->getId());
        self::assertInstanceOf(User::class, $find);
        $find = $this->entityManager->getRepository(UserFramework::class)->find($userFramework->getId());
        self::assertInstanceOf(UserFramework::class, $find);
        $find = $this->entityManager->getRepository(UserInterview::class)->find($userInterview->getId());
        self::assertInstanceOf(UserInterview::class, $find);

        // Cascade delete when remove parent entity
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        self::assertNull($user->getId());
        self::assertNull($userFramework->getId());
        self::assertNull($userInterview->getId());

        // Cascade persist (collections have not been purged)
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        self::assertIsInt($user->getId());
        self::assertIsInt($userFramework->getId());
        self::assertIsInt($userInterview->getId());

        // Purging collections
        $user->removeUserFramework($userFramework);
        $user->removeUserInterview($userInterview);
        self::assertCount(0, $user->getUserFrameworks());
        self::assertCount(0, $user->getUserInterviews());

        // Cascade delete when persist parent entity with empty collection
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        self::assertIsInt($user->getId());
        self::assertNull($userFramework->getId());
        self::assertNull($userInterview->getId());

        // Cleanup
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        self::assertNull($user->getId());
        self::assertCount(0, $user->getUserFrameworks());
        self::assertCount(0, $user->getUserInterviews());

        $find = $this->entityManager->getRepository(User::class)->findOneBy(['guid' => $user->getGuid()]);
        self::assertNull($find);
        $find = $this->entityManager->getRepository(UserFramework::class)->findOneBy(['user' => $user]);
        self::assertNull($find);
        $find = $this->entityManager->getRepository(UserInterview::class)->findOneBy(['user' => $user]);
        self::assertNull($find);
        $framework = $this->entityManager->getRepository(Framework::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[Framework::class],
        ]);
        self::assertInstanceOf(Framework::class, $framework);
        $interview = $this->entityManager->getRepository(Interview::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[Interview::class],
        ]);
        self::assertInstanceOf(Interview::class, $interview);
    }
}
