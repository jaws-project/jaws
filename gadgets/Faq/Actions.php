<?php
/**
 * Faq Actions file
 *
 * @category    GadgetActions
 * @package     Faq
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2004-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Index actions
 */
$actions['View'] = array(
    'normal' => true,
    'file' => 'Question',
);
$actions['ViewQuestion'] = array(
    'normal' => true,
    'file' => 'Question',
);
$actions['ViewCategory'] = array(
    'normal' => true,
    'file' => 'Category',
);
$actions['ListCategories'] = array(
    'layout' => true,
    'file' => 'Category',
);

/**
 * Admin actions
 */
$admin_actions['Questions'] = array(
    'normal' => true,
    'file' => 'Question',
);
$admin_actions['GetQuestions'] = array(
    'normal' => true,
    'file' => 'Question',
);
//$admin_actions['NewQuestion'] = array(
//    'normal' => true,
//    'file' => 'Question',
//);
//$admin_actions['EditQuestion'] = array(
//    'normal' => true,
//    'file' => 'Question',
//);
//$admin_actions['UpdateQuestion'] = array(
//    'normal' => true,
//    'file' => 'Question',
//);
$admin_actions['NewCategory'] = array(
    'normal' => true,
    'file' => 'Category',
);
$admin_actions['EditCategory'] = array(
    'normal' => true,
    'file' => 'Category',
);
$admin_actions['UpdateCategory'] = array(
    'normal' => true,
    'file' => 'Category',
);
$admin_actions['DeleteCategory'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['DeleteQuestion'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['MoveQuestion'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['MoveCategory'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['ParseText'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
$admin_actions['GetCategoryGrid'] = array(
    'standalone' => true,
    'file' => 'Ajax',
);
