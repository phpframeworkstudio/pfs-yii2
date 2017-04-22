<?php

namespace pfs\yii\actions;

use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class FileUploadAction extends Action
{
    public $modelParam = 'fum';
    public $fieldParam = 'fuf';
    public $indexParam = 'fui';
    public $model;
    public $thumbWidth = 110;
    public $thumbHeight = 220;
    public $mkdirMode = 0755;
    public $downloadViaPhp = false;

    public function init()
    {
        parent::init();
        if ($this->model === null) {
            throw new InvalidConfigException('The "model" property failed.');
        }
    }


    public function run()
    {
        $model = Yii::$app->request->post($this->modelParam, Yii::$app->request->get($this->modelParam));
        $field = Yii::$app->request->post($this->fieldParam, Yii::$app->request->get($this->fieldParam));
        $index = Yii::$app->request->post($this->indexParam, Yii::$app->request->get($this->indexParam));

        if ($model && $model === $this->model->formName() && $field 
            && $index && ($options = Yii::$app->config->getValue($this->controller->id .'.columns.'. $field)) !== null) {

            $path = Yii::$app->fileUploadManager->getTemporaryPath($this->controller->id, $field, $index);
            if (!empty($path)) {
                new \pfs\yii\web\FileUploadHandler([
                    'script_url' => Yii::$app->urlManager->createUrl([
                        '/'. $this->controller->id .'/do-upload',
                        $this->modelParam => $this->model->formName(),
                        $this->fieldParam => $field,
                        $this->indexParam => $index
                    ]),
                    'upload_dir' => Yii::getAlias('@webroot'. DIRECTORY_SEPARATOR . $path),
                    'upload_url' => Yii::getAlias('@web/'. $path),
                    'param_name' => str_replace('-', '_', Html::getInputId($this->model, $field)),
                    'mkdir_mode' => $this->mkdirMode,
                    'download_via_php' => $this->downloadViaPhp,
                    'delete_type' => 'POST',
                    'accept_file_types' => '/\.('. $options['file-allowed-types'] .')$/i',
                    'max_number_of_files' => isset($options['multiple']) ? $options['file-max-count'] : null,
                    'max_file_size' => (isset($options['file-max-size']) && $options['file-max-size'] > 0)
                        ? $options['file-max-size'] * 1024 
                        : null,
                    'min_file_size' => (isset($options['file-min-size']) && $options['file-min-size'] > 1)
                        ? $options['file-min-size'] * 1024 
                        : 1,
                    'max_width' => ArrayHelper::getValue($options, 'image-max-width', null),
                    'min_width' => ArrayHelper::getValue($options, 'image-min-width', 1),
                    'max_height' => ArrayHelper::getValue($options, 'image-max-height', null),
                    'min_height' => ArrayHelper::getValue($options, 'image-min-height', 1),
                    'image_library' => 1,
                    'image_versions' => [
                        '' => [
                            'auto_orient' => true
                        ],
                        'thumbnail' => [
                            'max_width' => $this->thumbWidth,
                            'max_height' => $this->thumbHeight
                        ]
                    ]
                ]);
            }
        }
    }
}