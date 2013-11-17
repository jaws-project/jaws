<?php
/**
 * Comments Actions file
 *
 * @category    GadgetActions
 * @package     Comments
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Index actions
 */
$actions['Comments'] = array(
    'normal' => true,
    'file'   => 'Comments',
);
$actions['PostMessage'] = array(
    'normal' => true,
    'file'   => 'Comments',
);
$actions['Preview'] = array(
    'normal' => true,
    'file'   => 'Comments',
);
$actions['RecentComments'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'RecentComments',
    'parametric' => true,
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
$admin_actions['Settings'] = array(
    'normal' => true,
    'file' => 'Settings',
);
$admin_actions['SearchComments'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['SizeOfCommentsSearch'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetComment'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateComment'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteComments'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['MarkAs'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['SaveSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
