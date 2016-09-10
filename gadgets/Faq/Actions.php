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
    'standalone' => true,
    'file' => 'Question',
);
$admin_actions['GetQuestionsCount'] = array(
    'standalone' => true,
    'file' => 'Question',
);
$admin_actions['GetQuestion'] = array(
    'standalone' => true,
    'file' => 'Question',
);
$admin_actions['InsertQuestion'] = array(
    'standalone' => true,
    'file' => 'Question',
);
$admin_actions['UpdateQuestion'] = array(
    'standalone' => true,
    'file' => 'Question',
);
$admin_actions['DeleteQuestion'] = array(
    'standalone' => true,
    'file' => 'Question',
);
$admin_actions['MoveQuestion'] = array(
    'standalone' => true,
    'file' => 'Question',
);
$admin_actions['Categories'] = array(
    'normal' => true,
    'file' => 'Category',
);
$admin_actions['GetCategories'] = array(
    'standalone' => true,
    'file' => 'Category',
);
$admin_actions['GetCategory'] = array(
    'standalone' => true,
    'file' => 'Category',
);
$admin_actions['InsertCategory'] = array(
    'standalone' => true,
    'file' => 'Category',
);
$admin_actions['UpdateCategory'] = array(
    'standalone' => true,
    'file' => 'Category',
);
$admin_actions['DeleteCategory'] = array(
    'standalone' => true,
    'file' => 'Category',
);
$admin_actions['MoveCategory'] = array(
    'standalone' => true,
    'file' => 'Category',
);
