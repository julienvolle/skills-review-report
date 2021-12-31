<?php

declare(strict_types=1);

namespace App\Exception\Framework;

use App\Constant\ExceptionConstant;
use App\Exception\AbstractException;

class FrameworkSaveException extends AbstractException
{
    protected $code = ExceptionConstant::ERROR_CODE_FRAMEWORK_SAVE;
}
