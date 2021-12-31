<?php

declare(strict_types=1);

namespace App\Exception\Interview;

use App\Constant\ExceptionConstant;
use App\Exception\AbstractException;

class InterviewRemoveException extends AbstractException
{
    protected $code = ExceptionConstant::ERROR_CODE_INTERVIEW_REMOVE;
}
