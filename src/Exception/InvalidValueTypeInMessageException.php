<?php declare(strict_types=1);

namespace App\Exception;

final class InvalidValueTypeInMessageException extends RetrievalException
{
    /**
     * @param array $message
     * @param string $key
     */
    public function __construct(array $message, string $key)
    {
        parent::__construct(sprintf(
            'Message value with key "%s" is expected to be a string. "%s" given',
            $key,
            is_object($message[$key]) ? get_class($message[$key]) : gettype($message[$key])
        ), StatusCodes::BAD_MESSAGE);
    }
}
