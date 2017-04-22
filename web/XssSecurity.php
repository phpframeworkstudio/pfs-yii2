<?php

namespace pfs\yii\web;

require_once __DIR__ .'/../third_party/CI_Security.php';

class XssSecurity extends \yii\base\Component
{
    protected $__ci_security;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->__ci_security === null) {
            $this->__ci_security = new \CI_Security;
        }
    }

    /**
     * XSS Clean
     *
     * Sanitizes data so that Cross Site Scripting Hacks can be
     * prevented.  This method does a fair amount of work but
     * it is extremely thorough, designed to prevent even the
     * most obscure XSS attempts.  Nothing is ever 100% foolproof,
     * of course, but I haven't been able to get anything passed
     * the filter.
     *
     * Note: Should only be used to deal with data upon submission.
     *   It's not something that should be used for general
     *   runtime processing.
     *
     * @link http://channel.bitflux.ch/wiki/XSS_Prevention
     *      Based in part on some code and ideas from Bitflux.
     *
     * @link http://ha.ckers.org/xss.html
     *      To help develop this script I used this great list of
     *      vulnerabilities along with a few other hacks I've
     *      harvested from examining vulnerabilities in other programs.
     *
     * @param string|string[] $string Input data
     * @param bool $isImage Whether the input is an image
     * @return string
     */
    public function clean($string, $isImage = false)
    {
        return $this->__ci_security->xss_clean($string, $isImage);
    }
}