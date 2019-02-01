<?php declare(strict_types=1);

namespace App\Retriever;

interface S3RetrieverInterface
{
    /**
     * @param RetrievalResource $resource
     * @param bool $replace
     * @return bool
     */
    public function retrieve(RetrievalResource $resource, bool $replace = true) : array;

}
