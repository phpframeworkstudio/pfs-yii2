<?php

namespace pfs\yii\components;

use Yii;
use yii\base\ErrorException;
use yii\base\Object;
use yii\helpers\ArrayHelper;

class Config extends Object
{
    /**
     * @var array The config params
     */
    public $params = [];

    /**
     * @var array The loading lists file
     */
    public $load = [];

    /**
     * @var array Language lists
     */
    public $languages = [];

    /**
     * @var array Path lists
     */
    public $pathes = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        foreach ($this->load as $name => $file) {
            $path = Yii::getAlias($file);
            if (file_exists($path)) {
                $this->pathes[$name] = $path;
            }
        }
    }

    /**
     * Get config value
     * @param string $value
     * @return mixed
     */
    public function getValue($value)
    {
        if (is_array($value) && count($value)) {
            $name = $value[0];
        } else if (is_string($value)) {
            $name = explode('.', $value)[0];
        } else {
            throw new ErrorException('The data format is entered incorrectly');
        }

        if (!isset($this->params[$name]) && isset($this->pathes[$name])) {
            $this->params[$name] = require($this->pathes[$name]);
        }

        return ArrayHelper::getValue($this->params, $value);
    }
}