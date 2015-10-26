<?php
/**
 * PrivateMessage URL maps
 *
 * @category    GadgetMaps
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$maps[] = array(
    'PrivateMessage',
    'privatemessage'
);
$maps[] = array(
    'Messages',
    'pm/messages[/folder/{folder}][/page/{page}][/read/{read}][/replied/{replied}][/term/{term}][/pageitem/{page_item}]',
    array(
        'page' => '[[:digit:]]+',
        'folder' => '[[:digit:]]+',
    )
);
$maps[] = array(
    'Compose',
    'pm/compose[/id/{id}][/user/{user}][/reply/{reply}]',
    array(
        'id' => '[\p{L}[:digit:]\-_\.]+',
        'user' => '[[:alnum:]\-_.@]+',
        'reply' => '[[:lower:]-]+',
    )
);
$maps[] = array(
    'Message',
    'pm/message/{id}',
    array(
        'id' => '[\p{L}[:digit:]\-_\.]+',
    )
);
$maps[] = array(
    'PublishMessage',
    'pm/publish/message/{id}',
    array(
        'id' => '[\p{L}[:digit:]\-_\.]+',
    )
);
$maps[] = array(
    'ChangeMessageRead',
    'pm/change/read/status/{status}/message/{id}',
    array(
        'id' => '[\p{L}[:digit:]\-_\.]+',
        'status' => '[[:lower:]\-]+',
    )
);
$maps[] = array(
    'TrashMessage',
    'pm/message/trash/{id}',
    array(
        'id' => '[\p{L}[:digit:]\-_\.]+',
    )
);
$maps[] = array(
    'DeleteMessage',
    'pm/message/delete/{id}',
    array(
        'id' => '[\p{L}[:digit:]\-_\.]+',
    )
);
$maps[] = array(
    'ArchiveMessage',
    'pm/message/archive/{id}',
    array(
        'id' => '[\p{L}[:digit:]\-_\.]+',
    )
);
$maps[] = array(
    'UnArchiveMessage',
    'pm/message/unarchive/{id}',
    array(
        'id' => '[\p{L}[:digit:]\-_\.]+',
    )
);

$maps[] = array(
    'Attachment',
    'pm/{uid}/message/{mid}/attachment/{aid}',
    array(
        'uid' => '[[:alnum:]\-_]+',
        'mid' => '[[:alnum:]\-_]+',
        'aid' => '[[:alnum:]\-_]+',
    ),
    ''
);