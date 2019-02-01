<?php declare(strict_types=1);

namespace App\AWS;

use App\Exception\SqsException;
use App\Exception\NoMessagesFoundException;
use Aws\S3\S3Client as AwsS3Client;
use Aws\Exception\AwsException;

final class S3Client
{
    /**
     * @var AwsS3Client
     */
    private $awsClient;

    /**
     * @var string
     */
    private $destinationBucket;

    /**
     * @param string $region
     * @param string $destinationBucket
     * @param string|null $profile
     * @throws \Exception
     */
    public function __construct(
        string $region,
        string $destinationBucket,
        string $profile = null
    ){
        $this->awsClient = new AwsS3Client([
            'profile'   => $profile,
            'region'    => $region,
            'version'   => '2006-03-01'
        ]);

        $this->destinationBucket = $destinationBucket;

    }

    public function sendAssetsToBucket($data = null)
    {
        try {
            $manager = new \Aws\S3\Transfer($this->awsClient, $data, $this->destinationBucket);
            $manager->transfer();
            // send success log
        } catch (\Exception $e) {
            // send failure log
        }
    }

    public function putAssetsToBucket($url,$name)
    {
        try {
            $this->awsClient->putObject([
                'Bucket' => $this->destinationBucket,
                'Key'    => $name,
                'SourceFile' => $url,
                'CacheControl' => 'max-age=86400',
                'Metadata' => [
                    'Cache-Control' => 'max-age=86400'
                ],

            ]);
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }


    public function putImageAssetsToBucketFromStream($url,$name)
    {
        try {
            $this->awsClient->putObject([
                'Bucket' => $this->destinationBucket,
                'Key'    => $name,
                'Body'   => $url,
                'ContentType' => 'image/jpeg',
                'CacheControl' => 'max-age=86400',
                'Metadata' => [
                    'Cache-Control' => 'max-age=86400'
                ],

            ]);
            // send success log
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }

    public function getAssetFromBucket($name)
    {

        $objectExists = false;
        $response = array();
        $message = null;

        try {
            $assetContentExists = $this->awsClient->doesObjectExist($this->destinationBucket,$name);
            if(isset($assetContentExists) and $assetContentExists) {
                $message = array(
                    'validation' => array(
                        'success' => true,
                        'bucket' => $this->destinationBucket,
                        'Asset' => $name,
                        'message' => "File " . $name . " On Bucket " . $this->destinationBucket . " Exists",
                    ),
                );
                $objectExists = true;
                $response['exists'] = $objectExists;
                $response['message'] = $message;
                return $response;
            } else {
                throw new \RuntimeException();
            }

        } catch (AwsException $e) {
            return $objectExists;
        } catch (\RuntimeException $e) {
            return $objectExists;
        }

    }

}