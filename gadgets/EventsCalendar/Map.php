<?php
/**
 * EventsCalendar URL maps
 *
 * @category    GadgetMaps
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

// Management
$maps[] = array(
    'ManageEvents',
    'events/manage[/page/{page}]',
    array(
        'page' => '[[:digit:]]+'
    )
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
    'events[/{year}]',
    array('year' => '\d{4}')
);
$maps[] = array(
    'ViewMonth',
    'events/{year}/{month}',
    array('year'  => '\d{4}',
          'month' => '[01]?\d')
);
$maps[] = array(
    'ViewDay',
    'events/{year}/{month}/{day}',
    array('year'  => '\d{4}',
          'month' => '[01]?\d',
          'day'   => '[0-3]?\d')
);
$maps[] = array(
    'ViewWeek',
    'events/{year}/{month}/{day}/week',
    array('year'  => '\d{4}',
          'month' => '[01]?\d',
          'day'   => '[0-3]?\d')
);
