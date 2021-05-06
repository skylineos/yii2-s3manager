<?php

use yii\web\View;
use skylineos\yii\s3manager\assets\MediaManagerAsset;

MediaManagerAsset::register($this);

?>
<div class="m-portlet m-portlet--bordered m-portlet--unair">
    <div class="m-portlet__head">
        <div class="m-portlet__head-caption">
            <div class="m-portlet__head-title">
                <h3 class="m-portlet__head-text">
                    Media Manager
                </h3>
            </div>
        </div>
    </div>
    <div class="m-portlet__body">
    	Upload files to <code><?= $uploadPath ?></code>
    </div>
</div>