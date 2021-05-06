<?php

namespace skylineos\yii\s3manager\widgets;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\InputWidget;

class FileInput extends InputWidget
{
    /**
     * @var string the label to be displayed
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
     * @var string widget template
     */
    public $template = '<div class="input-group">
        {input}
        <span class="input-group-btn">{button}{reset-button}</span>
    </div>';

    /**
     * @var string button tag
     */
    public $buttonTag = 'button';

    /**
     * @var string button name
     */
    public $buttonName = '<i class="fas fa-search"></i>';

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
    public $resetButtonName = '<i class="far fa-times-circle"></i>';

    /**
     * @var array reset button html options
     */
    public $resetButtonOptions = ['class' => 'btn btn-danger'];


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->buttonOptions['id'])) {
            $this->buttonOptions['id'] = $this->options['id'] . '-btn';
        }

        $this->buttonOptions['data-toggle'] = 'modal';
        $this->buttonOptions['href'] = '#MediaManager';
        $this->buttonOptions['type'] = 'button';
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
        $replace['{reset-button}'] = null;

        return strtr($this->template, $replace);
    }
}
