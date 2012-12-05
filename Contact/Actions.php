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
$index_actions = array();
$admin_actions = array();

/* Admin actions */
$admin_actions['Admin']         = array('AdminAction');
$admin_actions['Recipients']    = array('AdminAction');
$admin_actions['Properties']    = array('AdminAction');
$admin_actions['Mailer']        = array('AdminAction');
$admin_actions['UploadFile']    = array('StandaloneAdminAction');

/* Normal actions*/
$index_actions['ContactMini']   = array('NormalAction');
$index_actions['ContactSimple'] = array('NormalAction');
$index_actions['ContactFull']   = array('NormalAction');
$index_actions['Send']          = array('NormalAction');

$index_actions['Display']       = array(
    'LayoutAction',
    _t('CONTACT_ACTION_DISPLAY'),
    _t('CONTACT_ACTION_DISPLAY_DESCRIPTION')
);
$index_actions['DisplayMini']   = array(
    'LayoutAction',
    _t('CONTACT_ACTION_DISPLAY_MINI'),
    _t('CONTACT_ACTION_DISPLAY_MINI_DESCRIPTION')
);
$index_actions['DisplaySimple'] = array(
    'LayoutAction',
    _t('CONTACT_ACTION_DISPLAY_SIMPLE'),
    _t('CONTACT_ACTION_DISPLAY_SIMPLE_DESCRIPTION')
);
$index_actions['DisplayFull']   = array(
    'LayoutAction',
    _t('CONTACT_ACTION_DISPLAY_FULL'),
    _t('CONTACT_ACTION_DISPLAY_FULL_DESCRIPTION')
);
