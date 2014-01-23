<?php
/**
 * PrivateMessage URL maps
 *
 * @category   GadgetMaps
 * @package    PrivateMessage
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$maps[] = array(
    'PrivateMessage',
    'privatemessage'
);
$maps[] = array(
    'Inbox',
    'pm/inbox[/view/{view}][/page/{page}][/read/{read}][/replied/{replied}][/term/{term}][/pageitem/{page_item}]',
    array(
        'page' => '[[:digit:]]+',
        'view' => '[[:lower:]-]+',
    )
);
$maps[] = array(
    'Announcement',
    'pm/announcement[/view/{view}][/page/{page}][/read/{read}][/replied/{replied}][/term/{term}][/pageitem/{page_item}]',
    array(
        'page' => '[[:digit:]]+',
        'view' => '[[:lower:]-]+',
    )
);
$maps[] = array(
    'AllMessages',
    'pm/all/messages[/view/{view}][/page/{page}][/read/{read}][/replied/{replied}][/term/{term}][/pageitem/{page_item}]',
    array(
        'page' => '[[:digit:]]+',
        'view' => '[[:lower:]-]+',
    )
);
$maps[] = array(
    'Outbox',
    'pm/outbox[/page/{page}][/replied/{replied}][/term/{term}][/pageitem/{page_item}]',
    array(
        'page' => '[[:digit:]]+',
    )
);
$maps[] = array(
    'Draft',
    'pm/draft[/page/{page}][/term/{term}][/pageitem/{page_item}]',
    array(
        'page' => '[[:digit:]]+',
    )
);
$maps[] = array(
    'Compose',
    'pm/compose[/id/{id}][/reply/{reply}]',
    array(
        'id' => '[\p{L}[:digit:]-_\.]+',
        'reply' => '[[:lower:]-]+',
    )
);
$maps[] = array(
    'Reply',
    'pm/reply/{id}',
    array('id' => '[\p{L}[:digit:]-_\.]+',)
);
$maps[] = array(
    'Message',
    'pm/message/{id}',
    array(
        'id' => '[\p{L}[:digit:]-_\.]+',
    )
);
$maps[] = array(
    'PublishMessage',
    'pm/publish/message/{id}',
    array(
        'id' => '[\p{L}[:digit:]-_\.]+',
    )
);
$maps[] = array(
    'ChangeMessageRead',
    'pm/change/read/status/{status}/message/{id}',
    array(
        'id' => '[\p{L}[:digit:]-_\.]+',
        'status' => '[[:lower:]-]+',
    )
);
$maps[] = array(
    'TrashMessage',
    'pm/message/delete/{id}',
    array(
        'id' => '[\p{L}[:digit:]-_\.]+',
    )
);
$maps[] = array(
    'DeleteMessage',
    'pm/message/delete/{id}',
    array(
        'id' => '[\p{L}[:digit:]-_\.]+',
    )
);
$maps[] = array(
    'ArchiveMessage',
    'pm/message/archive/{id}',
    array(
        'id' => '[\p{L}[:digit:]-_\.]+',
    )
);

$maps[] = array(
    'Attachment',
    'pm/{uid}/message/{mid}/attachment/{aid}',
    array(
        'uid' => '[[:alnum:]-_]+',
        'mid' => '[[:alnum:]-_]+',
        'aid' => '[[:alnum:]-_]+',
    ),
    ''
);