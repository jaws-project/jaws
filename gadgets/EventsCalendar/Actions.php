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

/* Public Actions */
$actions['Events'] = array(
    'normal' => true,
    'layout' => true,
    'file' => 'Events'
);
$actions['NewEvent'] = array(
    'normal' => true,
    'file' => 'Create'
);
$actions['EditEvent'] = array(
    'normal' => true,
    'file' => 'Update'
);
$actions['ShareEvent'] = array(
    'normal' => true,
    'file' => 'Share'
);
$actions['CreateEvent'] = array(
    'standalone' => true,
    'file' => 'Create'
);
$actions['UpdateEvent'] = array(
    'standalone' => true,
    'file' => 'Update'
);
$actions['DeleteEvent'] = array(
    'standalone' => true,
    'file' => 'Delete'
);
$actions['Search'] = array(
    'standalone' => true,
    'file' => 'Events'
);
$actions['GetUsers'] = array(
    'standalone' => true,
    'file' => 'Share'
);
$actions['UpdateEvent'] = array(
    'standalone' => true,
    'file' => 'Share'
);
$actions['Pager'] = array(
    'standalone' => true,
    'file' => 'Pager'
);
