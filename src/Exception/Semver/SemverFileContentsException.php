<?php

declare(strict_types=1);

namespace App\Exception\Semver;

use App\Constant\ExceptionConstant;
use App\Exception\AbstractException;

class SemverFileContentsException extends AbstractException
{
    protected $code = ExceptionConstant::ERROR_CODE_SEMVER_FILE_CONTENTS;
}
