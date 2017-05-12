<?php

namespace pfs\yii\widgets;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files for fileupload.
 */
class AjaxInputAsset extends AssetBundle
{
    public $sourcePath = '@vendor/phpframeworkstudio/yii/assets';
    public $css = [
        'css/pfs.ajax.input.css'
    ];
    public $js = [
        'js/pfs.ajax.input.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'pfs\yii\web\AppAsset'
    ];
}
