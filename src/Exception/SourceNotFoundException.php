<?php declare(strict_types=1);

namespace App\Exception;

final class SourceNotFoundException extends RetrievalException
{
    /**
     * @param string $source
     */
    public function __construct(string $source)
    {
        parent::__construct(sprintf(
            'Source file "%s" could not be found',
            $source
        ), StatusCodes::SOURCE_NOT_FOUND);
    }
}