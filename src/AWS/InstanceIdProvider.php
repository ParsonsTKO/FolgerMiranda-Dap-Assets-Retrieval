<?php declare(strict_types=1);

namespace App\AWS;

class InstanceIdProvider
{
    /**
     * @var string
     */
    private $instanceId;

    /**
     * @return string
     */
    public function getInstanceId() : string
    {
        if (null === $this->instanceId) {
            $this->updateInstanceId();
        }

        return $this->instanceId;
    }

    /**
     * Update instance id from AWS metadata
     */
    private function updateInstanceId() : void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://169.254.169.254/latest/meta-data/instance-id');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $instanceId = curl_exec($ch);
        curl_close($ch);

        if (is_string($instanceId) && preg_match('/^i-/', $instanceId)) {
            $this->instanceId = $instanceId;
        } else {
            $this->instanceId = 'unknown';
        }
    }
}