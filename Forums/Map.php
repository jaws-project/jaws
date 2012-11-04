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
