<?php

namespace pfs\yii\web;

use Yii;
use yii\base\Component;
use yii\base\ErrorException;
use yii\helpers\FileHelper;

require_once __DIR__ .'/../third_party/ImageLib.php';

class FileUploadManager extends Component
{
    public $temporary = 'uploads/temp/';
    public $maxFileIncrementName = 100;
    public $encryptName = false;
    public $overwrite = false;

    public function init()
    {
        parent::init();
        if (!Yii::$app->session->isActive) {
            Yii::$app->session->open();
        }
    }

    /**
     * Create path to temporary path
     *
     * @controllerId string ID of Controller
     * @fieldName string Field name
     * @index int Indexing files for multiple upload
     */
    public function getTemporaryPath($controllerId, $fieldName, $index = 1)
    {
        $name = $controllerId .'_'. $fieldName .'_'. $index;
        $name = static::encode(strtolower($name));
        $directory = static::join(Yii::getAlias('@webroot'), $this->temporary);

        if (is_dir($directory)) {
            foreach (scandir($directory) as $key => $value) {
                if (strpos($value, 'temp__') !== false) {
                    $lastModifiedTime = self::getDirectoryLastModified(static::join($directory, $value));

                    if (round(abs(time() - $lastModifiedTime) / 60 / 60) >= 24) {
                        FileHelper::removeDirectory(static::join($directory, $value), [
                            'traverseSymlinks' => true
                        ]);
                        try {
                            $sesname = preg_replace('/(.*)temp__(.*?)__ud(.*)/', '$2', $value);
                            Yii::$app->session->remove(trim($sesname));
                        } catch (\Exception $e) {

                        }
                    }
                }
            }
        }

        $encodePath = 'temp__'. $name .'__ud'. uniqid() . md5(time() . $name);
        $path = static::join($this->temporary, $encodePath, $controllerId, $fieldName);

        if (Yii::$app->session->has($name)) {
            $session = Yii::$app->session->get($name);
            if (isset($session['path']) && isset($session['timeout'])) {
                if ($session['timeout'] > time()) {
                    $path = $session['path'];
                }
            }
        }

        Yii::$app->session->remove($name);
        Yii::$app->session->set($name, [
            'timeout' => time() + (24 * 60 * 60),
            'path' => $path
        ]);

        return $path;
    }

    public function releaseFiles($id, $field, $index = 1, $value = null, $old = null, $isNewRecord = true)
    {
        if (($fieldOptions = Yii::$app->config->getValue($id.'.columns.'. $field)) !== null) {
            
            $temp = $this->getTemporaryPath($id, $field, $index);
            $multiple = $fieldOptions['multiple'];
            $separator = $fieldOptions['multiple-separator'];
            if (empty($separator)) {
                $separator = ',';
            }
            $length = $fieldOptions['length'];
            $destination = static::join(Yii::getAlias('@webroot'), $fieldOptions['upload-folder']);
            $source = static::join(Yii::getAlias('@webroot'), $temp);
            $tempName = preg_replace('/(.*)temp__(.*?)\/(.*)/', '$1temp__$2', $temp);

            if (empty($value) || trim($value) == '') {
                return;
            }

            if (!is_dir($source)) {
                return;
            }

            if (!is_dir($destination)) {
                FileHelper::createDirectory($destination, 0755, true);
            }

            if (!$multiple) {
                $sourceFile = static::join($source, $value);

                if (!file_exists($sourceFile)) {
                    return;
                }

                if ($fieldOptions['image-resize'] && 
                    getimagesize($sourceFile) && 
                    is_numeric($fieldOptions['image-resize-width']) && 
                    is_numeric($fieldOptions['image-resize-height']) &&
                    $fieldOptions['image-resize-width'] &&
                    $fieldOptions['image-resize-height']) {
                    static::resizeImage($sourceFile, $fieldOptions['image-resize-width'], $fieldOptions['image-resize-height']);
                }

                $properties = static::getFileProperties($sourceFile);

                if ($fieldOptions['is-blob']) {
                    $properties['content'] = @file_get_contents($sourceFile);
                } else {
                    
                    if ($value === $old && !$isNewRecord) {
                        // update only
                        $newName = $value;
                    } else {
                        $newName = $this->setFileName($destination, $value, $properties);
                    }

                    if ($fieldOptions['length'] >= strlen($newName) && copy($sourceFile, static::join($destination, $newName))) {
                        $properties['content'] = $newName;
                        $properties['file_name'] = $newName;
                        $properties['raw_name'] = rtrim(str_replace($properties['file_ext'], '', $properties['file_name']), '.');
                    } else {
                        $properties = false;
                    }
                }

                FileHelper::removeDirectory(static::join(Yii::getAlias('@webroot'), $tempName), [
                    'traverseSymlinks' => true
                ]);

                return $properties;
            } else {
                if ($fieldOptions['is-blob']) {
                    return;
                }

                $files = explode($separator, $value);
                $valueLength = 0;
                $names = [];
                $values = [];

                if (!$isNewRecord) {
                    $oldFiles = explode($separator, $old);
                }

                foreach ($files as $file) {
                    $sourceFile = static::join($source, $file);

                    // skip
                    if (!file_exists($sourceFile)) {
                        continue;
                    }

                    // resize
                    if ($fieldOptions['image-resize'] && 
                        getimagesize($sourceFile) && 
                        is_numeric($fieldOptions['image-resize-width']) && 
                        is_numeric($fieldOptions['image-resize-height']) &&
                        $fieldOptions['image-resize-width'] &&
                        $fieldOptions['image-resize-height']) {

                        static::resizeImage($sourceFile, $fieldOptions['image-resize-width'], $fieldOptions['image-resize-height']);
                    }

                    // file properties
                    $properties = static::getFileProperties($sourceFile);

                    // file name
                    if (!$isNewRecord && in_array($file, $oldFiles)) {
                        $newName = $file;
                    } else {
                        $newName = $this->setFileName($destination, $file, $properties);
                    }

                    // value length
                    $nameLength = strlen($newName);// check value length

                    if ($fieldOptions['length'] >= ($valueLength + $nameLength)) {
                        $valueLength = $valueLength + $nameLength;
                        // copy image
                        if (@copy($sourceFile, static::join($destination, $newName))) {
                            // set new properties
                            $properties['content'] = $newName;
                            $properties['file_name'] = $newName;
                            $properties['raw_name'] = rtrim(str_replace($properties['file_ext'], '', $properties['file_name']), '.');
                            array_push($values, $properties);
                            array_push($names, $newName);
                        } else {
                            // rollback length
                            $valueLength = $valueLength - $nameLength;
                        }                    
                    } else {
                        // stop
                        break;
                    }
                }

                FileHelper::removeDirectory(static::join(Yii::getAlias('@webroot'), $tempName), [
                    'traverseSymlinks' => true
                ]);

                if (!count($values)) {
                    return;
                }

                return [
                    'content' => implode($separator, $names),
                    'properties' => $values
                ];
            }
        }

        return;
    }

    public function deleteFiles($id, $field, $index = 1, $value = null)
    {
        if (($fieldOptions = Yii::$app->config->getValue($id.'.columns.'. $field)) !== null) {

            $separator = $fieldOptions['multiple-separator'];
            if (empty($separator)) {
                $separator = ',';
            }
            $source = static::join(Yii::getAlias('@webroot'), $fieldOptions['upload-folder']);


            if (!is_dir($source)) {
                return;
            }

            if ($fieldOptions['is-blob']) {
                return;
            }

            if ($fieldOptions['multiple']) {
                if (!empty($value) && trim($value) !== '') {
                    $files = explode($separator, $value);
                    foreach ($files as $key => $file) {
                        $sourceFile = static::join($source, $file);
                        if (file_exists($sourceFile)) {
                            @unlink($sourceFile);
                        }
                    }
                }
            } else {
                $sourceFile = static::join($source, $value);
                if (file_exists($sourceFile)) {
                    @unlink($sourceFile);
                }
            }
        }
    }

    public function editFiles($id, $field, $index = 1, $value = null, $mime = 'image/jpg')
    {
        if (($fieldOptions = Yii::$app->config->getValue($id.'.columns.'. $field)) !== null) {

            $temp = $this->getTemporaryPath($id, $field, $index);
            $separator = $fieldOptions['multiple-separator'];
            if (empty($separator)) {
                $separator = ',';
            }

            $source = static::join(Yii::getAlias('@webroot'), $fieldOptions['upload-folder']);
            $destination = static::join(Yii::getAlias('@webroot'), $temp);
            $destinationThumb = static::join($destination, 'thumbnail');
            $tmpName = preg_replace('/(.*)temp__(.*?)\/(.*)/', '$1temp__$2', $temp);

            // clear
            FileHelper::removeDirectory($destination, [
                'traverseSymlinks' => true
            ]);

            if (!is_dir($destinationThumb)) {
                try {
                    FileHelper::createDirectory($destinationThumb, 0755, true);
                } catch (\Exception $e) {
                    throw new ErrorException(
                        $e->getMessage().". Failed to create directory \"{$destinationThumb}\"",
                        $e->getCode()
                    );
                }
            }

            if (!empty($value) && trim($value) != '' && $value !== null) {
                if ($fieldOptions['is-blob']) {
                    // blob
                    $name = static::createImageFromBlob($value, $destination, strtolower(static::encode(uniqid() . time())), $mime);
                    if (@copy(static::join($destination, $name), static::join($destinationThumb, $name))) {
                        static::resizeImage(static::join($destinationThumb, $name));
                    }
                } else if (!empty($value)) {
                    $files = explode($separator, $value);
                    foreach ($files as $key => $file) {
                        if (!is_dir(static::join($source, $file)) && file_exists(static::join($source, $file))) {
                            @copy(static::join($source, $file), static::join($destination, $file));
                            if (@copy(static::join($source, $file), static::join($destinationThumb, $file))) {
                                static::resizeImage(static::join($destinationThumb, $file));
                            }
                        }
                    }
                }
            }
        }

        return;
    }

    protected static function createImageFromBlob($src, $dst, $name, $mime = 'image/jpg')
    {
        $dst = rtrim($dst, '/');
        $types = array(
            'gif' => 'imagegif',
            'jpeg' => 'imagejpeg',
            'jpg' => 'imagejpeg',
            'png' => 'imagepng',
            'bmp' => 'imagejpeg'
        );

        $mimeEx = explode('/', $mime);
        if (count($mimeEx) > 1 && isset($types[$mimeEx[1]])) {
            $type = trim($mimeEx[1]);
            $display = $types[$type];

            $img = imagecreatefromstring($src);
            if ($type == 'png') {
                imagealphablending($img, false);
                imagesavealpha($img, true);
            }
            $display($img, $dst . DIRECTORY_SEPARATOR . $name .'.'. $type, 100);
            imagedestroy($img);

            return $name .'.'. $type;
        }

        return;

    }

    protected static function encode($str)
    {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }

    protected static function decode($str)
    {
        return base64_decode(str_pad(strtr($str, '-_', '+/'), strlen($str) % 4, '=', STR_PAD_RIGHT));
    }

    protected static function getDirectoryLastModified($path)
    {
        $lastModifiedTime = 0;

        foreach (scandir($path) as $key => $value) {
            if ($value == '.' || $value == '..') {
                continue;
            }

            if (is_file(static::join($path, $value))) {
                $filemtime = filemtime(static::join($path, $value));
                if ($filemtime > $lastModifiedTime) {
                    $lastModifiedTime = $filemtime;
                }
            } elseif (is_dir(static::join($path, $value))) {
                $filemtime = static::getDirectoryLastModified(static::join($path, $value));
                if ($filemtime > $lastModifiedTime) {
                    $lastModifiedTime = $filemtime;
                }
            }
        }

        return $lastModifiedTime;
    }

    protected static function resizeImage($path, $width = 110, $height = 220, $maintainRatio = true)
    {
        if (!is_bool($maintainRatio)) {
            $maintainRatio = true;
        }

        $imageLib = new \CI_Image_lib;
        $imageLib->initialize([
            'source_image' => $path,
            'width' => $width,
            'height' => $height,
            'maintain_ratio' => $maintainRatio
        ]);

        if (!$imageLib->resize()) {
            return false;
        }

        return true;
    }

    public static function join()
    {
        $startWithDirectorySeparator = false;

        $arr = [];
        foreach (func_get_args() as $key => $value) {
            if ($key === 0) {
                if (strpos($value, '/') === 0) {
                    $startWithDirectorySeparator = true;
                }
            }

            if (preg_match('/\.(.*)$/i', $value)) {
                $arr[] = ltrim($value, '/');
            } else {
                $arr[] = rtrim(ltrim($value, '/'), '/');
            }
        }

        $path = implode('/', $arr);
        if (!preg_match('/\.(.*)$/i', $value)) {
            $path .= '/';
        }

        if ($startWithDirectorySeparator) {
            $path = '/' . $path;
        }

        return $path;
    }

    protected static function getFileProperties($file)
    {
        $phpinfo = pathinfo($file);
        $mime = mime_content_type($file);
        $size = floor(filesize($file) / 1024);
        $image = getimagesize($file);

        $data = array();
        $data['file_name'] = $phpinfo['basename'];
        $data['raw_name'] = $phpinfo['filename'];
        $data['file_ext'] = $phpinfo['extension'];
        $data['file_type'] = $mime;
        $data['file_size'] = $size;
        if ($image) {
            $data['is_image'] = true;
            $data['image_width'] = $image[0];
            $data['image_height'] = $image[1];
        } else {
            $data['is_image'] = false;
            $data['image_width'] = 0;
            $data['image_height'] = 0;
        }

        return $data;
    }

    public function resizeBlobImage($src, $width = 200, $height = false, $mime = 'image/jpg', $scale = true)
    {
        $srcImg = imagecreatefromstring($src);
        $oriWidth = imagesx($srcImg);
        $oriHeight = imagesy($srcImg);

        // Scale
        $xScale = $oriWidth / $oriHeight;
        $yScale = $oriHeight / $oriWidth;

        // New size
        if ($width > $oriWidth) {
            $width = $oriWidth;
        }

        if ($height > $oriHeight) {
            $height = $oriHeight;
        }

        // Scale
        if ($scale) {
            if ($width) {
                $newWidth = $width;
                $newHeight = $width * $yScale;
            } elseif ($height) {
                $newWidth = $height * $xScale;
                $newHeight = $height;
            } else {
                $newWidth = $oriWidth;
                $newHeight = $oriHeight;
            }
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        $types = array(
            'gif' => 'imagegif',
            'jpeg' => 'imagejpeg',
            'jpg' => 'imagejpeg',
            'png' => 'imagepng'
        );

        // gd
        $dstImg = imagecreatetruecolor($newWidth, $newHeight);

        // type
        $mimeEx = explode('/', $mime);
        $type = trim($mimeEx[1]);
        $display = $types[$type];

        if ($type == 'png') {
            imagealphablending($dstImg, false);
            imagesavealpha($dstImg, true);
        }

        // copy
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $oriWidth, $oriHeight);

        // buffer
        ob_start();
        $display($dstImg, null, 100);
        imagedestroy($dstImg);
        imagedestroy($srcImg);
        $image = ob_get_contents();
        ob_end_clean();
        
        return $image;
    }

    protected function setFileName($path, $file, $properties = array())
    {
        if ($this->encryptName) {
            $fileName = md5(uniqid(mt_rand())) .'.'. $properties['file_ext'];
        }

        if ($this->overwrite || !file_exists(static::join($path, $file))) {
            return $file;
        }

        $fileName = $properties['raw_name'];
        $newFileName = '';
        for($i = 1; $i < $this->maxFileIncrementName; $i++) {
            if (!file_exists(static::join($path, $fileName .' ('.$i.').'. $properties['file_ext']))) {
                $newFileName = $fileName .' ('.$i.').'. $properties['file_ext'];
                break;
            }
        }

        return $newFileName;
    }
}