<?php
/**
 * EventsCalendar Actions
 *
 * @category    GadgetActions
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2008-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Admin actions
 */
$admin_actions['PublicEvents'] = array(
    'normal' => true,
    'file' => 'EventsCalendar'
);
$admin_actions['UserEvents'] = array(
    'normal' => true,
    'file' => 'EventsCalendar'
);
$admin_actions['GetEvents'] = array(
    'standalone' => true,
    'file' => 'EventsCalendar'
);
$admin_actions['GetEvent'] = array(
    'standalone' => true,
    'file' => 'EventsCalendar'
);
$admin_actions['CreateEvent'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'ManageEvent'
);
$admin_actions['UpdateEvent'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'ManageEvent'
);
$admin_actions['DeleteEvents'] = array(
    'standalone' => true,
    'internal' => true,
    'file' => 'ManageEvent'
);

/**
 * Index actions
 */
$actions['ManageEvents'] = array(
    'normal' => true,
    'file' => 'ManageEvents',
    'navigation' => array(
        'params' => array(
            'user' => (int)$this->app->session->user->id
        ),
        'order' => 1
    ),
    'acls' => array(
        'UserEvents',
    ),
);
$actions['Search'] = array(
    'standalone' => true,
    'file' => 'ManageEvents'
);
$actions['NewEvent'] = array(
    'normal' => true,
    'file' => 'ManageEvent',
    'acls' => array(
        'UserEvents',
    ),
);
$actions['EditEvent'] = array(
    'normal' => true,
    'file' => 'ManageEvent',
    'acls' => array(
        'UserEvents',
    ),
);
$actions['CreateEvent'] = array(
    'standalone' => true,
    'file' => 'ManageEvent',
    'acls' => array(
        'UserEvents',
    ),
);
$actions['UpdateEvent'] = array(
    'standalone' => true,
    'file' => 'ManageEvent'
);
$actions['DeleteEvent'] = array(
    'standalone' => true,
    'file' => 'ManageEvent',
    'acls' => array(
        'UserEvents',
    ),
);
$actions['ShareEvent'] = array(
    'normal' => true,
    'file' => 'ShareEvent',
    'acls' => array(
        'UserEvents',
    ),
);
$actions['UpdateShare'] = array(
    'standalone' => true,
    'file' => 'ShareEvent',
    'acls' => array(
        'UserEvents',
    ),
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
$actions['ViewEvent'] = array(
    'normal' => true,
    'file' => 'ViewEvent'
);
$actions['ViewYear'] = array(
    'normal' => true,
    'file' => 'ViewYear',
    'navigation' => array(
        'params' => array(
            'user' => (int)$this->app->session->user->id
        ),
        'order' => 0
    ),
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

/**
 * Layout actions
 */
$actions['Today'] = array(
    'layout' => true,
    'parametric' => true,
    'file' => 'Today'
);
$actions['Calendar'] = array(
    'layout' => true,
    'parametric' => true,
    'file' => 'Calendar'
);
$actions['Reminder'] = array(
    'layout' => true,
    'parametric' => true,
    'file' => 'Reminder'
);
