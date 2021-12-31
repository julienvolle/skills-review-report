<?php

declare(strict_types=1);

namespace App\Constant;

final class ExceptionConstant
{
    public const ERROR_CODE_SEMVER_CACHE         = 101;
    public const ERROR_CODE_SEMVER_FILE_CONTENTS = 102;
    public const ERROR_CODE_SEMVER_FILE_LOADER   = 103;

    public const ERROR_CODE_FRAMEWORK_IMPORT     = 201;
    public const ERROR_CODE_FRAMEWORK_REPLACE    = 202;
    public const ERROR_CODE_FRAMEWORK_SAVE       = 203;
    public const ERROR_CODE_FRAMEWORK_SEARCH     = 204;
    public const ERROR_CODE_FRAMEWORK_REMOVE     = 205;
    public const ERROR_CODE_FRAMEWORK_EXPORT     = 206;

    public const ERROR_CODE_INTERVIEW_IMPORT     = 301;
    public const ERROR_CODE_INTERVIEW_REPLACE    = 302;
    public const ERROR_CODE_INTERVIEW_SAVE       = 303;
    public const ERROR_CODE_INTERVIEW_SEARCH     = 304;
    public const ERROR_CODE_INTERVIEW_REMOVE     = 305;
    public const ERROR_CODE_INTERVIEW_EXPORT     = 306;
}
