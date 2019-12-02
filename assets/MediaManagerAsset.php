<?php

namespace dkemens\s3mediamanager\assets;

use yii\web\AssetBundle;

class MediaManagerAsset extends AssetBundle
{

	public $sourcePath = '@vendor/dkemens/yii2-aws-s3-manager/assets';

	public $css = [
		'css/basic.css',
		'css/dropzone.css',
		'css/yii2AwsS3Manager.css',
        '//use.fontawesome.com/releases/v5.3.0/css/all.css',
        '//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.8/themes/default/style.min.css',
	];

	public $js = [
		'js/dropzone.js',
		'js/yii2AwsS3Manager.js',
		'//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.8/jstree.min.js',
		'js/jquery.blockui.js',
	];

	public $depends = [
		'yii\bootstrap\BootstrapAsset',
		'yii\web\JqueryAsset',
	];
}