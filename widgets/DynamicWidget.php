<?php

namespace pfs\yii\widgets;

use Yii;
use yii\base\Widget;

class DynamicWidget extends Widget
{

    /**
     * @inheritdoc
     */
    public static function widget($config = [])
    {
        ob_start();
        ob_implicit_flush(false);
        try {
            $widget = Yii::createObject($config);
            $out = $widget->run();
        } catch (\Exception $e) {
            // close the output buffer opened above if it has not been closed already
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $e;
        }

        return ob_get_clean() . $out;
    }
}