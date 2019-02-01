<?php declare(strict_types=1);

namespace App\Exception;

final class KeysMissingFromMessageException extends RetrievalException
{
    /**
     * @param array $message
     * @param array $keys
     */
    public function __construct(array $message, array $keys)
    {
        $notFoundKeys = [];

        foreach ($keys as $key) {
            if (!array_key_exists($key, $message)) {
                $notFoundKeys[] = $key;
            }
        }

        parent::__construct(sprintf(
            'Message was missing the following key(s): "%s". Available key(s): "%s"',
            implode('", "', $notFoundKeys),
            implode('", "', array_keys($message))
        ), StatusCodes::BAD_MESSAGE);
    }
}