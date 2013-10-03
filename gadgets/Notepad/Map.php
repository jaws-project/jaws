<?php
/**
 * Notepad URL maps
 *
 * @category    GadgetMaps
 * @package     Notepad
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array(
    'Notepad',
    'notepad'
);
$maps[] = array(
    'NewNote',
    'notepad/new'
);
$maps[] = array(
    'ViewNote',
    'notepad/view/{id}',
    array('id' => '[[:digit:]]+')
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

