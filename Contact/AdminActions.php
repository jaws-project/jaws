<?php
/**
 * Contact Actions file
 *
 * @category    GadgetActions
 * @package     Contact
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

/* Admin actions */
$actions['Admin']         = array('AdminAction');
$actions['Recipients']    = array('AdminAction');
$actions['Properties']    = array('AdminAction');
$actions['Mailer']        = array('AdminAction');
$actions['UploadFile']    = array('StandaloneAdminAction');
