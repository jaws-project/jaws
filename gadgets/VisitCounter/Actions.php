<?php
/**
 * VisitCounter Actions file
 *
 * @category    GadgetActions
 * @package     VisitCounter
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['DisplayOnline'] = array(
    'layout' => true,
    'file' => 'VisitCounter'
);
$actions['DisplayToday'] = array(
    'layout' => true,
    'file' => 'VisitCounter'
);
$actions['DisplayYesterday'] = array(
    'layout' => true,
    'file' => 'VisitCounter'
);
$actions['DisplayTotal'] = array(
    'layout' => true,
    'file' => 'VisitCounter'
);
$actions['Display'] = array(
    'layout' => true,
    'file' => 'VisitCounter'
);

/**
 * Admin actions
 */
$admin_actions['VisitCounter'] = array(
    'normal' => true,
    'file' => 'VisitCounter'
);
$admin_actions['CleanEntries'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['ResetCounter'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetStartDate'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateProperties'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['getData'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
