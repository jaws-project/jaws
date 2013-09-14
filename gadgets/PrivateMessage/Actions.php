<?php
/**
 * PrivateMessage Actions
 *
 * @category    GadgetActions
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$actions = array();

$actions['PrivateMessage'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'PrivateMessage',
);
$actions['Inbox'] = array(
    'normal' => true,
    'file' => 'Inbox',
);
$actions['Draft'] = array(
    'normal' => true,
    'file' => 'Draft',
);
$actions['Message'] = array(
    'normal' => true,
    'file' => 'Message',
);
$actions['PublishMessage'] = array(
    'standalone' => true,
    'file' => 'Message',
);
$actions['UnreadMessage'] = array(
    'standalone' => true,
    'file' => 'Message',
);
$actions['MessageHistory'] = array(
    'normal' => true,
    'file' => 'Message',
);
$actions['DeleteMessage'] = array(
    'standalone' => true,
    'file' => 'Message',
);
$actions['UnreadMessage'] = array(
    'standalone' => true,
    'file' => 'Message',
);
$actions['Reply'] = array(
    'normal' => true,
    'file' => 'Reply',
);
$actions['ReplyMessage'] = array(
    'standalone' => true,
    'file' => 'Reply',
);
$actions['Outbox'] = array(
    'normal' => true,
    'file' => 'Outbox',
);
$actions['Send'] = array(
    'normal' => true,
    'file' => 'Send',
);
$actions['SendMessage'] = array(
    'standalone' => true,
    'file' => 'Send',
);
$actions['Attachment'] = array(
    'standalone' => true,
    'file'   => 'Attachment',
);