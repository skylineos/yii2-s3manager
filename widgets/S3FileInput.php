<?php

namespace skyline\yii\s3manager\widgets;

use yii;
use yii\helpers\Html;
use yii\widgets\InputWidget as YiiInputWidget;

/**
 * TODO: document...
 */

class S3FileInput extends YiiInputWidget
{
    /**
     * @var string whether input is to be disabled
     */
    public $label = null;

    /**
     * @var array options for the label tag
     */
    public $labelOptions = ['class' => 'control-label'];

    /**
     * @var boolean whether input is to be disabled
     */
    public $disabled = false;

    /**
     * @var boolean whether input is to be readonly
     */
    public $readonly = false;

    /**
     * @var boolean whether or not to include a div wrapper (ie. bootstrap's form-group)
     */
    public $wrapper = true;

    /**
     * @var string the type of tag to wrap around the input
     */
    public $wrapperTag = 'div';

    /**
     * @var array html options for the wrapper div
     */
    public $wrapperOptions = ['class' => 'form-group'];

    /**
     * @var string 'append' or 'prepend' - whether to put the button first or second
     */
    public $inputGroup = 'append';

    /**
     * @var whatever the input-group-prepend content should be
     */
    public $inputGroupContent = 'Browse';

    /**
     * @inheritdoc
     */
    public $options = ['class' => 'form-control', 'placeholder' => 'Select a file'];

    /**
     * @var array initialize the FileInput widget
     */
    public function init()
    {
        parent::init();

        $input = $this->buildInput();

        echo $input;
    }

    /**
     * Generates an input.
     *
     * @return string the rendered input markup
     */
    protected function buildInput(): string
    {
        $input = '';

        if ($this->wrapper === true) {
            $input .= $this->startWrapper();
        }

        if ($this->label !== null) {
            $input .= $this->getLabel();
        }

        // Start input group
        $input .= '<div class="input-group">';

        if ($this->inputGroup === 'prepend') {
            $input .= $this->buildInputGroup();
        }


        if ($this->hasModel()) {
            $input .= Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            $input .= Html::textInput($this->name, $this->value, $this->options);
        }


        if ($this->inputGroup === 'append') {
            $input .= $this->buildInputGroup();
        }

        // End input group
        $input .= '</div>';

        if ($this->wrapper === true) {
            $input .= $this->endWrapper();
        }

        return $input;
    }

    /**
     * Generates the beginning of a wrapper (if $this->wrapper === true)
     * Ie <div class="form-control"> from bootstrap
     *
     * @return string the beginning of the wrapper tag
     */
    protected function startWrapper(): string
    {
        $startWrapper = '<' . $this->wrapperTag;

        foreach ($this->wrapperOptions as $key => $value) {
            $startWrapper .= ' ' . $key . '="' . $value . '"';
        }

        $startWrapper .= ">\n";

        return $startWrapper;
    }

    /**
     * Generates the end of a wrapper (if $this->wrapper === true)
     * Ie <div class="form-control"> </div> from bootstrap
     *
     * @return string the end of the wrapper tag
     */
    protected function endWrapper(): string
    {
        return '</' . $this->wrapperTag . '>';
    }

    /**
     * Build the label (if $this->label === true )
     *
     * @return string the label
     */
    protected function getLabel(): string
    {
        $label = "\t<label";

        foreach ($this->labelOptions as $key => $value) {
            $label .= ' ' . $key . '="' . $value . '"';
        }

        $label .= ">" . $this->label . "</label>\n";

        return $label;
    }

    /**
     * Build the input group prepend/append tags/data
     *
     * @return string the input-group
     */
    protected function buildInputGroup(): string
    {
        $group = '<div class="input-group-' . $this->inputGroup . '">';
        $group .= '<button href="#MediaManager" class="btn btn-secondary" type="button" data-toggle="modal">';
        $group .= $this->inputGroupContent;
        $group .= '</button>';
        $group .= '</div>';

        return $group;
    }

    protected function buildModal(): string
    {
        MediaManagerAsset::register($this->view);

        $modal = $this->renderFile('@vendor/skyline/s3manager/views/default/index.php', []);
    }
}
