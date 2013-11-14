<?php
/**
 * Notepad Actions
 *
 * @category    GadgetActions
 * @package     Notepad
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['Notepad'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Notepad'
);
$actions['StickyNote'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'StickyNote',
    'parametric' => true
);
$actions['ViewNote'] = array(
    'normal' => true,
    'file' => 'View'
);
$actions['OpenNote'] = array(
    'normal' => true,
    'file' => 'Open'
);
$actions['NewNote'] = array(
    'normal' => true,
    'file' => 'Create'
);
$actions['EditNote'] = array(
    'normal' => true,
    'file' => 'Update'
);
$actions['ShareNote'] = array(
    'normal' => true,
    'file' => 'Share'
);
$actions['CreateNote'] = array(
    'standalone' => true,
    'file' => 'Create'
);
$actions['UpdateNote'] = array(
    'standalone' => true,
    'file' => 'Update'
);
$actions['DeleteNote'] = array(
    'standalone' => true,
    'file' => 'Delete'
);
$actions['Search'] = array(
    'standalone' => true,
    'file' => 'Notepad'
);
$actions['GetUsers'] = array(
    'standalone' => true,
    'file' => 'Share'
);
$actions['UpdateShare'] = array(
    'standalone' => true,
    'file' => 'Share'
);
$actions['Pager'] = array(
    'standalone' => true,
    'file' => 'Pager'
);
