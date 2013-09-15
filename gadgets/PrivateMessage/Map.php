<?php
/**
 * PrivateMessage URL maps
 *
 * @category   GadgetMaps
 * @package    PrivateMessage
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$maps[] = array(
    'PrivateMessage',
    'privatemessage'
);
$maps[] = array(
    'Inbox',
    'pm/inbox[/view/{view}][/page/{page}]',
    array(
        'page' => '[[:digit:]]+',
        'view' => '[[:lower:]-]+',
    )
);
$maps[] = array(
    'Outbox',
    'pm/outbox[/page/{page}]',
    array(
        'page' => '[[:digit:]]+',
    )
);
$maps[] = array(
    'Draft',
    'pm/draft[/page/{page}]',
    array(
        'page' => '[[:digit:]]+',
    )
);
$maps[] = array(
    'Compose',
//    'pm/compose[/forward/{id}]',
    'pm/compose[/id/{id}][/reply/{reply}]',
    array('id' => '[\p{L}[:digit:]-_\.]+',)
);
$maps[] = array(
    'Reply',
    'pm/reply/{id}',
    array('id' => '[\p{L}[:digit:]-_\.]+',)
);
$maps[] = array(
    'Message',
    'pm/message/{id}[/view/{view}]',
    array(
        'id' => '[\p{L}[:digit:]-_\.]+',
        'view' => '[[:lower:]-]+',
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
    'MessageHistory',
    'pm/message/history/{id}',
    array('id' => '[\p{L}[:digit:]-_\.]+',)
);
$maps[] = array(
    'DeleteMessage',
    'pm/message[/type/{type}]/delete/{id}',
    array(
        'id' => '[\p{L}[:digit:]-_\.]+',
        'type' => '[[:lower:]-]+',
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