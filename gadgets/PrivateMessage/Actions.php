<?php
/**
 * PrivateMessage Actions
 *
 * @category    GadgetActions
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Index actions
 */
$actions['PrivateMessage'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'PrivateMessage',
);
$actions['Messages'] = array(
    'normal' => true,
    'file' => 'Message',
);
$actions['Message'] = array(
    'normal' => true,
    'file' => 'Message',
);
$actions['DraftMessage'] = array(
    'standalone' => true,
    'file' => 'Draft',
);
$actions['DeleteMessage'] = array(
    'standalone' => true,
    'file' => 'Message',
);
$actions['ArchiveMessage'] = array(
    'standalone' => true,
    'file' => 'Message',
);
$actions['UnArchiveMessage'] = array(
    'standalone' => true,
    'file' => 'Message',
);
$actions['TrashMessage'] = array(
    'standalone' => true,
    'file' => 'Message',
);
$actions['RestoreTrashMessage'] = array(
    'standalone' => true,
    'file' => 'Message',
);
$actions['ChangeMessageRead'] = array(
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
$actions['Compose'] = array(
    'normal' => true,
    'file' => 'Compose',
);
$actions['ComposeMessage'] = array(
    'standalone' => true,
    'file' => 'Compose',
);
$actions['GetMessageAttachmentUI'] = array(
    'standalone' => true,
    'file' => 'Compose',
);
$actions['Attachment'] = array(
    'standalone' => true,
    'file'   => 'Attachment',
);
$actions['UploadFile'] = array(
    'standalone' => true,
    'file' => 'Attachment'
);