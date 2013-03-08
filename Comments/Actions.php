<?php
/**
 * Comments Actions file
 *
 * @category    GadgetActions
 * @package     Comments
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Comments'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'Comments',
    'parametric' => true,
);

$actions['PostMessage'] = array(
    'normal' => true,
    'file'   => 'Comments',
);

$actions['RecentComments'] = array(
    'normal' => true,
    'layout' => true,
    'parametric' => true,
);
