<?php declare(strict_types=1);

namespace App\AWS;

use App\Exception\SqsException;
use App\Exception\NoMessagesFoundException;
use Aws\Sqs\SqsClient as AwsSqsClient;

final class SqsClient
{
    /**
     * @var AwsSqsClient
     */
    private $awsClient;

    /**
     * @var string
     */
    private $queueUrl;

    /**
     * @var int
     */
    private $waitTime;

    /**
     * @var string
     */
    private $lastReceiptHandle;

    /**
     * @param int $waitTime
     * @param string $region
     * @param string $queueUrl
     * @param string|null $profile
     * @throws \Exception
     */
    public function __construct(
        int $waitTime,
        string $region,
        string $queueUrl,
        string $profile = null
    ) {
        $this->awsClient = new AwsSqsClient([
            'profile'   => $profile,
            'region'    => $region,
            'version'   => '2012-11-05'
        ]);

        $this->queueUrl = $queueUrl;

        if ($waitTime < 0 || $waitTime > 20) {
            throw new \Exception(sprintf(
                'Wait time "%d" is invalid. Wait time is expected to be >= 0 & <=20',
                $waitTime
            ));
        }

        $this->waitTime = $waitTime;
    }

    /**
     * @return array
     * @throws NoMessagesFoundException
     * @throws SqsException
     */
    public function receiveMessage() : array
    {
        $result = $this->awsClient->receiveMessage([
            'AttributeNames'        => ['SentTimestamp'],
            'MaxNumberOfMessages'   => 1,
            'MessageAttributeNames' => ['All'],
            'QueueUrl'              => $this->queueUrl,
            'WaitTimeSeconds'       => $this->waitTime,
        ]);

        $messages = $result->get('Messages');

        if (null !== $messages && count($messages) > 0) {
            $this->lastReceiptHandle = $messages[0]['ReceiptHandle'];

            $array = json_decode($messages[0]['Body'], true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new SqsException($messages[0]['Body']);
            }

            return $array;
        }

        throw new NoMessagesFoundException();
    }

    /**
     * Delete last received message
     */
    public function deleteLastMessage() : void
    {
        if (null !== $this->lastReceiptHandle) {
            $this->awsClient->deleteMessage([
                'QueueUrl'      => $this->queueUrl,
                'ReceiptHandle' => $this->lastReceiptHandle,
            ]);

            $this->lastReceiptHandle = null;
        }
    }
}
