<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Constant\SecurityConstant;
use App\DataFixtures\AppFixtures;
use App\Entity\Interview;
use App\Entity\User;
use App\Entity\UserInterview;
use App\Tests\CustomTestCase;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @group functional
 */
class UserInterviewRepositoryTest extends CustomTestCase
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

    public function testFindByUserAndInterview(): void
    {
        $interview = $this->entityManager->getRepository(Interview::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[Interview::class],
        ]);

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_USER],
        ]);

        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_ADMIN],
        ]);

        self::assertNull($this->entityManager
            ->getRepository(UserInterview::class)
            ->findByUserAndInterview($interview, $user));

        self::assertInstanceOf(UserInterview::class, $this->entityManager
            ->getRepository(UserInterview::class)
            ->findByUserAndInterview($interview, $admin));
    }
}
