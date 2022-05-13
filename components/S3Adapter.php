<?php

namespace skylineos\yii\s3manager\components;

use yii\web\BadRequestHttpException;
use yii\helpers\ArrayHelper;
use Aws\S3\S3Client;
use League\Flysystem\StorageAttributes;

class S3Adapter extends \yii\base\BaseObject
{
    /**
     * What we use as the root file node
     */
    public const ROOT_DELIMITER = '/';

    public const AWS_PUBLIC_ACL_GROUP = 'http://acs.amazonaws.com/groups/global/AllUsers';

    /**
     * @var string $s3Bucket The s3 bucket to use *** REQUIRED ***
     * @var string $s3Region The region in which the $s3Bucket exists, example 'us-east-1' *** REQUIRED ***
     * @var string $s3Prefix The s3 prefix to use. Can be any base folder
     */
    private $s3;
    public string $s3Bucket;
    public string $s3Region;
    public ?string $s3Prefix = null;

    /**
     * @var League\Flysystem\Filesystem $filesystem
     */
    private \League\Flysystem\Filesystem $filesystem;

    /**
     * @var array $bucketObject a multidimensional array containing the folders and files in the requested bucket
     */
    private array $folderObject = [
        [
            'text' => '/',
            'parent' => '#',
            'id' => '/',
            'icon' => '',
            'state' => [
                'opened' => true,
                'selected' => true,
            ],
        ]
    ];
    private array $bucketObject = ["/" => []];

    /**
     * @var string $s3scheme The s3 scheme to use (typically 'http')
     */
    public string $s3scheme = 'http';

    /**
     * @var string $s3version The s3 version to use (typically 'latest')
     */
    public string $s3version = 'latest';

    /**
     * @var string $folderIcon the icon to use for folders
     */
    public string $folderIcon = 'fas fa-folder text-muted';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->s3Bucket === null || $this->s3Region === null) {
            throw new BadRequestHttpException('Cannot create S3 object without a bucket and a region');
        }

        // Set root folder icon
        $this->folderObject[0]['icon'] = $this->folderIcon;

        $this->s3 = new S3Client([
            'version' => $this->s3version,
            'region'  => $this->s3Region,
            'scheme' => $this->s3scheme,
        ]);

        parent::init();
    }

    /**
     * Builds the full bucket on request - files and folders
     *
     * @return void
     */
    public function buildBucket(): void
    {
        $this->connectAdapter();

        /**
         * Build the full folder/file tree
         * @todo: make this lazy
         */
        $rootObjects = $this->listContents('', true);

        foreach ($rootObjects as $item) {
            if ($item instanceof \League\Flysystem\FileAttributes) {
                $this->addFile(
                    $item->path(),
                    $item->lastModified(),
                    $item->fileSize()
                );
            } elseif ($item instanceof \League\Flysystem\DirectoryAttributes) {
                $this->addFolder($item->path(), $item->path());
            }
        }
    }

    private function getVisibility(string $path): string
    {
        try {
            return $this->filesystem->visibility($path);
        } catch (FilesystemError | UnableToRetrieveMetadata $exception) {
            \Yii::error($exception);
            return 'Unknown';
        }
    }
    /**
     * Lists all items in a filesystem. Prefix and recusive options available. Default functionality is
     * recusive on a given prefix (default null) - build the entire filesystem
     *
     * @param string|null $prefix
     * @param boolean $recursive
     * @return mixed the listing object or false
     */
    public function listContents(string $prefix = '', bool $recursive = false)
    {
        try {
            $listing = $this->filesystem->listContents($prefix, $recursive);
            return $listing;
        } catch (FilesystemError $exception) {
            \Yii::error($exception);
        }

        return false;
    }

    /**
     * Gets an object from s3 for download
     *
     * @param string $key
     * @return AWS\Result the object result
     */
    public function download(string $key): AWS\Result
    {
        return $this->s3->getObject([
            'Bucket' => $this->s3Bucket,
            'Key' => $this->s3Prefix . $key,
        ]);
    }

    /**
     * Deletes an object from s3
     *
     * @param string $key
     * @return object the result of the request
     */
    public function delete(string $key): object
    {
        return $this->s3->deleteObject([
            'Bucket' => $this->s3Bucket,
            'Key' => $this->s3Prefix . $key,
        ]);
    }

    /**
     * Uploads a file to s3
     *
     * @param string $path folder/to/my/cat/pictures
     * @param string $contents the raw contents of the file
     * @param string $filename cat.jpg
     * @return mixed the object head (array) or false
     */
    public function upload(string $path, string $contents, string $filename)
    {
        $this->connectAdapter();

        try {
            if ($path === '/') {
                $key = $filename;
            } else {
                $key = \preg_replace('/(\/+)/', '/', $this->s3Prefix . $path . '/' . $filename);
            }

            $options = [
                'Bucket' => $this->s3Bucket,
                'Key' => $key,
                'Body' => $contents,
                'ACL' => 'public-read',
            ];

            // Special ContentType handler svgs (otherwise they're octetstreams that don't render)
            if (\substr(\strtolower($filename), -3, 3) === 'svg') {
                $options['ContentType'] = 'image/svg+xml';
            }

            $this->s3->putObject($options);

            return $this->s3->headObject([
                'Bucket' => $this->s3Bucket,
                'Key' => $key
            ]);
        } catch (FilesystemError | UnableToWriteFile $exception) {
            \Yii::error($exception);
        }

        return false;
    }

    /**
     * Returns a properly built s3 effective url
     *
     * @param string $key
     * @return string
     */
    public function getEffectiveUrl(string $key): string
    {
        $key = preg_replace('/(\/+)/', '/', "$this->s3Bucket.s3.amazonaws.com/$this->s3Prefix/$key");
        return "https://$key";
    }

    /**
     * Returns a single row properly formatted for frontend display, adding it to the internal
     * memory in the process
     *
     * @param string $key
     * @return array the frontend formatted object
     */
    public function getObjectRow(string $key): array
    {
        $this->connectAdapter();

        $objectHead = $this->s3->headObject([
            'Bucket' => $this->s3Bucket,
            'Key' => preg_replace('/(\/+)/', '/', $this->s3Prefix . $key)
        ]);

        return $this->addFile(
            $key,
            strtotime($objectHead['LastModified']),
            $objectHead['ContentLength']
        );
    }

    /**
     * Creates a folder in s3
     *
     * @param string $path
     * @return boolean
     */
    public function createFolder(string $path): bool
    {
        $this->connectAdapter();

        try {
            $this->filesystem->createDirectory($path);
            return true;
        } catch (FilesystemError | UnableToCreateDirectory $exception) {
            \Yii::error($exception);
        }

        return false;
    }

    /**
     * Deletes a folder in s3, first making sure that the folder is empty
     *
     * @param string $path
     * @return boolean
     */
    public function deleteFolder(string $path): bool
    {
        $this->connectAdapter();

        /**
         * Don't delete a folder with stuff in it
         * listContents will always include itself in the list, so, > 1
         */
        if (count($this->listContents($path)->toArray()) > 1) {
            return false;
        }

        try {
            $this->filesystem->deleteDirectory($path);
            return true;
        } catch (FilesystemError | UnableToDeleteDirectory $exception) {
            \Yii::error($exception);
        }

        return false;
    }

    public function getFolderObject()
    {
        return $this->folderObject;
    }

    public function getBucketObject()
    {
        return json_encode($this->bucketObject);
    }

    /**
     * Initializes the flysystem if needed
     *
     * @return void
     */
    private function connectAdapter(): void
    {
        $this->filesystem = new \League\Flysystem\Filesystem(
            new \League\Flysystem\AwsS3V3\AwsS3V3Adapter(
                $this->s3,
                $this->s3Bucket,
                $this->s3Prefix
            )
        );
    }

    /**
     * Adds a folder to our in-memory filesystem
     *
     * @param string $path the full path of the folder (eg folder1/folder2/folder3)
     * @return integer @see https://php.net/array_push
     */
    private function addFolder(string $path): int
    {
        $folderParts = $this->getPathParts($path);

        $existingFolders = ArrayHelper::getColumn($this->folderObject, 'id');
        if (in_array($folderParts['id'], $existingFolders) || $folderParts['id'] === '') {
            return -1;
        }

        return array_push($this->folderObject, [
            'text' => $folderParts['name'],
            'parent' => $folderParts['parent'],
            'id' => $folderParts['id'],
            'icon' => $this->folderIcon,
            'state' => [
                'opened' => false,
                'selected' => false,
            ],
        ]);
    }

    /**
     * Adds a given file to the bucket object
     *
     * @param string $path the full path to the item (minus prefix)
     * @param string $modified last modfiied
     * @param float $size filesize
     * @return array the bucket object friendly array
     */
    private function addFile(string $path, string $modified, float $size): array
    {
        $fileParts = $this->getPathParts($path);

        if ($fileParts['name'] === '') {
            return [];
        }

        $fileType = $this->getType($path);

        if (!isset($this->bucketObject[$fileParts['parent']])) {
            $this->bucketObject[$fileParts['parent']] = [];
        }

        $item = [
            'text' => $fileParts['name'],
            'id' => $fileParts['id'],
            'modified' => $this->getModifiedDate($modified),
            'icon' => $fileType['icon'],
            'filetype' => $fileType['type'],
            'size' => \Yii::$app->formatter->asSize($size),
            'effectiveUrl' => $this->getEffectiveUrl($path),
        ];

        array_push($this->bucketObject[$fileParts['parent']], $item);

        /**
         * 4/20/2021 - DK
         * In odd cases, folders will not show up (though files will) - I have no idea why. To circumvent
         * the issue, we must check each file's parent folder exists, and add it if false
         */
        $this->addFolder($fileParts['parent']);

        return $item;
    }

    /**
     * Gets the name and parent of a given s3 object. If given
     * folder1/folder2/object, the name becomes object and the parent folder1/folder2
     *
     * @param string $path
     * @return array
     */
    private function getPathParts(?string $path): array
    {
        $parts = explode('/', $path);

        if (count($parts) === 1) {
            return [
                'name' => $parts[0],
                'id' => $parts[0],
                'parent' => self::ROOT_DELIMITER
            ];
        }

        $folderName = $parts[(\count($parts) - 1)];
        unset($parts[(\count($parts) - 1)]);
        $parent = \implode('/', $parts);

        return [
            'name' => $folderName,
            'id' => "$parent/$folderName",
            'parent' => $parent,
        ];
    }

    private function getModifiedDate(string $date): ?string
    {
        return \DateTime::createFromFormat('U', $date)->format('m/d/Y H:i a');
    }

    /**
     * Returns a font awesome icon and file type (for frontend display purposes)
     *
     * @param string $key
     * @return array
     */
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
                case 'svg':
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
}
