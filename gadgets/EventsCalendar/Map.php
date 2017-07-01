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

// Public Calendar
$maps[] = array(
    'ViewYear',
    'events[/{user}[/calendar[/{year}]]]',
    array(
        'user' => '[[:digit:]]+',
        'year' => '\d{4}'
    )
);
$maps[] = array(
    'ViewMonth',
    'events/{user}/calendar/{year}/{month}',
    array(
        'user' => '[[:digit:]]+',
        'year' => '\d{4}',
        'month' => '[01]?\d'
    )
);
$maps[] = array(
    'ViewDay',
    'events/{user}/calendar/{year}/{month}/{day}',
    array(
        'user' => '[[:digit:]]+',
        'year' => '\d{4}',
        'month' => '[01]?\d',
        'day' => '[0-3]?\d'
    )
);
$maps[] = array(
    'ViewWeek',
    'events/{user}/calendar/{year}/{month}/{day}/week',
    array(
        'user' => '[[:digit:]]+',
        'year' => '\d{4}',
        'month' => '[01]?\d',
        'day' => '[0-3]?\d'
    )
);
$maps[] = array(
    'ViewEvent',
    'events/{user}/{event}',
    array(
        'user' => '[[:digit:]]+',
        'event' => '[[:digit:]]+'
    )
);
$maps[] = array(
    'ManageEvents',
    'events/{user}/manage[/page/{page}]',
    array('page' => '[[:digit:]]+')
);

$maps[] = array(
    'EditEvent',
    'events/{user}/{event}/edit',
    array(
        'user' => '[[:digit:]]+',
        'event' => '[[:digit:]]+'
    )
);
$maps[] = array(
    'ShareEvent',
    'events/{user}/{event}/share',
    array(
        'user' => '[[:digit:]]+',
        'event' => '[[:digit:]]+'
    )
);

