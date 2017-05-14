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
            1 => Yii::t('app', 'The uploaded file exceeds the upload_max_filesize directive in php.ini'),
            2 => Yii::t('app', 'Upload html max size'),
            3 => Yii::t('app', 'The uploaded file was only partially uploaded'),
            4 => Yii::t('app', 'Upload is empty'),
            6 => Yii::t('app', 'Missing a temporary folder'),
            7 => Yii::t('app', 'Failed to write file to disk'),
            8 => Yii::t('app', 'A php extension stopped the file upload'),
            'post_max_size' => Yii::t('app', 'The uploaded file exceeds the post_max_size directive in php.ini'),
            'max_file_size' => Yii::t('app', 'File is too big'),
            'min_file_size' => Yii::t('app', 'File is too small'),
            'accept_file_types' => Yii::t('app', 'Filetype not allowed'),
            'max_number_of_files' => Yii::t('app', 'Upload max number of files'),
            'max_width' => Yii::t('app', 'Image exceeds maximum width'),
            'min_width' => Yii::t('app', 'Image requires a minimum width'),
            'max_height' => Yii::t('app', 'Image exceeds maximum height'),
            'min_height' => Yii::t('app', 'Image requires a minimum height'),
            'abort' => Yii::t('app', 'File upload aborted'),
            'image_resize' => Yii::t('app', 'Failed to resize image')
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