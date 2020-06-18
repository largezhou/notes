<?php

namespace App\Vendors\Cos;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;

class CosAdapter extends AbstractAdapter
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function has($path)
    {
        echo 'has: '.$path.PHP_EOL;
        return $this->client->has($path);
    }

    public function read($path)
    {
        echo 'read'.PHP_EOL;
    }

    public function write($path, $contents, Config $config)
    {
        echo 'write'.PHP_EOL;
    }

    public function delete($path)
    {
        echo 'delete'.PHP_EOL;
    }

    public function update($path, $contents, Config $config)
    {
        echo 'update'.PHP_EOL;
    }

    public function rename($path, $newpath)
    {
        echo 'rename'.PHP_EOL;
    }

    public function copy($path, $newpath)
    {
        echo 'copy'.PHP_EOL;
    }

    public function createDir($dirname, Config $config)
    {
        echo 'createDir'.PHP_EOL;
    }

    public function deleteDir($dirname)
    {
        echo 'deleteDir'.PHP_EOL;
    }

    public function setVisibility($path, $visibility)
    {
        echo 'setVisibility'.PHP_EOL;
    }

    public function getVisibility($path)
    {
        echo 'getVisibility'.PHP_EOL;
    }

    public function getTimestamp($path)
    {
        echo 'getTimestamp'.PHP_EOL;
    }

    public function getSize($path)
    {
        echo 'getSize'.PHP_EOL;
    }

    public function getMetadata($path)
    {
        echo 'getMetadata'.PHP_EOL;
    }

    public function getMimetype($path)
    {
        echo 'getMimetype'.PHP_EOL;
    }

    public function listContents($directory = '', $recursive = false)
    {
        echo 'listContents'.PHP_EOL;
    }

    public function readStream($path)
    {
        echo 'readStream'.PHP_EOL;
    }

    public function writeStream($path, $resource, Config $config)
    {
        return $this->client->writeSteam($path, $resource);
    }

    public function updateStream($path, $resource, Config $config)
    {
        echo 'updateStream'.PHP_EOL;
    }
}
