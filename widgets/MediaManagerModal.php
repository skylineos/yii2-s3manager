<?php

namespace skylineos\yii\s3manager\widgets;

use yii\base\Widget;
use skylineos\yii\s3manager\Module as skyS3Module;
use skylineos\yii\s3manager\assets\MediaManagerAsset;

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
                : \Yii::$app->modules['s3manager']['configuration']['bucket'];
        }

        if ($this->s3region === null) {
            $this->s3region = isset(\Yii::$app->params['s3region'])
                ? \Yii::$app->params['s3region']
                : \Yii::$app->modules['s3manager']['configuration']['region'];
        }

        if ($this->s3prefix === null) {
            if (isset(\Yii::$app->params['s3prefix'])) {
                $this->s3prefix = \Yii::$app->params['s3prefix'];
            }

            if (isset(\Yii::$app->modules['s3manager']['configuration']['s3prefix'])) {
                $this->s3prefix = \Yii::$app->modules['s3manager']['configuration']['s3prefix'];
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

        return $this->renderFile('@vendor/skylineos/yii2-s3manager/views/default/modal.php', []);
    }
}
