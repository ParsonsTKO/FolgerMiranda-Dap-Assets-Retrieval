<?php declare(strict_types=1);

namespace App\Exception;

final class StatusCodes
{
    private function __construct()
    {
        // Can not be instantiated
    }

    const DEBUG                             = 000;

    const OK                                = 200;
    const REPLACED                          = 201;

    const AWS_ERROR                         = 401;
    const BAD_MESSAGE                       = 402;
    const SOURCE_NOT_FOUND                  = 404;

    const DESTINATION_NOT_WRITABLE          = 501;
    const DESTINATION_ALREADY_EXISTS        = 502;
    const DESTINATION_COULD_NOT_BE_REPLACED = 503;
    const COPY_FAILED                       = 504;
}