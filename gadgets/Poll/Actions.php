<?php
/**
 * Poll Actions file
 *
 * @category    GadgetActions
 * @package     Poll
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['Poll'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Poll',
    'parametric' => true,
);
$actions['Polls'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Poll',
    'parametric' => true,
);
$actions['ViewResult'] = array(
    'normal' => true,
    'file' => 'Poll',
);
$actions['Vote'] = array(
    'normal' => true,
    'file' => 'Poll',
);

/**
 * Admin actions
 */
$admin_actions['Polls'] = array(
    'normal' => true,
    'file' => 'Poll',
);
$admin_actions['PollGroups'] = array(
    'normal' => true,
    'file' => 'Group',
);
$admin_actions['Reports'] = array(
    'normal' => true,
    'file' => 'Report',
);
$admin_actions['GetPoll'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InsertPoll'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdatePoll'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeletePoll'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['PollAnswersUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetPollAnswers'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdatePollAnswers'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetPollGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InsertPollGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdatePollGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeletePollGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['PollGroupPollsUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetPollGroupPolls'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['AddPollsToPollGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGroupPolls'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['PollResultsUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['getData'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
