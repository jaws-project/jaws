<?php
/*
 * StaticEntry.php - StaticEntry Class, static text in forms
 *
 * @version  $Id: $
 * @author   Helgi �rmar �rbj�nsson <dufuz@php.net>
 *
 * <c> Helgi �rmar �rbj�nsson 2005
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Bin/Bin.php';

define('STATICENTRY_REQ_PARAMS', 1);
class StaticEntry extends Bin
{
    function __construct($value, $title = '')
    {
        $this->_value = $value;
        $this->_title = $title;

        parent::init();
    }

    function buildXHTML()
    {
        $this->_XHTML = $this->getValue();
    }
}