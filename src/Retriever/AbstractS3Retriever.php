<?php declare(strict_types=1);

namespace App\Retriever;

use App\Exception\RetrievalException;

abstract class AbstractS3Retriever implements S3RetrieverInterface
{
    /**
     * @var string
     */
    protected $tmpCopyToS3;

    /**
     * @param string $destinationBucket
     * @throws \Exception
     */
    public function __construct(string $tmpCopyToS3)
    {

        $this->$tmpCopyToS3 = $tmpCopyToS3;
    }
}
