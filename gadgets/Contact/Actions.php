<?php
/**
 * Contact Actions file
 *
 * @category    GadgetActions
 * @package     Contact
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['Contact'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Contact'
);
$actions['ContactMini'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Contact'
);
$actions['ContactSimple'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Contact'
);
$actions['ContactFull'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Contact'
);
$actions['Send'] = array(
    'normal' => true,
    'file' => 'Send'
);

/**
 * Admin actions
 */
$admin_actions['Contacts'] = array(
    'normal' => true,
    'file' => 'Contacts'
);
$admin_actions['Recipients'] = array(
    'normal' => true,
    'file' => 'Recipients'
);
$admin_actions['Properties'] = array(
    'normal' => true,
    'file' => 'Properties'
);
$admin_actions['Mailer'] = array(
    'normal' => true,
    'file' => 'Mailer'
);
$admin_actions['UploadFile'] = array(
    'standalone' => true,
    'file' => 'Mailer'
);
