<?php

namespace skylineosos\yii\s3manager;

/**
 * portal module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'skylineosos\yii\s3manager\controllers';

    /**
     * The module configuration
     */
    public $configuration;

    /**
     * the session key for the s3 bucket
     *
     * @var        string
     */
    public const SESSION_BUCKET_KEY = 'skys3bucket';

    /**
     * The sesion key for the s3 region
     *
     * @var        string
     */
    public const SESSION_REGION_KEY = 'skys3region';

    /**
     * The sesion key for the s3 prefix
     *
     * @var        string
     */
    public const SESSION_PREFIX_KEY = 'skys3prefix';



    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->configuration === null) {
            \Yii::error('s3manager configuation must be defined in web/config. Refer to README.');
        }

        // custom initialization code goes here
        $this->modules = [];
    }
}
