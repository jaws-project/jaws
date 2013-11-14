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

/**
 * Index actions
 */
$actions['PrivateMessage'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'PrivateMessage',
);
$actions['AllMessages'] = array(
    'normal' => true,
    'file' => 'AllMessages',
);
$actions['Announcement'] = array(
    'normal' => true,
    'file' => 'Announcement',
);
$actions['Inbox'] = array(
    'normal' => true,
    'file' => 'Inbox',
);
$actions['Draft'] = array(
    'normal' => true,
    'file' => 'Draft',
);
$actions['DraftMessage'] = array(
    'standalone' => true,
    'file' => 'Draft',
);
$actions['InboxMessage'] = array(
    'normal' => true,
    'file' => 'InboxMessage',
);
$actions['OutboxMessage'] = array(
    'normal' => true,
    'file' => 'OutboxMessage',
);
$actions['PublishMessage'] = array(
    'standalone' => true,
    'file' => 'OutboxMessage',
);
$actions['MessageHistory'] = array(
    'normal' => true,
    'file' => 'Message',
);
$actions['DeleteInboxMessage'] = array(
    'standalone' => true,
    'file' => 'InboxMessage',
);
$actions['ArchiveInboxMessage'] = array(
    'standalone' => true,
    'file' => 'InboxMessage',
);
$actions['DeleteOutboxMessage'] = array(
    'standalone' => true,
    'file' => 'OutboxMessage',
);
$actions['ChangeMessageRead'] = array(
    'standalone' => true,
    'file' => 'InboxMessage',
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

/**
 * Admin actions
 */
$admin_actions['Properties'] = array(
    'normal' => true,
    'file' => 'Properties',
);
