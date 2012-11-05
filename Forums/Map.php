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
    'forums/{fid}/topics',
    '',
    array('fid' => '[[:alnum:]-_]+',)
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
    array('fid' => '[[:alnum:]-_]+',
          'tid' => '[[:alnum:]-_]+',)
);
$maps[] = array(
    'EditTopic',
    'forums/{fid}/topics/{tid}/edit',
    '',
    array('fid' => '[[:alnum:]-_]+',
          'tid' => '[[:alnum:]-_]+',)
);
$maps[] = array(
    'LockTopic',
    'forums/{fid}/topics/{tid}/lock ',
    '',
    array('fid' => '[[:alnum:]-_]+',
          'tid' => '[[:alnum:]-_]+',)
);
$maps[] = array(
    'Posts',
    'forums/{fid}/topics/{tid}/posts',
    '',
    array('fid' => '[[:alnum:]-_]+',
          'tid' => '[[:alnum:]-_]+',)
);
$maps[] = array(
    'NewPost',
    'forums/{fid}/topics/{tid}/posts/new',
    '',
    array('fid' => '[[:alnum:]-_]+',
          'tid' => '[[:alnum:]-_]+',)
);
$maps[] = array(
    'Post',
    'forums/{fid}/topics/{tid}/posts/{pid}',
    '',
    array('fid' => '[[:alnum:]-_]+',
          'tid' => '[[:alnum:]-_]+',
          'pid' => '[[:alnum:]-_]+',)
);
$maps[] = array(
    'EditPost',
    'forums/{fid}/topics/{tid}/posts/{pid}/edit',
    '',
    array('fid' => '[[:alnum:]-_]+',
          'tid' => '[[:alnum:]-_]+',
          'pid' => '[[:alnum:]-_]+',)
);
