<?php
/**
 * PrivateMessage Actions
 *
 * @category    GadgetActions
 * @package     PrivateMessage
 * @author      ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2008-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
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
    'layout' => true,
    'parametric' => true,
    'file' => 'Message',
);
$actions['GetMessages'] = array(
    'standalone' => true,
    'file' => 'Message',
);
$actions['Message'] = array(
    'normal' => true,
    'file' => 'Message',
);
$actions['DraftMessage'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Draft',
);
$actions['DeleteMessage'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Message',
);
$actions['ArchiveMessage'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Message',
);
$actions['UnArchiveMessage'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Message',
);
$actions['TrashMessage'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Message',
);
$actions['RestoreTrashMessage'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Message',
);
$actions['ChangeMessageRead'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Message',
);
$actions['Compose'] = array(
    'normal' => true,
    'file' => 'Compose',
);
$actions['SendMessage'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Compose',
);
$actions['GetMessageAttachmentUI'] = array(
    'standalone' => true,
    'file' => 'Compose',
);
$actions['GetUsers'] = array(
    'standalone' => true,
    'file' => 'Compose'
);
$actions['CheckUserExist'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Compose'
);
$actions['Attachment'] = array(
    'standalone' => true,
    'file'   => 'Attachment',
);
$actions['UploadFile'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'Attachment'
);

/**
 * Admin actions
 */
$admin_actions['Properties'] = array(
    'normal' => true,
    'file' => 'Properties',
);
