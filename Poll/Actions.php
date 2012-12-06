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
    'NormalAction:Poll,LayoutAction:Poll',
   true
);
$actions['Polls'] = array(
    'NormalAction:Polls,LayoutAction:Polls',
    true
);
$actions['ViewResult'] = array(
    'normal' => true,
);
$actions['Vote'] = array(
    'normal' => true,
);
