<?php

namespace pfs\yii\widgets;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files for fileupload.
 */
class FileUploadAsset extends AssetBundle
{
    public $sourcePath = '@vendor/phpframeworkstudio/yii/assets';
    public $css = [
        'jquery-fileupload/css/jquery.fileupload.css',
        'jquery-fileupload/css/jquery.fileupload-ui.css',
        'jquery-fileupload/css/jquery.fileupload-ui-noscript.css'
    ];
    public $js = [
        'jquery-fileupload/vendor/jquery.ui.widget.js',
        'jquery-fileupload/vendor/canvas-to-blob.min.js',
        'jquery-fileupload/vendor/load-image.all.min.js',
        'jquery-fileupload/js/jquery.fileupload.js',
        'jquery-fileupload/js/jquery.fileupload-ui.js',
        // 'jquery-fileupload/js/jquery.fileupload-audio.js',
        // 'jquery-fileupload/js/jquery.fileupload-image.js',
        'jquery-fileupload/js/jquery.fileupload-process.js',
        'jquery-fileupload/js/jquery.fileupload-validate.js',
        'jquery-fileupload/js/jquery.fileupload-video.js',
        'jquery-fileupload/js/jquery.iframe-transport.js',
        'js/pfs.fileupload.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'pfs\yii\web\AppAsset'
    ];
}
