<?php
/**
 * Faq Actions file
 *
 * @category    GadgetActions
 * @package     Faq
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2004-2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['View']         = array('NormalAction');
$actions['ViewQuestion'] = array('NormalAction');
$actions['ViewCategory'] = array('NormalAction');

$actions['ListCategories'] = array(
    'LayoutAction',  
    _t('FAQ_LAYOUT_LISTCATEGORIES'),
    _t('FAQ_LAYOUT_LISTCATEGORIES_DESCRIPTION')
);