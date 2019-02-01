<?php declare(strict_types=1);

namespace App\Retriever;

use App\Exception\InvalidValueTypeInMessageException;
use App\Exception\KeysMissingFromMessageException;
use App\Exception\RetrievalException;

final class RetrievalResource
{
    const KEYS = [
        'destinationFilename',
        'fileURL',
        'encodingFormat',
    ];

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $destinationFilename;

    /**
     * @var string
     */
    private $format;

    /**
     * @param array $message
     * @throws RetrievalException
     */
    public function __construct(array $message)
    {
        foreach (self::KEYS as $key) {
            if (!isset($message[$key])) {
                throw new KeysMissingFromMessageException($message, self::KEYS);
            }

            if (!is_string($message[$key])) {
                throw new InvalidValueTypeInMessageException($message, $key);
            }
        }

        $this->source = $message['fileURL'];
        $this->destinationFilename = $message['destinationFilename'];
        $this->format = $message['encodingFormat'];
    }

    /**
     * @return string
     */
    public function getSource() : string
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getDestinationFilename() : string
    {
        return $this->destinationFilename;
    }

    /**
     * @return string
     */
    public function getFormat() : string
    {
        return $this->format;
    }
}
