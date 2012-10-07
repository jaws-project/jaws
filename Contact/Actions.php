<?php
/**
 * Contact Actions file
 *
 * @category   GadgetActions
 * @package    Contact
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$actions = array();

/* Normal actions*/
$actions['ContactMini']   = array('NormalAction');
$actions['ContactSimple'] = array('NormalAction');
$actions['ContactFull']   = array('NormalAction');
$actions['Send']          = array('NormalAction');

$actions['Display']       = array('LayoutAction', _t('CONTACT_ACTION_DISPLAY'),        _t('CONTACT_ACTION_DISPLAY_DESCRIPTION'));
$actions['DisplayMini']   = array('LayoutAction', _t('CONTACT_ACTION_DISPLAY_MINI'),   _t('CONTACT_ACTION_DISPLAY_MINI_DESCRIPTION'));
$actions['DisplaySimple'] = array('LayoutAction', _t('CONTACT_ACTION_DISPLAY_SIMPLE'), _t('CONTACT_ACTION_DISPLAY_SIMPLE_DESCRIPTION'));
$actions['DisplayFull']   = array('LayoutAction', _t('CONTACT_ACTION_DISPLAY_FULL'),   _t('CONTACT_ACTION_DISPLAY_FULL_DESCRIPTION'));

/* Admin actions */
$actions['Admin']         = array('AdminAction');
$actions['Recipients']    = array('AdminAction');
$actions['Properties']    = array('AdminAction');
$actions['Mailer']        = array('AdminAction');
$actions['UploadFile']    = array('StandaloneAdminAction');

