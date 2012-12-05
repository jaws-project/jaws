<?php
/**
 * Faq Actions file
 *
 * @category   GadgetActions
 * @package    Faq
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$index_actions = array();
$admin_actions = array();

/* Admin actions */
$admin_actions['Admin']           = array('AdminAction');
$admin_actions['ManageQuestions'] = array('AdminAction');
$admin_actions['NewQuestion']     = array('AdminAction');
$admin_actions['EditQuestion']    = array('AdminAction');
$admin_actions['UpdateQuestion']  = array('AdminAction');
$admin_actions['NewCategory']     = array('AdminAction');
$admin_actions['EditCategory']    = array('AdminAction');
$admin_actions['UpdateCategory']  = array('AdminAction');

$index_actions['View']         = array('NormalAction');
$index_actions['ViewQuestion'] = array('NormalAction');
$index_actions['ViewCategory'] = array('NormalAction');

$index_actions['ListCategories'] = array(
    'LayoutAction',  
    _t('FAQ_LAYOUT_LISTCATEGORIES'),
    _t('FAQ_LAYOUT_LISTCATEGORIES_DESCRIPTION')
);