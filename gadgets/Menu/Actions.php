<?php
/**
 * Menu Actions file
 *
 * @category    GadgetActions
 * @package     Menu
 */

/**
 * Index actions
 */
$actions['Menu'] = array(
    'layout' => true,
    'file' => 'Menu',
    'parametric' => true
);
$actions['LoadImage'] = array(
    'standalone' => true,
    'file' => 'Menu'
);

/**
 * Admin actions
 */
$admin_actions['Menu'] = array(
    'normal' => true,
    'file' => 'Menu'
);
$admin_actions['UploadImage'] = array(
    'standalone' => true,
    'file' => 'Menu'
);
$admin_actions['LoadImage'] = array(
    'standalone' => true,
    'file' => 'Menu'
);
$admin_actions['GetMenusTrees'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGroupUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetMenuUI'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetGroups'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetMenu'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InsertGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['InsertMenu'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['UpdateMenu'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteGroup'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteMenu'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetParentMenus'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['MoveMenu'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetPublicURList'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetACLKeys'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
