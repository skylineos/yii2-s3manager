<?php

namespace dkemens\s3mediamanager\components;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\InputWidget;
use dkemens\s3mediamanager\components\S3Constructor;
use dkemens\s3mediamanager\assets\MediaManagerAsset;

class FileInput extends InputWidget
{
	
    const DATA_ID = 'id';
    const DATA_URL = 'url';

	/**
	 * @var array $s3 the s3constructor singleton
	 * @var string $s3bucket The s3 bucket to use *** REQUIRED ***
	 * @var string $s3region The region in which the $s3bucket exists, example 'us-east-1' *** REQUIRED ***
	 * @var string $delimiter The s3 prefix to use. Can be any base folder
	 */
	public $s3, $s3bucket, $s3region, $delimiter = null;

    /**
     * @var string widget template
     */
    public $template = '<div class="input-group">{input}<span class="input-group-btn">{button}{reset-button}</span></div>';

    /**
     * @var string button tag
     */
    public $buttonTag = 'button';

    /**
     * @var string button name
     */
    public $buttonName = '<i class="fa fa-browse"></i>';

    /**
     * @var array button html options
     */
    public $buttonOptions = ['class' => 'btn btn-primary'];

    /**
     * @var string reset button tag
     */
    public $resetButtonTag = 'button';

    /**
     * @var string reset button name
     */
    public $resetButtonName = '<i class="fa fa-times-circle"></i>';

    /**
     * @var array reset button html options
     */
    public $resetButtonOptions = ['class' => 'btn btn-danger'];

    /**
     * @var string This data will be inserted in input field
     */
    public $pasteData = self::DATA_URL;

    /**
     * @var array widget html options
     */
    public $options = ['class' => 'form-control'];


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->buttonOptions['id'])) {
            $this->buttonOptions['id'] = $this->options['id'] . '-btn';
        }

        $this->s3 = new S3Constructor(['s3bucket' => $this->s3bucket, 's3region' => $this->s3region, 'delimiter' => $this->delimiter]);

        $this->buttonOptions['data-toggle'] = 'modal';
        $this->buttonOptions['href'] = '#MediaManager';
        $this->resetButtonOptions['role'] = 'clear-input';
        $this->resetButtonOptions['data-clear-element-id'] = $this->options['id'];
    }
    /**
     * Runs the widget.
     */
    public function run()
    {
    	$replace['{input}'] = $this->hasModel() 
    		? Html::activeTextInput($this->model, $this->attribute, $this->options) 
    		: Html::textInput($this->name, $this->value, $this->options);
        $replace['{button}'] = Html::tag($this->buttonTag, $this->buttonName, $this->buttonOptions);
        $replace['{reset-button}'] = Html::tag($this->resetButtonTag, $this->resetButtonName, $this->resetButtonOptions);

        MediaManagerAsset::register($this->view);
        
        $modal = $this->renderFile('@vendor/dkemens/yii2-aws-s3-manager/views/default/index.php', [
            'inputId' => $this->options['id'],
            'btnId' => $this->buttonOptions['id'],
            'frameId' => $this->options['id'] . '-frame',
            'pasteData' => $this->pasteData,
            'bucketObject' => $this->s3->bucketObject,
            'folderObject' => $this->s3->folderObject,
        ]);

        return strtr($this->template, $replace) . $modal;
    }
}