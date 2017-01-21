<?php
/**
 * Directory URL maps
 *
 * @category    GadgetMaps
 * @package     Directory
 */
$maps[] = array(
    'Directory',
    'directory[/user/{user}][/public/{public}][/{id}][/type/{type}][/page/{page}][/order/{order}]',
    array(
        'id' => '[[:digit:]]+',
        'user' => '[[:alnum:]\-_.@]+',
        'page' => '[[:digit:]]+',
        'type' => '[[:digit:],]+',
        'order' => '[[:alnum:]]+',
    )
);
$maps[] = array(
    'UploadFileUI',
    'directory[/id/{id}][/parent/{parent}]',
    array(
        'parent' => '[[:digit:]]+',
    )
);
$maps[] = array(
    'Download',
    'directory/download/{id}[/user/{user}][/key/{key}][.{ext}]',
    array('id' => '[[:digit:]]+'),
    ''
);
