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
$actions = array();

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