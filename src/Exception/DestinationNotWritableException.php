<?php declare(strict_types=1);

namespace App\Exception;

final class DestinationNotWritableException extends RetrievalException
{
    /**
     * @param string $destinationDirectory
     */
    public function __construct(string $destinationDirectory)
    {
        parent::__construct(sprintf(
            'Destination directory "%s" is not writable',
            $destinationDirectory
        ), StatusCodes::DESTINATION_NOT_WRITABLE);
    }
}