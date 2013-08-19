<?php
/**
 * Contact Actions file
 *
 * @category    GadgetActions
 * @package     Contact
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

/* Admin actions */
$actions['Contacts'] = array(
    'normal' => true,
    'file' => 'Contacts'
);
$actions['Recipients'] = array(
    'normal' => true,
    'file' => 'Recipients'
);
$actions['Properties'] = array(
    'normal' => true,
    'file' => 'Properties'
);
$actions['Mailer'] = array(
    'normal' => true,
    'file' => 'Mailer'
);
$actions['UploadFile'] = array(
    'standalone' => true,
    'file' => 'Mailer'
);
