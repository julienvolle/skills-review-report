<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\FrameworkService;

use App\Constant\SecurityConstant;
use App\Entity\Framework;
use App\Entity\User;

/**
 * @group unit
 */
class SetUserRolesTest extends AbstractTestFrameworkService
{
    public function testSetUserRoles(): void
    {
        $user = new User();
        $framework = new Framework();
        self::assertCount(0, $framework->getUserFrameworks());

        $framework = $this->frameworkService->setUserRoles($framework, $user, [SecurityConstant::ROLE_USER]);
        self::assertCount(1, $framework->getUserFrameworks());
        self::assertSame($user, $framework->getUserFrameworks()->first()->getUser());
        self::assertSame($framework, $framework->getUserFrameworks()->first()->getFramework());
        self::assertSame([
            SecurityConstant::ROLE_USER,
        ], $framework->getUserFrameworks()->first()->getRoles());

        $framework = $this->frameworkService->setUserRoles($framework, $user, [SecurityConstant::ROLE_ADMIN]);
        self::assertCount(1, $framework->getUserFrameworks());
        self::assertSame($user, $framework->getUserFrameworks()->first()->getUser());
        self::assertSame($framework, $framework->getUserFrameworks()->first()->getFramework());
        self::assertSame([
            SecurityConstant::ROLE_USER,
            SecurityConstant::ROLE_ADMIN,
        ], $framework->getUserFrameworks()->first()->getRoles());
    }
}
