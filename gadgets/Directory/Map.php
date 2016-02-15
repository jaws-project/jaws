<?php
/**
 * Directory URL maps
 *
 * @category    GadgetMaps
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array(
    'Directory',
    'directory[/{id}][/type/{type}][/page/{page}][/order/{order}]',
    array(
        'id' => '[[:digit:]]+',
        'page' => '[[:digit:]]+',
        'type' => '[[:digit:]]+',
        'order' => '[[:alnum:]]+',
    )
);
$maps[] = array(
    'Download',
    'directory/download/{id}[.{ext}]',
    array('id' => '[[:digit:]]+'),
    ''
);
