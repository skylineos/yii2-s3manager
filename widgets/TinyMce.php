<?php

namespace skylineos\yii\s3manager\widgets;

use Yii;
use yii\widgets\InputWidget;
use yii\web\JsExpression;
use dosamigos\tinymce\TinyMce as TinyMceWidget;
use yii\helpers\Url;

class TinyMce extends InputWidget
{
    /**
     * @var string Optional, if set, only this image can be selected by user
     */
    public $thumb = '';

    /**
     * @var string JavaScript function, which will be called before insert file data to input.
     * Argument data contains file data.
     * data example: [alt: "Witch with cat", description: "123", url: "/uploads/2014/12/vedma-100x100.jpeg", id: "45"]
     */
    public $callbackBeforeInsert = '';

    /**
     * @var array the options for the TinyMCE JS plugin.
     * Please refer to the TinyMCE JS plugin Web page for possible options.
     * @see http://www.tinymce.com/wiki.php/Configuration
     */
    public $clientOptions = [];

    /**
     * @var string TinyMCE widget
     */
    private $tinyMCE = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->clientOptions['file_picker_callback'])) {
            $this->clientOptions['file_picker_callback'] = new JsExpression(
                'function(callback, value, meta) {
                    filemanagerTinyMCE(callback, value, meta);
                }'
            );
        }

        if (empty($this->clientOptions['document_base_url'])) {
            $this->clientOptions['document_base_url'] = '';
        }

        if (empty($this->clientOptions['convert_urls'])) {
            $this->clientOptions['convert_urls'] = false;
        }

        $this->tinyMCE = TinyMceWidget::widget([
            'name' => $this->name,
            'model' => $this->model,
            'attribute' => $this->attribute,
            'value' => $this->value,
            'clientOptions' => $this->clientOptions,
            'options' => $this->options,
        ]);
    }

    /**
     * Runs the widget.
     */
    public function run()
    {
        if (!empty($this->callbackBeforeInsert)) {
            $this->view->registerJs(
                '
                $("#' . $this->options['id'] . '").on("fileInsert", ' . $this->callbackBeforeInsert . ');'
            );
        }

        return $this->tinyMCE;
    }
}
