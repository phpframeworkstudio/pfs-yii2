<?php

namespace pfs\yii\web;

use Yii;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Asset bundle for the PHP Framework Studio files.
 *
 * @author Dida Nurwanda <didanurwanda@gmail.com>
 */
class AppAsset extends AssetBundle
{
    public $sourcePath = '@vendor/phpframeworkstudio/yii/assets';
    public $css = [
        'css/bundle.css',
        'third_party/bootstrap-submenu/dist/css/bootstrap-submenu.css',
        'third_party/jquery-confirm/jquery-confirm.min.css',
    ];
    public $js = [
        'js/bundle.js',
        'third_party/bootstrap-submenu/dist/js/bootstrap-submenu.js',
        'third_party/jquery-confirm/jquery-confirm.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

    public function init()
    {
        parent::init();
        Yii::$app->controller->view->registerJs('var AdminLTEOptions = {
    enableBSToppltip: false
};', yii\web\View::POS_HEAD);

        Yii::$app->controller->view->registerJsFile(Yii::getAlias('@web/js/language-'. Yii::$app->language .'.js'), [
            'position' => View::POS_HEAD
        ]);
    }
}
