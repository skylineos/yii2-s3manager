<?php

namespace skyline\yii\s3mediamanager\widgets;

use yii\base\Widget;
use skyline\yii\s3mediamanager\Module as skyS3Module;
use skyline\yii\s3mediamanager\assets\MediaManagerAsset;

class MediaManagerModal extends Widget
{
    /**
     * This should be set here, in params, or in the module config
     *
     * @var string $s3bucket The s3 bucket to use.
     */
    public $s3bucket;

    /**
     * This should be set here, in params, or in the module config
     *
     * @var string $s3region The region in which the $s3bucket exists, example 'us-east-1'
     */
    public $s3region;

    /**
     * @var string $s3prefix The s3 prefix to use. Can be any base folder
     */
    public $s3prefix = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->s3bucket === null) {
            $this->s3bucket = isset(\Yii::$app->params['s3bucket'])
                ? \Yii::$app->params['s3bucket']
                : \Yii::$app->modules['s3mediamanager']['configuration']['bucket'];
        }

        if ($this->s3region === null) {
            $this->s3region = isset(\Yii::$app->params['s3region'])
                ? \Yii::$app->params['s3region']
                : \Yii::$app->modules['s3mediamanager']['configuration']['region'];
        }

        if ($this->s3prefix === null) {
            if (isset(\Yii::$app->params['s3prefix'])) {
                $this->s3prefix = \Yii::$app->params['s3prefix'];
            }

            if (isset(\Yii::$app->modules['s3mediamanager']['configuration']['s3prefix'])) {
                $this->s3prefix = \Yii::$app->modules['s3mediamanager']['configuration']['s3prefix'];
            }

            $this->s3prefix = null;
        }
    }

    /**
     * Renders the media manager wrapped in a modal
     * @return [type] [description]
     */
    public function run()
    {
        MediaManagerAsset::register($this->view);

        \Yii::$app->session->set(skyS3Module::SESSION_BUCKET_KEY, $this->s3bucket);
        \Yii::$app->session->set(skyS3Module::SESSION_REGION_KEY, $this->s3region);
        \Yii::$app->session->set(skyS3Module::SESSION_PREFIX_KEY, $this->s3prefix);

        return $this->renderFile('@vendor/skyline/yii2-aws-s3-manager/views/default/modal.php', []);
    }
}
