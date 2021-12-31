<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\InterviewService;

use App\Constant\SecurityConstant;
use App\Entity\Interview;
use App\Entity\User;

/**
 * @group unit
 */
class SetUserRolesTest extends AbstractTestInterviewService
{
    public function testSetUserRoles(): void
    {
        $user = new User();
        $interview = new Interview();
        self::assertCount(0, $interview->getUserInterviews());

        $interview = $this->interviewService->setUserRoles($interview, $user, [SecurityConstant::ROLE_USER]);
        self::assertCount(1, $interview->getUserInterviews());
        self::assertSame($user, $interview->getUserInterviews()->first()->getUser());
        self::assertSame($interview, $interview->getUserInterviews()->first()->getInterview());
        self::assertSame([
            SecurityConstant::ROLE_USER,
        ], $interview->getUserInterviews()->first()->getRoles());

        $interview = $this->interviewService->setUserRoles($interview, $user, [SecurityConstant::ROLE_ADMIN]);
        self::assertCount(1, $interview->getUserInterviews());
        self::assertSame($user, $interview->getUserInterviews()->first()->getUser());
        self::assertSame($interview, $interview->getUserInterviews()->first()->getInterview());
        self::assertSame([
            SecurityConstant::ROLE_USER,
            SecurityConstant::ROLE_ADMIN,
        ], $interview->getUserInterviews()->first()->getRoles());
    }
}
