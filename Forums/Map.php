<?php
/**
 * Forums URL maps
 *
 * @category    GadgetMaps
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array(
    'Forums',
    'forums'
);
$maps[] = array(
    'Forum',
    'forums/{fid}',
    '',
    array('fid' => '[[:alnum:]-_]+',)
);
$maps[] = array(
    'Topics',
    'forums/{fid}/topics[/page/{page}]',
    '',
    array(
        'fid'  => '[[:alnum:]-_]+',
        'page' => '[[:digit:]]+',
    )
);
$maps[] = array(
    'NewTopic',
    'forums/{fid}/topics/new',
    '',
    array('fid' => '[[:alnum:]-_]+',)
);
$maps[] = array(
    'Topic',
    'forums/{fid}/topics/{tid}',
    '',
    array(
        'fid' => '[[:alnum:]-_]+',
        'tid' => '[[:alnum:]-_]+',
    )
);
$maps[] = array(
    'EditTopic',
    'forums/{fid}/topics/{tid}/edit',
    '',
    array(
        'fid' => '[[:alnum:]-_]+',
        'tid' => '[[:alnum:]-_]+',
    )
);
$maps[] = array(
    'LockTopic',
    'forums/{fid}/topics/{tid}/lock',
    '',
    array(
        'fid' => '[[:alnum:]-_]+',
        'tid' => '[[:alnum:]-_]+',
    )
);
$maps[] = array(
    'DeleteTopic',
    'forums/{fid}/topics/{tid}/delete',
    '',
    array(
        'fid' => '[[:alnum:]-_]+',
        'tid' => '[[:alnum:]-_]+',
    )
);
$maps[] = array(
    'Posts',
    'forums/{fid}/topics/{tid}/posts[/page/{page}]',
    '',
    array(
        'fid' => '[[:alnum:]-_]+',
        'tid' => '[[:alnum:]-_]+',
        'page' => '[[:digit:]]+',
    )
);
$maps[] = array(
    'NewPost',
    'forums/{fid}/topics/{tid}/posts/new',
    '',
    array(
        'fid' => '[[:alnum:]-_]+',
        'tid' => '[[:alnum:]-_]+',
    )
);
$maps[] = array(
    'Post',
    'forums/{fid}/topics/{tid}/posts/{pid}',
    '',
    array(
        'fid' => '[[:alnum:]-_]+',
        'tid' => '[[:alnum:]-_]+',
        'pid' => '[[:alnum:]-_]+',
    )
);
$maps[] = array(
    'EditPost',
    'forums/{fid}/topics/{tid}/posts/{pid}/edit',
    '',
    array(
        'fid' => '[[:alnum:]-_]+',
        'tid' => '[[:alnum:]-_]+',
        'pid' => '[[:alnum:]-_]+',
    )
);
$maps[] = array(
    'Attachment',
    'forums/{fid}/topics/{tid}/posts/{pid}/attachment',
    '',
    array(
        'fid' => '[[:alnum:]-_]+',
        'tid' => '[[:alnum:]-_]+',
        'pid' => '[[:alnum:]-_]+',
    )
);
$maps[] = array(
    'DeletePost',
    'forums/{fid}/topics/{tid}/posts/{pid}/delete',
    '',
    array(
        'fid' => '[[:alnum:]-_]+',
        'tid' => '[[:alnum:]-_]+',
        'pid' => '[[:alnum:]-_]+',
    )
);
