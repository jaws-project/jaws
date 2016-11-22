<?php
/**
 * EventsCalendar URL maps
 *
 * @category    GadgetMaps
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2016 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

// Management
$maps[] = array(
    'ManageEvents',
    'events/manage[/page/{page}]',
    array('page' => '[[:digit:]]+')
);
$maps[] = array(
    'NewEvent',
    'events/manage/new'
);
$maps[] = array(
    'EditEvent',
    'events/manage/edit/{id}',
    array('id' => '[[:digit:]]+')
);
$maps[] = array(
    'ShareEvent',
    'events/manage/share/{id}',
    array('id' => '[[:digit:]]+')
);

// Calendar
$maps[] = array(
    'ViewEvent',
    'events/view/{id}',
    array('id' => '[[:digit:]]+')
);
$maps[] = array(
    'ViewYear',
    'events[/user/{user}][/{year}]',
    array(
        'user' => '[[:digit:]]+',
        'year' => '\d{4}'
    )
);
$maps[] = array(
    'ViewMonth',
    'events[/user/{user}]/{year}/{month}',
    array(
        'user' => '[[:digit:]]+',
        'year'  => '\d{4}',
        'month' => '[01]?\d'
    )
);
$maps[] = array(
    'ViewDay',
    'events[/user/{user}]/{year}/{month}/{day}',
    array(
        'user' => '[[:digit:]]+',
        'year'  => '\d{4}',
        'month' => '[01]?\d',
        'day'   => '[0-3]?\d'
    )
);
$maps[] = array(
    'ViewWeek',
    'events[/user/{user}]/{year}/{month}/{day}/week',
    array(
        'user' => '[[:digit:]]+',
        'year'  => '\d{4}',
        'month' => '[01]?\d',
        'day'   => '[0-3]?\d'
    )
);

// Public Calendar
$maps[] = array(
    'PublicViewYear',
    'events/public[/{year}]',
    array('year' => '\d{4}')
);
$maps[] = array(
    'PublicViewMonth',
    'events/public/{year}/{month}',
    array(
        'year'  => '\d{4}',
        'month' => '[01]?\d'
    )
);
$maps[] = array(
    'PublicViewDay',
    'events/public/{year}/{month}/{day}',
    array(
        'year'  => '\d{4}',
        'month' => '[01]?\d',
        'day'   => '[0-3]?\d'
    )
);
$maps[] = array(
    'PublicViewWeek',
    'events/public/{year}/{month}/{day}/week',
    array(
        'year'  => '\d{4}',
        'month' => '[01]?\d',
        'day'   => '[0-3]?\d'
    )
);
