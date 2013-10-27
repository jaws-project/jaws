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
    'layout' => true,
    'file' => 'Events'
);
$actions['Search'] = array(
    'standalone' => true,
    'file' => 'Events'
);
$actions['Menubar'] = array(
    'standalone' => true,
    'file' => 'Menubar'
);
$actions['NewEvent'] = array(
    'normal' => true,
    'file' => 'Event'
);
$actions['EditEvent'] = array(
    'normal' => true,
    'file' => 'Event'
);
$actions['CreateEvent'] = array(
    'standalone' => true,
    'file' => 'Event'
);
$actions['UpdateEvent'] = array(
    'standalone' => true,
    'file' => 'Event'
);
$actions['DeleteEvent'] = array(
    'standalone' => true,
    'file' => 'Event'
);
$actions['ShareEvent'] = array(
    'normal' => true,
    'file' => 'Share'
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

/* Report Actions */
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
