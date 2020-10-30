<?php

namespace dkemens\s3mediamanager\components;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

/**
 * Custom handler for S3
 */
class S3Manager extends \yii\base\BaseObject
{
    public $bucket = null;
    public $key = null;
    public $body = null;
    public $acl = 'public-read';
    public $client;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    public function init()
    {
        $this->client = S3Client::factory([
            'version' => 'latest',
            'region' => 'us-east-1',
        ]);

        if ($this->bucket === null) {
            return false;
        }

        parent::init();
    }

    /**
     * Uploads a file to s3
     * @param bool $folder whether or not this is a folder
     * @return mixed false if there is an error (logged), the url if it was uploaded successfully
     */
    public function upload($folder = false)
    {
        // Make sure we never end up with double slashes:
        $this->key = str_replace('//', '/', $this->key);
        
        // Upload data.
        $result = $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $this->key,
            'Body' => $this->body,
            'ACL' => $this->acl,
        ]);

        if ($folder === false) {
            // Get the object head and return that
            return $this->client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $this->key,
            ]);
        }
    }

    public function createFolder($key)
    {
        $key = str_replace(' ', '-', ltrim($key, '/'));
        $this->key = $key.'/.folder';
        $this->body = 'Folder placeholder';
        $this->upload(true);

        return $key;
    }

    public function delete($key)
    {
        return $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => urldecode($key),
        ]);
    }

    public function download($key)
    {
        return $this->client->getObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);
    }
}
