<?php
/**
 * Directory URL maps
 *
 * @category    GadgetMaps
 * @package     Directory
 */
$maps[] = array(
    'File',
    'directory/file[/{id}]',
    array(
        'id' => '[[:digit:]]+',
    )
);
$maps[] = array(
    'Directory',
    'directory/directory/[/user/{user}][/public/{public}][/{id}][/type/{type}][/page/{page}][/order/{order}]',
    array(
        'id' => '[[:digit:]]+',
        'user' => '[[:alnum:]\-_.@]+',
        'page' => '[[:digit:]]+',
        'type' => '[[:digit:],]+',
        'order' => '[[:alnum:]]+',
    )
);
$maps[] = array(
    'Download',
    'directory/download[/{id}][/user/{user}][/key/{key}][.{ext}]',
    array('id' => '[[:digit:]]+'),
    ''
);
