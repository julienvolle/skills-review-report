<?php

declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use App\Constant\SecurityConstant;
use App\DataFixtures\AppFixtures;
use App\Entity\Category;
use App\Entity\Framework;
use App\Entity\Level;
use App\Entity\Skill;
use App\Entity\User;
use App\Entity\UserFramework;
use App\Tests\CustomTestCase;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @group functional
 */
class FrameworkMappingCollectionTest extends CustomTestCase
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
        // Framework
        $framework = (new Framework())->setName('framework');
        self::assertNull($framework->getId());
        self::assertTrue(Uuid::isValid($framework->getGuid()));
        self::assertInstanceOf(ArrayCollection::class, $framework->getLevels());
        self::assertInstanceOf(ArrayCollection::class, $framework->getCategories());
        self::assertInstanceOf(ArrayCollection::class, $framework->getInterviews());
        self::assertInstanceOf(ArrayCollection::class, $framework->getUserFrameworks());
        self::assertCount(0, $framework->getLevels());
        self::assertCount(0, $framework->getCategories());
        self::assertCount(0, $framework->getInterviews());
        self::assertCount(0, $framework->getUserFrameworks());
        self::assertInstanceOf(DateTime::class, $framework->getCreatedAt());
        self::assertInstanceOf(DateTime::class, $framework->getUpdatedAt());

        // Level
        $level = (new Level())->setName('level');
        self::assertNull($level->getId());
        self::assertTrue(Uuid::isValid($level->getGuid()));
        $framework->addLevel($level);
        self::assertCount(1, $framework->getLevels());
        self::assertSame($framework, $level->getFramework());

        // Category
        $category = (new Category())->setName('category');
        self::assertNull($category->getId());
        self::assertTrue(Uuid::isValid($category->getGuid()));
        self::assertInstanceOf(ArrayCollection::class, $category->getSkills());
        self::assertCount(0, $category->getSkills());
        $framework->addCategory($category);
        self::assertCount(1, $framework->getCategories());
        self::assertSame($framework, $category->getFramework());

        // Skill
        $skill = (new Skill())->setName('skill');
        self::assertNull($skill->getId());
        self::assertTrue(Uuid::isValid($skill->getGuid()));
        $category->addSkill($skill);
        self::assertCount(1, $category->getSkills());
        self::assertSame($category, $skill->getCategory());

        // User
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_ADMIN],
        ]);
        $userFramework = (new UserFramework())->setUser($user);
        self::assertNull($userFramework->getId());
        $framework->addUserFramework($userFramework);
        self::assertCount(1, $framework->getUserFrameworks());

        // Cascade persist
        $this->entityManager->persist($framework);
        $this->entityManager->flush();
        self::assertIsInt($framework->getId());
        self::assertIsInt($level->getId());
        self::assertIsInt($category->getId());
        self::assertIsInt($skill->getId());
        self::assertIsInt($userFramework->getId());

        $find = $this->entityManager->getRepository(Framework::class)->find($framework->getId());
        self::assertInstanceOf(Framework::class, $find);
        $find = $this->entityManager->getRepository(Level::class)->find($level->getId());
        self::assertInstanceOf(Level::class, $find);
        $find = $this->entityManager->getRepository(Category::class)->find($category->getId());
        self::assertInstanceOf(Category::class, $find);
        $find = $this->entityManager->getRepository(Skill::class)->find($skill->getId());
        self::assertInstanceOf(Skill::class, $find);
        $find = $this->entityManager->getRepository(UserFramework::class)->find($userFramework->getId());
        self::assertInstanceOf(UserFramework::class, $find);

        // Cascade delete when remove parent entity
        $this->entityManager->remove($framework);
        $this->entityManager->flush();
        self::assertNull($framework->getId());
        self::assertNull($level->getId());
        self::assertNull($category->getId());
        self::assertNull($skill->getId());
        self::assertNull($userFramework->getId());

        // Cascade persist (collections have not been purged)
        $this->entityManager->persist($framework);
        $this->entityManager->flush();
        self::assertIsInt($framework->getId());
        self::assertIsInt($level->getId());
        self::assertIsInt($category->getId());
        self::assertIsInt($skill->getId());
        self::assertIsInt($userFramework->getId());

        // Purging collections
        $category->removeSkill($skill);
        self::assertCount(0, $category->getSkills());
        $framework->removeCategory($category);
        $framework->removeUserFramework($userFramework);
        $framework->removeLevel($level);
        self::assertCount(0, $framework->getLevels());
        self::assertCount(0, $framework->getCategories());
        self::assertCount(0, $framework->getUserFrameworks());

        // Cascade delete when persist parent entity with empty collection
        $this->entityManager->persist($framework);
        $this->entityManager->flush();
        self::assertIsInt($framework->getId());
        self::assertNull($level->getId());
        self::assertNull($category->getId());
        self::assertNull($skill->getId());
        self::assertNull($userFramework->getId());

        // Cleanup
        $this->entityManager->remove($framework);
        $this->entityManager->flush();
        self::assertNull($framework->getId());
        self::assertCount(0, $category->getSkills());
        self::assertCount(0, $framework->getLevels());
        self::assertCount(0, $framework->getCategories());
        self::assertCount(0, $framework->getUserFrameworks());

        $find = $this->entityManager->getRepository(Framework::class)->findOneBy(['guid' => $framework->getGuid()]);
        self::assertNull($find);
        $find = $this->entityManager->getRepository(Level::class)->findOneBy(['guid' => $level->getGuid()]);
        self::assertNull($find);
        $find = $this->entityManager->getRepository(Category::class)->findOneBy(['guid' => $category->getGuid()]);
        self::assertNull($find);
        $find = $this->entityManager->getRepository(Skill::class)->findOneBy(['guid' => $skill->getGuid()]);
        self::assertNull($find);
        $find = $this->entityManager->getRepository(UserFramework::class)->findOneBy(['framework' => $framework]);
        self::assertNull($find);
    }
}
