<?php
/**
 * Directory URL maps
 *
 * @category    GadgetMaps
 * @package     Directory
 */
$maps[] = array(
    'Directory',
    'directory[/{id}][/user/{user}][/type/{type}][/page/{page}][/order/{order}]',
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
    'directory/upload[/{parent}]',
    array(
        'parent' => '[[:digit:]]+',
    )
);
$maps[] = array(
    'Download',
    'directory/download/{id}[.{ext}]',
    array('id' => '[[:digit:]]+'),
    ''
);
