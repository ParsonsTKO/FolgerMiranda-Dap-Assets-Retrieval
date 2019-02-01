<?php declare(strict_types=1);

namespace App\Retriever;

use App\Exception\RetrievalException;

abstract class AbstractRetriever implements RetrieverInterface
{
    /**
     * @var string
     */
    protected $destinationDirectory;

    /**
     * @param string $destinationDirectory
     * @throws \Exception
     */
    public function __construct(string $destinationDirectory)
    {
        if (!is_dir($destinationDirectory)) {
            mkdir($destinationDirectory);
        }

        $testFile = sprintf('%s/%s', $destinationDirectory, uniqid());

        @touch($testFile);

        if (!is_writable($testFile)) {
            throw new \Exception(sprintf(
                'Destination directory "%s" is not writable',
                $destinationDirectory
            ));
        }

        unlink($testFile);

        $this->destinationDirectory = $destinationDirectory;
    }
}