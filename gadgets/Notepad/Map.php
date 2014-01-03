<?php
/**
 * Notepad URL maps
 *
 * @category    GadgetMaps
 * @package     Notepad
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array(
    'Notepad',
    'notepad[/filter/{filter}][/query/{query}][/page/{page}]',
    array(
        'filter' => '[[:digit:]]+',
        'query' => '[[:alnum:]-_]+',
        'page' => '[[:digit:]]+'
    )
);
$maps[] = array(
    'ViewNote',
    'notepad/view/{id}',
    array('id' => '[[:digit:]]+')
);
$maps[] = array(
    'OpenNote',
    'notepad/open/{id}',
    array('id' => '[[:digit:]]+')
);
$maps[] = array(
    'NewNote',
    'notepad/new'
);
$maps[] = array(
    'EditNote',
    'notepad/edit/{id}',
    array('id' => '[[:digit:]]+')
);
$maps[] = array(
    'ShareNote',
    'notepad/share/{id}',
    array('id' => '[[:digit:]]+')
);

