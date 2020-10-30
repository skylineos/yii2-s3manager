<?php

namespace dkemens\s3mediamanager;

/**
 * portal module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'dkemens\s3mediamanager\controllers';

    /**
     * The module configuration
     */
    public $configuration;

    /**
     * the session key for the s3 bucket
     *
     * @var        string
     */
    const SESSION_BUCKET_KEY = 'dks3bucket';

    /**
     * The sesion key for the s3 region
     *
     * @var        string
     */
    const SESSION_REGION_KEY = 'dks3region';

    /**
     * The sesion key for the s3 prefix
     *
     * @var        string
     */
    const SESSION_PREFIX_KEY = 'dks3prefix';

    

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->configuration === null) {
            \Yii::error('s3mediamanager configuation must be defined in web/config. Refer to README.');
        }
        
        // custom initialization code goes here
        $this->modules = [];
    }
}
