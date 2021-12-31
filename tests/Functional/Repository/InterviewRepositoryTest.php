<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Constant\SecurityConstant;
use App\DataFixtures\AppFixtures;
use App\Entity\Interview;
use App\Entity\User;
use App\Security\Voter\AbstractVoter;
use App\Tests\CustomTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Uid\Uuid;

/**
 * @group functional
 */
class InterviewRepositoryTest extends CustomTestCase
{
    private ?KernelBrowser $client = null;
    private ?EntityManagerInterface $entityManager = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    public function tearDown(): void
    {
        unset($this->client);

        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }

    /** @dataProvider providerTestFindAll */
    public function testFindAll(?string $attributes, ?string $userGuid, int $countResult): void
    {
        if ($userGuid) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['guid' => $userGuid]);
            $this->client->loginUser($user);
        }

        if ($attributes) {
            $result = $this->entityManager->getRepository(Interview::class)->findAll($attributes);
        } else {
            $result = $this->entityManager->getRepository(Interview::class)->findAll();
        }

        self::assertIsArray($result);
        self::assertContainsOnlyInstancesOf(Interview::class, $result);
        self::assertCount($countResult, $result);
    }

    public function providerTestFindAll(): iterable
    {
        yield 'without attributes' => [null, null, 1];

        yield 'with granted attributes' => [
            AbstractVoter::ACCESS,
            AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_ADMIN],
            1,
        ];

        yield 'with not granted attributes' => [AbstractVoter::ACCESS, '', 0];
    }

    public function testFindOneByGuid(): void
    {
        self::assertNull($this->entityManager->getRepository(Interview::class)
            ->findOneByGuid((string) Uuid::v4()));

        self::assertInstanceOf(Interview::class, $this->entityManager->getRepository(Interview::class)
            ->findOneByGuid(AppFixtures::GUIDS[Interview::class]));
    }
}
