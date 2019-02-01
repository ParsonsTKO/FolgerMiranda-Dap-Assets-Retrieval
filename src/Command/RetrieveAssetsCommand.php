<?php declare(strict_types=1);

namespace App\Command;

use App\AWS\InstanceIdProvider;
use App\AWS\SqsClient;
use App\AWS\S3Client;
use App\Exception\NoMessagesFoundException;
use App\Exception\RetrievalException;
use App\Exception\StatusCodes;
use App\Retriever\RetrievalResource;
use App\Retriever\RetrieverInterface;
use App\Retriever\S3RetrieverInterface;
use App\WaitStrategy\WaitStrategyInterface;
use Aws\Exception\AwsException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Exception\SourceNotFoundException;

final class RetrieveAssetsCommand extends Command
{
    /**
     * @var SqsClient
     */
    private $sqsClient;

    /**
     * @var S3Client
     */
    private $s3Client;

    /**
     * @var S3RetrieverInterface
     */
    private $retriever;

    /**
     * @var WaitStrategyInterface
     */
    private $waitStrategy;

    /**
     * @var InstanceIdProvider
     */
    private $instanceIdProvider;

    /**
     * @var int|null
     */
    private $maximumRuntime;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var bool
     */
    private $replace = false;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param SqsClient $sqsClient
     * @param S3Client $s3Client
     * @param S3RetrieverInterface $retriever
     * @param WaitStrategyInterface $waitStrategy
     * @param InstanceIdProvider $instanceIdProvider
     * @param int|null $maximumRuntime
     */
    public function __construct(
        SqsClient $sqsClient,
        S3Client $s3Client,
        S3RetrieverInterface $retriever,
        WaitStrategyInterface $waitStrategy,
        InstanceIdProvider $instanceIdProvider,
        int $maximumRuntime = null
    ) {
        $this->sqsClient = $sqsClient;
        $this->s3Client = $s3Client;
        $this->retriever = $retriever;
        $this->waitStrategy = $waitStrategy;
        $this->instanceIdProvider = $instanceIdProvider;
        $this->maximumRuntime = $maximumRuntime;

        parent::__construct('assets:retrieve');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption(
                'debug',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Show debug output',
                false
            )
            ->addOption(
                'replace',
                'r',
                InputOption::VALUE_OPTIONAL,
                'Replace images if already downloaded',
                false
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->debug = (bool) $input->getOption('debug');
        $this->replace = (bool) $input->getOption('replace');
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = time();

        $this->debug('Begin');

        while (null === $this->maximumRuntime || ($start + $this->maximumRuntime > time())) {
            $this->debug('Receiving message');

            try {
                /** @var array $message */
                $message = $this->sqsClient->receiveMessage();
                $replaced = false;
                $retrieveMessage = '';
                //$message = array('destinationFilename' => "d62bf1c8-2fb1-4c8b-b9dd-5212d62dbbf3.TEXT/CSV", 'fileURL' => "https://s3.us-east-2.amazonaws.com/miranda-files/externalprojects/SDFB_datasets_2018_11_07.zip", 'encodingFormat' => "text/csv");
                $this->debug(sprintf(
                    'Message received: %s',
                    json_encode($message)
                ));
                $this->debug('Retrieving asset');

                $resource = new RetrievalResource($message);

                $test = $this->s3Client->getAssetFromBucket($resource->getDestinationFilename());

                try {

                    if(!is_null($test['message'])) {
                        if ($this->replace) {
                            $retrieve = $this->retriever->retrieve($resource, $this->replace);
                            $this->s3Client->putAssetsToBucket($retrieve['destination'],$resource->getDestinationFilename());
                            $replacementMessage = "Asset updated with new version to Bucket ";
                            $retrieveMessage = "(replaced previous version)";
                            $replaced = $retrieve['replaced'];

                        } else {
                            $replacementMessage = "Asset not Overwritten in Bucket ";
                        }

                    } else {
                        $retrieve = $this->retriever->retrieve($resource, $this->replace);
                        $this->s3Client->putAssetsToBucket($retrieve['destination'],$resource->getDestinationFilename());
                        $replaced = true;
                        $retrieveMessage = " (New Asset Downloaded to Bucket)";
                        $replacementMessage = "New Asset copied to Bucket ";
                    }

                    if(isset($retrieve['destination']) and !is_null($retrieve['destination'])){
                        @unlink($retrieve['destination']);
                    }

                    $this->sqsClient->deleteLastMessage();

                    $this->success(sprintf(
                        $replacementMessage . '"%s"%s',
                        $resource->getDestinationFilename(),
                        $replaced ? $retrieveMessage : ' No replacement of Asset'
                    ), $replaced, $message);

                    $this->waitStrategy->reset();

                } catch (\Exception $exception) {
                    $classException = get_class($exception);
                    $this->error(sprintf(
                        'Asset retrieval failed: %s',
                        'Exception:' . $classException,
                        $exception->getMessage()
                    ), $exception->getCode(), $message);
                    //throw new $classException($exception->getMessage());
                }

            } catch (AwsException $exception) {
                $this->error(sprintf(
                    'AWS Exception: %s',
                    $exception->getMessage()
                ), StatusCodes::AWS_ERROR);
                //pcntl_signal(SIGTERM, [$this, 'doTerminate']);
                $this->output->writeln('Restarting command');
                exit;
            } catch (NoMessagesFoundException $exception) {
                $this->debug('No messages found');

                $wait = $this->waitStrategy->next();

                $this->debug(sprintf('Sleeping for %d seconds', $wait));
                sleep($wait);
            } catch (RetrievalException $exception) {
                if (StatusCodes::DESTINATION_ALREADY_EXISTS === $exception->getCode()) {
                    $this->sqsClient->deleteLastMessage();
                }

                $this->error(sprintf(
                    'Asset retrieval failed: %s',
                    $exception->getMessage()
                ), $exception->getCode(), $message);
            }
        }

        $this->debug('Finished: Timeout');
    }

    /**
     * @param string $message
     * @param bool $replaced
     * @param array|null $sqsMessage
     */
    private function success(string $message, bool $replaced, array $sqsMessage = null) : void
    {
        $this->writeln(
            'success',
            $replaced ? StatusCodes::REPLACED : StatusCodes::OK,
            $message,
            $sqsMessage
        );
    }

    private function doTerminate()
    {
        $this->output->writeln('AWS Exception -- Restarting Command.');

        exit;
    }
    /**
     * @param string $message
     * @param array|null $sqsMessage
     */
    private function debug(string $message, array $sqsMessage = null) : void
    {
        if ($this->debug) {
            $this->writeln('debug', StatusCodes::DEBUG, $message, $sqsMessage);
        }
    }

    /**
     * @param string $message
     * @param int $statusCode
     * @param array|null $sqsMessage
     */
    private function error(string $message, int $statusCode, array $sqsMessage = null) : void
    {
        $this->writeln('error', $statusCode, $message, $sqsMessage);
    }

    /**
     * @param string $type
     * @param int $statusCode
     * @param string $message
     * @param array|null $sqsMessage
     */
    private function writeln(string $type, int $statusCode, string $message, array $sqsMessage = null) : void
    {
        $this->output->writeln(sprintf(
            '[%s] [%s] [%d] [instance %s] %s%s',
            (new \DateTime())->format('Y-m-d H:i:s'),
            $type,
            $statusCode,
            $this->instanceIdProvider->getInstanceId(),
            $message,
            $sqsMessage ? sprintf(' [%s]', json_encode($sqsMessage)) : ''
        ));
    }
}
