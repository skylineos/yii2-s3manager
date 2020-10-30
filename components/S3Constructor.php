<?php

namespace dkemens\s3mediamanager\components;

use yii\web\BadRequestHttpException;
use yii\helpers\ArrayHelper;
use Aws\S3\S3Client;

class S3Constructor extends \yii\base\BaseObject
{
    /**
     * @var object $s3 the s3 connection object
     * @var string $s3bucket The s3 bucket to use *** REQUIRED ***
     * @var string $s3region The region in which the $s3bucket exists, example 'us-east-1' *** REQUIRED ***
     * @var string $s3prefix The s3 prefix to use. Can be any base folder
     */
    public $s3;
    public $s3bucket;
    public $s3region;
    public $s3prefix = null;

    /**
     * @var array $bucketObject a multidimensional array containing the folders and files in the requested bucket
     */
    private $bucketObject = [];

    /**
     * @var array $bucketObject a multidimensional array containing the folders and files in the requested bucket
     */
    private $folderObject = [];

    /**
     * @var string $s3scheme The s3 scheme to use (typically 'http')
     */
    public $s3scheme = 'http';

    /**
     * @var string $s3version The s3 version to use (typically 'latest')
     */
    public $s3version = 'latest';

    /**
     * @var string $folderIcon the icon to use for folders
     */
    public $folderIcon = 'fas fa-file text-muted';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->s3bucket === null || $this->s3region === null) {
            throw new BadRequestHttpException('Cannot create S3 object without a bucket and a region');
        }

        $this->s3 = new S3Client([
            'version' => $this->s3version,
            'region'  => $this->s3region,
            'scheme' => $this->s3scheme,
        ]);
    }

    public function listObjects(string $prefix = null)
    {
        $prefix = $prefix === null ? $this->s3prefix : $this->s3prefix.'/'.$prefix;

        $parameters = [
            'Bucket' => $this->s3bucket, // REQUIRED
            'Prefix' => $prefix,
            'EncodingType' => 'url',
            'StartAfter' => null,
        ];

        $objects = $this->s3->ListObjectsV2($parameters);
        $isTruncated = $objects['IsTruncated'];

        while ($isTruncated == '1') {
            $lastObject = end($objects['Contents']);
            $parameters['StartAfter'] = $lastObject['Key'];

            $moreObjects = $this->s3->ListObjectsV2($parameters);
            $isTruncated = $moreObjects['IsTruncated'];
            $objects['Contents'] = array_merge($objects['Contents'], $moreObjects['Contents']);
        }

        return $objects;
    }

    public function buildBucket()
    {
        $objects = $this->listObjects();

        if (isset($objects['Contents']) && count($objects['Contents']) > 0) {

            /**
             * Keep track of the things we've added as a checksum of text/parent combination - prevent duplicates
             */
            $index = [];

            /**
             * Create the root folder since it won't be naturally present
             */
            array_push($this->folderObject, [
                'text' => '/',
                'parent' => '#',
                'id' => strlen($this->s3prefix) < 1 ? '/' : $this->s3prefix,
                'icon' => $this->folderIcon,
                'state' => [
                    'opened' => true,
                    'selected' => true,
                ],
            ]);

            $this->bucketObject['/'] = [];

            foreach ($objects['Contents'] as $object) {
                if ($this->s3prefix !== null && $object['Key'] !== $this->s3prefix) {
                    /**
                     * If the key does not contain '/', it's a file in the root directory
                     */
                    if (!preg_match('/\//', $object['Key'])) {
                        $fileType = $this->getType($object['Key']);
                        array_push($this->bucketObject['/'], [
                            'text' => str_replace('+', ' ', $object['Key']),
                            'id' => $object['Key'],
                            'modified' => $this->getModifiedDate($object['LastModified']),
                            'icon' => $fileType['icon'],
                            'filetype' => $fileType['type'],
                            'size' => \Yii::$app->formatter->asSize($object['Size']),
                            'effectiveUrl' => 'https://s3.amazonaws.com/'.$this->s3bucket.'/'.$object['Key'],
                        ]);
                    } else {
                        $parts = explode("/", $object['Key']);

                        for ($i = 0; $i < count($parts) - 1; $i++) {
                            /**
                             * Use id from the previous iteration as parent before resetting it
                             */
                            $parent = $i > 0 ? $id : '/';
                            $id = $parent === '/' ? $parts[$i].'/' : $parent.$parts[$i].'/';

                            if ($this->folderExists('id', $id) === false) {
                                $text = urldecode($parts[$i]);
                                $text = str_replace('%2B', ' ', $text);
                                $text = str_replace('+', ' ', $text);

                                array_push($this->folderObject, [
                                    'text' => $text,
                                    'parent' => $parent,
                                    'id' => $id,
                                    'icon' => $this->folderIcon,
                                ]);
                            }
                        }

                        /**
                         * The last element of parts will always be a file
                         */
                        $filename = end($parts);
                        $fileType = $this->getType($object['Key']);

                        if ($filename !== '.folder') { // Our custom placeholder for making folders with tree.js
                            $parent = substr($object['Key'], 0, strlen($object['Key']) - strlen($filename));

                            if (!isset($this->bucketObject[$parent])) {
                                $this->bucketObject[$parent] = [];
                            }

                            array_push($this->bucketObject[$parent], [
                                'text' => urldecode($filename),
                                'id' => $object['Key'],
                                'modified' => $this->getModifiedDate($object['LastModified']),
                                'icon' => $fileType['icon'],
                                'filetype' => $fileType['type'],
                                'size' => \Yii::$app->formatter->asSize($object['Size']),
                                'effectiveUrl' => 'https://s3.amazonaws.com/'.$this->s3bucket.'/'.$object['Key'],
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function getFolderObject()
    {
        return $this->folderObject;
    }

    public function getBucketObject()
    {
        return json_encode($this->bucketObject);
    }

    public function getObjectHead(string $bucket, string $key)
    {
        return $this->s3->HeadObject([
            'Bucket' => $bucket,
            'Key' => ltrim($key, '/'),
        ]);
    }

    public function getModifiedDate($date)
    {
        $modified = new \DateTime($date);
        return $modified->format('m/d/Y H:i a');
    }

    public function getType(string $key)
    {
        if (preg_match('/./', $key)) {
            $parts = explode('.', $key);
            $ext = end($parts);

            switch (strtolower($ext)) {
                case 'png':
                case 'jpg':
                case 'jpeg':
                case 'gif':
                    return [
                        'icon' => 'fas fa-file-image text-success',
                        'type' => 'image'
                    ];
                    break;

                case 'json':
                case 'kml':
                case 'txt':
                    return [
                        'icon' => 'fas fa-file-text text-muted',
                        'type' => 'document'
                    ];
                    break;

                case 'zip':
                case 'kmz':
                    return [
                        'icon' => 'fas fa-file-archive text-muted',
                        'type' => 'archive'
                    ];
                    break;

                case 'pdf':
                    return [
                        'icon' => 'fas fa-file-pdf text-danger',
                        'type' => 'document'
                    ];
                    break;

                case 'doc':
                case 'docx':
                    return [
                        'icon' => 'fas fa-file-word text-success',
                        'type' => 'document'
                    ];
                    break;

                case 'xls':
                case 'xlsx':
                    return [
                        'icon' => 'fas fa-file-excel text-success',
                        'type' => 'document'
                    ];
                    break;

                default:
                    return [
                        'icon' => 'fas fa-file text-muted',
                        'type' => 'unknown'
                    ];
                    break;
            }
        }

        return [
            'icon' => 'fas fa-file text-muted',
            'type' => 'unknown'
        ];
    }

    public function folderExists($key, $value)
    {
        $subset = \yii\helpers\ArrayHelper::getColumn($this->folderObject, $key);
        return \yii\helpers\ArrayHelper::isIn($value, $subset);
    }
}
