<?php declare(strict_types=1);

namespace App\Exception;

final class DestinationCouldNotBeReplacedException extends RetrievalException
{
    /**
     * @param string $destination
     */
    public function __construct(string $destination)
    {
        parent::__construct(sprintf(
            'Destination "%s" could not be replaced as it could not be removed',
            $destination
        ), StatusCodes::DESTINATION_COULD_NOT_BE_REPLACED);
    }
}