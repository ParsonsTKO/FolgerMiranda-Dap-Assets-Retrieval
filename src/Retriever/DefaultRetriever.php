<?php declare(strict_types=1);

namespace App\Retriever;

use App\Exception\CopyFailedException;
use App\Exception\DestinationAlreadyExistsException;
use App\Exception\DestinationCouldNotBeReplacedException;
use App\Exception\DestinationNotWritableException;
use App\Exception\SourceNotFoundException;

final class DefaultRetriever extends AbstractRetriever
{
    /**
     * {@inheritdoc}
     */
    public function retrieve(RetrievalResource $resource, bool $replace = true) : bool
    {
        $replaced = false;
        $destination = sprintf('%s/%s', $this->destinationDirectory, $resource->getDestinationFilename());

        if (is_file($destination)) {
            if (!$replace) {
                throw new DestinationAlreadyExistsException($destination);
            }

            @unlink($destination);
            $replaced = true;

            if (file_exists($destination)) {
                throw new DestinationCouldNotBeReplacedException($destination);
            }
        }

        if (!@fopen($resource->getSource(),'r')) {
            throw new SourceNotFoundException($resource->getSource());
        }

        if (!@copy($resource->getSource(), $destination)) {
            $errors = error_get_last();

            throw new CopyFailedException($errors['message']);
        }

        return $replaced;
    }

    /**
     * @throws DestinationNotWritableException
     */
    public function test() : void
    {
        $destination = $destination = sprintf('%s/%s', $this->destinationDirectory, uniqid('_test_'));

        @touch($destination);

        if (!file_exists($destination)) {
            throw new DestinationNotWritableException($this->destinationDirectory);
        }

        @unlink($destination);
    }
}