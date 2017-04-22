<?php

namespace pfs\yii\web;

use Yii;

require_once __DIR__ .'/../third_party/UploadHandler.php';

class FileUploadHandler extends \UploadHandler
{
    /**
     * @inheritdoc
     */
    public function __construct($options = null, $initialize = true, $error_messages = null) {
        parent::__construct($options, $initialize, $error_messages);
        $this->error_messages = [
            1 => Yii::t('app', 'UploadPhpMaxSize'),
            2 => Yii::t('app', 'UploadHtmlMaxSize'),
            3 => Yii::t('app', 'UploadPartially'),
            4 => Yii::t('app', 'UploadEmpty'),
            6 => Yii::t('app', 'UploadMissingTemporaryFolder'),
            7 => Yii::t('app', 'UploadWriteFailed'),
            8 => Yii::t('app', 'UploadPhpExtensionStopped'),
            'post_max_size' => Yii::t('app', 'UploadPostMaxSize'),
            'max_file_size' => Yii::t('app', 'UploadMaxSize'),
            'min_file_size' => Yii::t('app', 'UploadMinSize'),
            'accept_file_types' => Yii::t('app', 'UploadAcceptFileTypes'),
            'max_number_of_files' => Yii::t('app', 'UploadMaxNumberOfFiles'),
            'max_width' => Yii::t('app', 'UploadMaxWidth'),
            'min_width' => Yii::t('app', 'UploadMinWidth'),
            'max_height' => Yii::t('app', 'UploadMaxHeight'),
            'min_height' => Yii::t('app', 'UploadMinHeight'),
            'abort' => Yii::t('app', 'UploadAbord'),
            'image_resize' => Yii::t('app', 'UploadImageResize')
        ];
    }

    /**
     * @inheritdoc
     */
    protected function get_singular_param_name()
    {
        return $this->options['param_name'];
    }

    /**
     * @inheritdoc
     */
    protected function get_file_names_params() 
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function post($print_response = true)
    {
        $ar = parent::post(FALSE);
        if (array_key_exists($this->options["param_name"], $ar)) {
            $ar["files"] = $ar[$this->options["param_name"]];
            unset($ar[$this->options["param_name"]]);
        }
        return $this->generate_response($ar, $print_response);
    }

    /**
     * @inheritdoc
     */
    protected function get_unique_filename($file_path, $name, $size, $type, $error, $index, $content_range)
    {
        $name = htmlentities($name, ENT_COMPAT, "UTF-8");
        $name = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil);/', '$1', $name);
        $name = html_entity_decode($name, ENT_COMPAT, "UTF-8");

        return parent::get_unique_filename($file_path, $name, $size, $type, $error, $index, $content_range);
    }
}