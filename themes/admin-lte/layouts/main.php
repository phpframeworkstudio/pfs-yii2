<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */


if (Yii::$app->controller->action->id === 'login') { 
    /**
     * Do not use this code in your template. Remove it. 
     * Instead, use the code  $this->layout = '//main-login'; in your controller.
     */
    $this->render('main-login', [
        'content' => $content
    ]);
} else {
    if (class_exists('backend\assets\AppAsset')) {
        // Yii2 Advanced Theme
        backend\assets\AppAsset::register($this);
    } else {
        // Yii2 Basic Theme
        app\assets\AppAsset::register($this);
    }

    dmstr\web\AdminLteAsset::register($this);
    pfs\yii\web\AppAsset::register($this);
    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<?php $this->beginBody() ?>
<div class="wrapper">
    <?= $this->render('header', [
        'directoryAsset' => $directoryAsset
    ]) ?>
    
    <?= $this->render('sidebar', [
        'directoryAsset' => $directoryAsset
    ]) ?>

    <?= $this->render('content', [
        'content' => $content,
        'directoryAsset' => $directoryAsset
    ]) ?>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
<?php } ?>
