<?php
/**
 * Directory URL maps
 *
 * @category    GadgetMaps
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array(
    'Directory',
    'directory[/{dirid}]',
    array('dirid' => '[[:digit:]]+')
);
$maps[] = array(
    'GetFiles',
    'directory/files/{id}[/shared/{shared}][/foreign/{foreign}]',
    array(
        'id' => '[[:digit:]]+',
        'shared' => '[[:digit:]]+',
        'foreign' => '[[:digit:]]+'
    )
);
$maps[] = array(
    'GetFile',
    'directory/file/{id}',
    array('id' => '[[:digit:]]+')
);
$maps[] = array(
    'GetFileUsers',
    'directory/file/{id}/users',
    array('id' => '[[:digit:]]+')
);
$maps[] = array(
    'DownloadFile',
    'directory/file/{id}/download',
    array('id' => '[[:digit:]]+')
);
$maps[] = array(
    'OpenFile',
    'directory/file/{id}/open[.{ext}]',
    array(
        'id' => '[[:digit:]]+',
        'ext' => '[[:alnum:]]+'
    ),
    ''
);
$maps[] = array(
    'GetPath',
    'directory/path/{id}',
    array('id' => '[[:digit:]]+')
);
$maps[] = array(
    'GetTree',
    'directory/tree[/{id}]',
    array('id' => '[[:digit:]]+')
);
$maps[] = array(
    'Move',
    'directory/move/{id}/target/{target}',
    array(
        'id' => '[[:digit:]]+',
        'target' => '[[:digit:]]+'
    )
);
$maps[] = array(
    'FileForm',
    'directory/form/file[/{mode}]',
    array('mode' => '[[:alnum:]]+')
);
$maps[] = array(
    'DirectoryForm',
    'directory/form/directory[/{mode}]',
    array('mode' => '[[:alnum:]]+')
);
$maps[] = array(
    'ShareForm',
    'directory/form/share'
);

