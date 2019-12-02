<p align="center">
    <h1 align="center">A Yii 2 extension for managing files in AWS S3 buckets</h1>
    <br>
</p>

This was heavily based on Pendalf89's implementation (https://github.com/PendalF89/yii2-filemanager). Thank you Pendalf! I just needed more flexibility in a few areas - but that package was the basis of most of this.

This extension provides a very customizable method for managing files in [AWS S3 buckets](https://aws.amazon.com/s3/?nc2=h_m1) for the [Yii framework 2.0](http://www.yiiframework.com).
It can function on it's own, as a callback for a form field, or integrated with TinyMCE.

For license information check the [LICENSE](LICENSE.md)-file.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist dkemens/yii2-aws-s3-manager
```

or add

```json
"dkemens/yii2-aws-s3-manager": "*"
```

to the require section of your composer.json.

Configuration
-------------

To use this extension, you should add the module to your web configuration. Configuration of the module itself can be done here or on the fly.

```php
return [
    //....
    'modules' => [
        's3mediamanager' => [
            'class' => 'dkemens\yii2-aws-s3-manager\Module',
            // All settings can be configured on the fly regardless of usage type (fileinput, standalone manager, tinymce plugin)
            'configuration' => [ 
                'bucket' => 'your-bucket-name', // can be overriden with \Yii::$app->params['s3bucket']
                'version' => 'latest',
                'region' => 'your-bucket-region', // can be overriden with \Yii::$app->params['s3region']
                'scheme' => 'http',
            ],            
        ],
    ]
];
```

Be certain to check the widgets folder for exposed parameters. 

Use
---

### Standalone

Simply navigate to /s3mediamanager

### With a file input (active form)

In your form, add the following (ie. views/post/form.php)

`use dkemens\s3mediamanager\widgets\{FileInput, MediaManagerModal};`

Wherever you want your form field:

```php
<label>My Field</label>
<?= FileInput::widget(['model' => $model, 'attribute' => 'myField']) ?>
```

Then, at the bottom of the page (after your `<?php ActiveForm::end(); ?>`)
`<?= MediaManagerModal::widget(['s3region' => 'us-east1', 's3bucket' => 'my-bucket-name']) ?>`

### With TinyMCE

On your form.php

`use dkemens\s3mediamanager\widgets\{TinyMce, MediaManagerModal};`

Wherever you want your TinyMCE (client options are largely up to you): 

```php
<?= $form->field($model, 'content')->widget(TinyMce::className(), [
    'options' => ['rows' => 15],
    'clientOptions' => [
        'plugins' => [
            "advlist autolink lists link charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu paste image"
        ],
        'menubar' => 'edit insert view format table tools help',
        'toolbar' => "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
    ]
]);?>
```

Then, at the bottom of the page (after your `<?php ActiveForm::end(); ?>`)
`<?= MediaManagerModal::widget(['s3region' => 'us-east1', 's3bucket' => 'my-bucket-name']) ?>`