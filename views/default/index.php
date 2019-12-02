<?php

use yii\helpers\Html;
use dkemens\s3mediamanager\assets\MediaManagerAsset;

MediaManagerAsset::register($this);

?>
<div class="m-portlet m-portlet--bordered m-portlet--unair" id="mm__wrapper">
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
        <div class="row">
            <div class="col-md-2">
                <p class="lead">Folders</p>
                <hr>                         
                <div id="folderTree"></div>
            
                <hr>
                
            </div>                    
            <div class="col">
                <span class="pull-left"><i class="fas fa-folder-open"></i> <span id="s3mm-object-path-display" class="text-info">/</span></span>
                <span class="text-info pull-right">&nbsp;&nbsp;<i class="fas fa-copy invisible" id="s3mm-copy-file-uri"></i></span>
                <span id="s3mm-file-url-display" class="pull-right"></span> 
                <div class="clearfix"></div>
                <hr>

                <?= Html::beginForm(['/s3mediamanager/default/upload'], 'post', ['enctype' => 'multipart/form-data', 'id' => 's3mm-file-upload-form']) ?>
                
                <div class="dz-message btn btn-info" data-dz-message><span><i class="fas fa-cloud-upload-alt"></i> Click or drag files here to upload (Max Filesize: <?= ini_get('upload_max_filesize') ?>)</span></div>
                <?= html::hiddenInput('s3mm-upload-path', '/', ['id' => 's3mm-upload-path']) ?>

                <?= Html::endForm() ?>

                <div id="s3mm-file-details" class="d-none">
                    <p class="lead">File Details</p>
                    <hr>
                </div>

                <table class="table table-striped table-hover" id="s3mm-object-list">
                    <thead>
                        <tr>
                            <th class="column_actions"></th>
                            <th class="column_fileName">File name</th>
                            <th class="column_lastModified">Last modified</th>
                            <th class="column_fileSize text-right">File size</th>
                        </tr>
                    </thead>
                    <tbody id="files">

                    </tbody>
                </table>

            </div>
        </div>  
    </div>
</div>
