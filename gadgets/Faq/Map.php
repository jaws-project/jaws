<?php
/**
 * Faq URL maps
 *
 * @category   GadgetMaps
 * @package    Faq
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array('View', 'faq');
$maps[] = array(
    'ViewQuestion',
    'faq/question/{id}',
    array('id' =>  '[\p{L}[:digit:]-_\.]+',)
);
$maps[] = array(
    'ViewCategory',
    'faq/category/{id}',
    array('id' =>  '[\p{L}[:digit:]-_\.]+',)
);
