<?php declare(strict_types=1);

namespace App\Retriever;

use App\Exception\CopyFailedException;
use App\Exception\DestinationAlreadyExistsException;
use App\Exception\DestinationCouldNotBeReplacedException;
use App\Exception\DestinationNotWritableException;
use App\Exception\SourceNotFoundException;

final class S3Retriever extends AbstractS3Retriever
{
    /**
     * {@inheritdoc}
     */
    /**
     * {@inheritdoc}
     */
    public function retrieve(RetrievalResource $resource, bool $replace = true) : array
    {
        $response = array();
        $destination = sprintf('%s/%s', '/tmp', $resource->getDestinationFilename());

        if (!@fopen($resource->getSource(),'r')) {
            throw new SourceNotFoundException($resource->getSource());
        }

        if (!@copy($resource->getSource(), $destination)) {
            $errors = error_get_last();

            throw new CopyFailedException($errors['message']);
        }

        $response['replaced'] = $replace;
        $response['destination'] = $destination;
        return $response;
    }

    /**
     * @throws DestinationNotWritableException
     */
    public function test() : void
    {
        $destination = $destination = sprintf('%s/%s', '/tmp', uniqid('_test_'));

        @touch($destination);

        if (!file_exists($destination)) {
            throw new DestinationNotWritableException('/tmp');
        }

        @unlink($destination);
    }
}
