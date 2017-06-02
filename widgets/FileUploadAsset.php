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
        'third_party/jquery-fileupload/css/jquery.fileupload.css',
        'third_party/jquery-fileupload/css/jquery.fileupload-ui.css',
        'third_party/jquery-fileupload/css/jquery.fileupload-ui-noscript.css'
    ];
    public $js = [
        'third_party/jquery-fileupload/vendor/jquery.ui.widget.js',
        'third_party/jquery-fileupload/vendor/canvas-to-blob.min.js',
        'third_party/jquery-fileupload/vendor/load-image.all.min.js',
        'third_party/jquery-fileupload/js/jquery.fileupload.js',
        'third_party/jquery-fileupload/js/jquery.fileupload-ui.js',
        // 'third_party/jquery-fileupload/js/jquery.fileupload-audio.js',
        // 'third_party/jquery-fileupload/js/jquery.fileupload-image.js',
        'third_party/jquery-fileupload/js/jquery.fileupload-process.js',
        'third_party/jquery-fileupload/js/jquery.fileupload-validate.js',
        'third_party/jquery-fileupload/js/jquery.fileupload-video.js',
        'third_party/jquery-fileupload/js/jquery.iframe-transport.js',
        'js/pfs.fileupload.js'
    ];
    public $depends = [
        //'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
        //'yii\widgets\ActiveFormAsset',
        //'pfs\yii\web\AppAsset'
    ];
}
