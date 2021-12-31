<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Constant\SecurityConstant;
use App\Entity\Category;
use App\Entity\Framework;
use App\Entity\Interview;
use App\Entity\Level;
use App\Entity\Skill;
use App\Entity\User;
use App\Entity\UserFramework;
use App\Entity\UserInterview;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public const GUIDS = [
        User::class => [
            SecurityConstant::ROLE_USER        => 'e2b7564a-da8e-406d-a8a2-be5e23754b8b',
            SecurityConstant::ROLE_ADMIN       => '176163b1-d774-4690-b595-eb77f0de2a32',
            SecurityConstant::ROLE_SUPER_ADMIN => '59b49911-c326-4bf4-99dc-691099f8b7f8',
        ],
        Framework::class => 'ba3efdca-cdb1-4696-ba55-9599f79bd572',
        Interview::class => '3275c968-1793-4133-a520-e01ed90cece0',
    ];

    public const CREDENTIALS = [
        SecurityConstant::ROLE_USER        => ['username' => 'user@skills-review.com',  'password' => '__user'],
        SecurityConstant::ROLE_ADMIN       => ['username' => 'admin@skills-review.com', 'password' => '__admin'],
        SecurityConstant::ROLE_SUPER_ADMIN => ['username' => 'root@skills-review.com',  'password' => '__root'],
    ];

    private UserPasswordHasherInterface $encoder;
    private array $data = [];

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUser($manager);
        $this->loadFramework($manager);
        $this->loadInterview($manager);
    }

    private function loadUser(ObjectManager $manager): void
    {
        $user = (new User())
            ->setEmail(self::CREDENTIALS[SecurityConstant::ROLE_USER]['username'])
            ->setRoles([SecurityConstant::ROLE_USER]);
        $user->setPassword(
            $this->encoder->hashPassword($user, self::CREDENTIALS[SecurityConstant::ROLE_USER]['password'])
        );
        $user->setGuid(self::GUIDS[User::class][SecurityConstant::ROLE_USER]);
        $manager->persist($user);

        $admin = (new User())
            ->setEmail(self::CREDENTIALS[SecurityConstant::ROLE_ADMIN]['username'])
            ->setRoles([SecurityConstant::ROLE_ADMIN]);
        $admin->setPassword(
            $this->encoder->hashPassword($admin, self::CREDENTIALS[SecurityConstant::ROLE_ADMIN]['password'])
        );
        $admin->setGuid(self::GUIDS[User::class][SecurityConstant::ROLE_ADMIN]);
        $manager->persist($admin);

        $root = (new User())
            ->setEmail(self::CREDENTIALS[SecurityConstant::ROLE_SUPER_ADMIN]['username'])
            ->setRoles([SecurityConstant::ROLE_SUPER_ADMIN]);
        $root->setPassword(
            $this->encoder->hashPassword($root, self::CREDENTIALS[SecurityConstant::ROLE_SUPER_ADMIN]['password'])
        );
        $root->setGuid(self::GUIDS[User::class][SecurityConstant::ROLE_SUPER_ADMIN]);
        $manager->persist($root);

        $manager->flush();

        $this->data[User::class] = $admin;
    }

    private function loadFramework(ObjectManager $manager): void
    {
        $framework = (new Framework())
            ->setGuid(self::GUIDS[Framework::class])
            ->setName('My first framework')
            ->addLevel((new Level())->setName('OK')->setPriority(1))
            ->addLevel((new Level())->setName('KO')->setPriority(2))
            ->addCategory(
                (new Category())
                    ->setName('My first category')
                    ->addSkill((new Skill())->setName('My first skill'))
            )
            ->addUserFramework((new UserFramework())
                ->setUser($this->data[User::class])
                ->setRoles([SecurityConstant::ROLE_ADMIN]));

        $manager->persist($framework);
        $manager->flush();

        $this->data[Framework::class] = $framework;
    }

    private function loadInterview(ObjectManager $manager): void
    {
        $interview = (new Interview())
            ->setGuid(self::GUIDS[Interview::class])
            ->setTitle('My first interview')
            ->setLastname('John')
            ->setFirstname('Doe')
            ->setFramework($this->data[Framework::class])
            ->addUserInterview((new UserInterview())
                ->setUser($this->data[User::class])
                ->setRoles([SecurityConstant::ROLE_ADMIN]));

        $manager->persist($interview);
        $manager->flush();

        $this->data[Interview::class] = $interview;
    }
}
