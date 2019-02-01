<?php declare(strict_types=1);

namespace App\Exception;

final class SqsException extends RetrievalException
{
    /**
     * @param string $body
     */
    public function __construct(string $body)
    {
        parent::__construct(sprintf(
            'Message body contains invalid JSON: %s',
            $body
        ), StatusCodes::BAD_MESSAGE);
    }
}