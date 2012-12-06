<?php
/**
 * Users Actions
 *
 * @category    GadgetActions
 * @package     Users
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$actions = array();

$actions['Users']        = array('AdminAction:Users');
$actions['MyAccount']    = array('AdminAction:MyAccount');
$actions['Groups']       = array('AdminAction:Groups');
$actions['OnlineUsers']  = array('AdminAction:OnlineUsers');
$actions['Properties']   = array('AdminAction:Properties');
$actions['LoadAvatar']   = array('StandaloneAdminAction:Avatar');
$actions['UploadAvatar'] = array('StandaloneAdminAction:Avatar');
