<?php

namespace App\Vendors\Cos;

use Exception;
use Illuminate\Support\Arr;
use Qcloud\Cos\Client as CosClient;
use Qcloud\Cos\Exception\ServiceResponseException;

class Client
{
    /**
     * @var CosClient
     */
    protected $client;

    protected $bucket;

    public function __construct(array $config)
    {
        $this->setBucket(Arr::get($config, 'bucket'));
        $this->initCosClient($config);
    }

    public function setBucket(string $bucket)
    {
        if (empty($bucket)) {
            throw new CosException('必须配置存储桶');
        }

        $this->bucket = $bucket;
    }

    protected function initCosClient(array $config)
    {
        $this->client = new CosClient([
            'schema' => 'https',
            'region' => Arr::get($config, 'region'),
            'credentials' => [
                'secretId' => Arr::get($config, 'secret_id'),
                'secretKey' => Arr::get($config, 'secret_key'),
            ],
        ]);
    }

    public function getClient(): CosClient
    {
        return $this->client;
    }

    public function has(string $path): bool
    {
        try {
            $this->client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $path,
            ]);
        } catch (ServiceResponseException $e) {
            if ($e->getStatusCode() === 404) {
                return false;
            } else {
                throw $e;
            }
        }

        return true;
    }

    public function writeSteam(string $path, $resource)
    {
        return $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $path,
            'Body' => $resource,
        ]);
    }
}
