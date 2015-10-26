<?php
/**
 * Contact Actions file
 *
 * @category    GadgetActions
 * @package     Contact
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2006-2015 Jaws Development Group
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
$admin_actions['GetContact'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateContact'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateReply'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteContact'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetReply'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['ReplyUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetRecipient'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InsertRecipient'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateRecipient'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteRecipient'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateProperties'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetContacts'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetContactsCount'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetUsers'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetMessagePreview'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['SendEmail'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['getData'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
