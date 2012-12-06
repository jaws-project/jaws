<?php
/**
 * Preferences Actions file
 *
 * @category    GadgetActions
 * @package     Preferences
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Save'] = array('NormalAction');
$actions['SetLanguage'] = array('NormalAction');

/* Layout actions */
$actions['Display'] = array(
    'LayoutAction',
    _t('PREFERENCES_LAYOUT_DISPLAY'),
    _t('PREFERENCES_LAYOUT_DISPLAY_DESCRIPTION'),
);
