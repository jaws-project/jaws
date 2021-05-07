<?php
/**
 * Comments Actions file
 *
 * @category    GadgetActions
 * @package     Comments
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2012-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Index actions
 */
$actions['Guestbook'] = array(
    'normal' => true,
    'file'   => 'Comments',
);
$actions['PostMessage'] = array(
    'normal' => true,
    'internal' => true,
    'file'   => 'Comments',
);
$actions['RecentComments'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'RecentComments',
    'parametric' => true,
);
$actions['MostCommented'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'MostCommented',
    'parametric' => true,
);
$actions['UserComments'] = array(
    'normal' => true,
    'file'   => 'UserComments',
);
$actions['FeedsLink'] = array(
    'layout' => true,
    'parametric' => true,
    'file'   => 'Feeds',
);
$actions['RecentCommentsRSS'] = array(
    'standalone' => true,
    'file' => 'Feeds',
);
$actions['RecentCommentsAtom'] = array(
    'standalone' => true,
    'file' => 'Feeds',
);

/**
 * Admin actions
 */
$admin_actions['Comments'] = array(
    'normal' => true,
    'file' => 'Comments',
);
$admin_actions['GetComments'] = array(
    'standalone' => true,
    'file' => 'Comments',
);
$admin_actions['GetComment'] = array(
    'standalone' => true,
    'file' => 'Comments',
);
$admin_actions['UpdateComment'] = array(
    'standalone' => true,
    'file' => 'Comments',
);
$admin_actions['DeleteComments'] = array(
    'standalone' => true,
    'file' => 'Comments',
);
$admin_actions['MarkComments'] = array(
    'standalone' => true,
    'file' => 'Comments',
);
$admin_actions['Settings'] = array(
    'normal' => true,
    'file' => 'Settings',
);
$admin_actions['SaveSettings'] = array(
    'standalone' => true,
    'file' => 'Settings',
);