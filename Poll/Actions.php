<?php
/**
 * Poll Actions file
 *
 * @category    GadgetActions
 * @package     Poll
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Poll'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Poll',
    'parametric' => true,
);
$actions['Polls'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Polls',
    'parametric' => true,
);
$actions['ViewResult'] = array(
    'normal' => true,
);
$actions['Vote'] = array(
    'normal' => true,
);
