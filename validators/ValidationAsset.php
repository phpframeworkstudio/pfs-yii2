<?php

namespace pfs\yii\validators;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files for client validation.
 */
class ValidationAsset extends AssetBundle
{
    public $sourcePath = '@vendor/phpframeworkstudio/yii/assets';
    public $js = [
        'js/pfs.yii.validation.js',
    ];
    public $depends = [
        'yii\validators\ValidationAsset',
    ];
}
