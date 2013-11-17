<?php
/**
 * Tags Actions
 *
 * @category    GadgetActions
 * @package     Tags
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Index actions
 */
$actions['TagCloud'] = array(
    'layout' => true,
    'parametric' => true,
    'file'   => 'Tags',
);
$actions['ViewTag'] = array(
    'normal' => true,
    'file'   => 'Tags',
);
$actions['ManageTags'] = array(
    'normal' => true,
    'file'   => 'ManageTags',
);
$actions['EditTagUI'] = array(
    'normal' => true,
    'file'   => 'ManageTags',
);
$actions['UpdateTag'] = array(
    'standalone' => true,
    'file'       => 'ManageTags',
);
$actions['DeleteTags'] = array(
    'standalone' => true,
    'file'       => 'ManageTags',
);
$actions['MergeTags'] = array(
    'standalone' => true,
    'file'       => 'ManageTags',
);

/**
 * Admin actions
 */
$admin_actions['Tags'] = array(
    'normal' => true,
    'file' => 'Tags',
);
$admin_actions['Properties'] = array(
    'normal' => true,
    'file' => 'Settings',
);
$admin_actions['SearchTags'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['SizeOfTagsSearch'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGadgetActions'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetTag'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['AddTag'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateTag'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteTags'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['MergeTags'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['SaveSettings'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
