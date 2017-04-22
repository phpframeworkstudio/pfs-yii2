<?php

namespace pfs\yii\widgets;

use Yii;
use yii\bootstrap\Alert;

class FlashMessage extends \yii\base\Widget
{
    const FLASH_SUCCESS = 'success';
    const FLASH_INFO = 'info';
    const FLASH_DANGER = 'danger';
    const FLASH_WARNING = 'warning';

    protected $types = [
        'success',
        'info',
        'danger',
        'warning'
    ];

    public function run()
    {
        $result = [];
        $flashes = Yii::$app->session->getAllFlashes();
        foreach ($flashes as $type => $data) {
            if (in_array(trim(strtolower($type)), $this->types) !== false) {
                if (is_array($data) && isset($data['body'])) {
                    $result[] = Alert::widget($data);
                } else {
                    $result[] = Alert::widget([
                        'options' => [
                            'class' => 'alert-'. $type
                        ],
                        'body' => $data
                    ]);
                }

                if (Yii::$app->request->isAjax) {
                    Yii::$app->session->removeFlash($type);
                }
            }
        }

        return implode("\n", $result);
    }
}