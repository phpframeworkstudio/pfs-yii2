<?php

namespace pfs\yii\widgets;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files for fileupload.
 */
class AdvancedSearchAsset extends AssetBundle
{
    public $sourcePath = '@vendor/phpframeworkstudio/yii/assets';
    public $js = [
        'js/pfs.advancedsearch.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
