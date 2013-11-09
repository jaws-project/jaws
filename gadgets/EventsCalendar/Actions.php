<?php
/**
 * EventsCalendar Actions
 *
 * @category    GadgetActions
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

/* Manage Actions */
$actions['ManageEvents'] = array(
    'normal' => true,
    'file' => 'ManageEvents'
);
$actions['Search'] = array(
    'standalone' => true,
    'file' => 'ManageEvents'
);
$actions['NewEvent'] = array(
    'normal' => true,
    'file' => 'ManageEvent'
);
$actions['EditEvent'] = array(
    'normal' => true,
    'file' => 'ManageEvent'
);
$actions['CreateEvent'] = array(
    'standalone' => true,
    'file' => 'ManageEvent'
);
$actions['UpdateEvent'] = array(
    'standalone' => true,
    'file' => 'ManageEvent'
);
$actions['DeleteEvent'] = array(
    'standalone' => true,
    'file' => 'ManageEvent'
);
$actions['ShareEvent'] = array(
    'normal' => true,
    'file' => 'ShareEvent'
);
$actions['UpdateShare'] = array(
    'standalone' => true,
    'file' => 'ShareEvent'
);
$actions['GetUsers'] = array(
    'standalone' => true,
    'file' => 'ShareEvent'
);
$actions['Menubar'] = array(
    'standalone' => true,
    'file' => 'Menubar'
);
$actions['Pager'] = array(
    'standalone' => true,
    'file' => 'Pager'
);

/* Calendar Actions */
$actions['ViewEvent'] = array(
    'normal' => true,
    'file' => 'ViewEvent'
);
$actions['ViewYear'] = array(
    'normal' => true,
    'file' => 'ViewYear'
);
$actions['ViewMonth'] = array(
    'normal' => true,
    'file' => 'ViewMonth'
);
$actions['ViewWeek'] = array(
    'normal' => true,
    'file' => 'ViewWeek'
);
$actions['ViewDay'] = array(
    'normal' => true,
    'file' => 'ViewDay'
);
$actions['Reminder'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Reminder'
);
