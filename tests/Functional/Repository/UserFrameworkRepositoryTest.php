<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Constant\SecurityConstant;
use App\DataFixtures\AppFixtures;
use App\Entity\Framework;
use App\Entity\User;
use App\Entity\UserFramework;
use App\Tests\CustomTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @group functional
 */
class UserFrameworkRepositoryTest extends CustomTestCase
{
    private ?EntityManagerInterface $entityManager = null;

    public function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    public function tearDown(): void
    {
        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }

    public function testFindByUserAndFramework(): void
    {
        $framework = $this->entityManager->getRepository(Framework::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[Framework::class],
        ]);

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_USER],
        ]);

        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_ADMIN],
        ]);

        self::assertNull($this->entityManager
            ->getRepository(UserFramework::class)
            ->findByUserAndFramework($framework, $user));

        self::assertInstanceOf(UserFramework::class, $this->entityManager
            ->getRepository(UserFramework::class)
            ->findByUserAndFramework($framework, $admin));
    }
}
