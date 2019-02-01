<?php declare(strict_types=1);

namespace App\Exception;

final class CopyFailedException extends RetrievalException
{
    /**
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct(sprintf(
            'Copy Failed: %s',
            $message
        ), StatusCodes::COPY_FAILED);
    }
}