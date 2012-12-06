<?php
/**
 * Blog Actions file
 *
 * @category    GadgetActions
 * @package     Blog
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

$actions['Summary']          = array('AdminAction:Summary');
$actions['NewEntry']         = array('AdminAction:Entries');
$actions['SaveNewEntry']     = array('AdminAction:Entries');
$actions['PreviewNewEntry']  = array('AdminAction:Entries');
$actions['ListEntries']      = array('AdminAction:Entries');
$actions['EditEntry']        = array('AdminAction:Entries');
$actions['PreviewEditEntry'] = array('AdminAction:Entries');
$actions['SaveEditEntry']    = array('AdminAction:Entries');
$actions['DeleteEntry']      = array('AdminAction:Entries');
$actions['UpdateCategory']   = array('AdminAction:Categories');
$actions['AddCategory']      = array('AdminAction:Categories');
$actions['EditCategory']     = array('AdminAction:Categories');
$actions['DeleteCategory']   = array('AdminAction:Categories');
$actions['ManageCategories'] = array('AdminAction:Categories');
$actions['ManageComments']   = array('AdminAction:Comments');
$actions['EditComment']      = array('AdminAction:Comments');
$actions['SaveEditComment']  = array('AdminAction:Comments');
$actions['DeleteComment']    = array('AdminAction:Comments');
$actions['ManageTrackbacks'] = array('AdminAction:Trackbacks');
$actions['ViewTrackback']    = array('AdminAction:Trackbacks');
$actions['AdditionalSettings']     = array('AdminAction:Settings');
$actions['SaveAdditionalSettings'] = array('AdminAction:Settings');
