<?php declare(strict_types=1);

namespace App\Exception;

final class DestinationAlreadyExistsException extends RetrievalException
{
    /**
     * @param string $destination
     */
    public function __construct(string $destination)
    {
        parent::__construct(sprintf(
            'Destination "%s" already exists and retriever has not be set to replace files',
            $destination
        ), StatusCodes::DESTINATION_ALREADY_EXISTS);
    }
}