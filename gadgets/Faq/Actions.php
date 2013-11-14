<?php
/**
 * Faq Actions file
 *
 * @category    GadgetActions
 * @package     Faq
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2004-2013 Jaws Development Group
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
$admin_actions['ManageQuestions'] = array(
    'normal' => true,
    'file' => 'Question',
);
$admin_actions['NewQuestion'] = array(
    'normal' => true,
    'file' => 'Question',
);
$admin_actions['EditQuestion'] = array(
    'normal' => true,
    'file' => 'Question',
);
$admin_actions['UpdateQuestion'] = array(
    'normal' => true,
    'file' => 'Question',
);
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
