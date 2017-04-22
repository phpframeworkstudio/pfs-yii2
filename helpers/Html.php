<?php

namespace pfs\yii\helpers;

class Html extends \yii\helpers\Html
{

    /**
     * Create html 'css' icon.
     * @param string $class Class name.
     * @param string $tag Tag for icon, default `i`.
     * @return string Html icon.
     */
    public static function iconCls($class, $tag = 'i')
    {
        return self::tag($tag, '', ['class' => $class]);
    }

    /** 
     * Merges already existing attributes with new one.
     * This method provides the priority for attribute existing over additional.
     * @param array $existing alerady existing attribute.
     * @param array $additional attriute to be added.
     * @return array merge result.
     */
    public static function mergeAttribute(array $existing, array $additional)
    {
        self::reformatAttributeName($existing);

        foreach ($additional as $attribute => $value) {
            $attribute = strtolower(trim($attribute));

            // new attribute
            if (!array_key_exists($attribute, $existing)) {
                $existing[$attribute] = $value;
            
            // merge attribute value
            } else {

                // remove multiple spaces
                if (!is_array($value)) {
                    $value = preg_replace('/\s+/', ' ', $value);
                }

                // merge classes
                if ($attribute === 'class') {
                    self::addCssClass($existing, $value);
                
                // merge css
                } elseif ($attribute === 'style') {
                    $existingCss = self::cssStyleToArray($existing[$attribute]);
                    $additionalCss = self::cssStyleToArray($value);
                    $existing[$attribute] = self::cssStyleFromArray(array_merge($existingCss, $additionalCss));
                
                // replace other attribute
                } else {
                    $existing[$attribute] = $value;
                }
            }
        }

        return $existing;
    }

    /**
     * Replace multiple space for  value
     * @param array $attr the attributes
     */
    private static function reformatAttributeName(array &$attr)
    {
        foreach ($attr as $attribute => $value) {
            $attribute = strtolower(trim($attribute));
            if (!is_array($value)) {
                // remove multiple spaces
                $value = preg_replace('/\s+/', ' ', $value);
            }
        }
    }
}