<?php
/**
 * Jaws Upgrade Stage - From 0.8 to 0.9.0
 *
 * @category   Application
 * @package    UpgradeStage
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_08To090 extends JawsUpgraderStage
{
    /**
     * Builds the upgader page.
     *
     * @access  public
     * @return  string A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        $tpl = new Jaws_Template(UPGRADE_PATH  . 'stages/08To090/templates/');
        $tpl->Load('display.html', false, false);
        $tpl->SetBlock('08To090');

        $tpl->setVariable('lbl_info',  _t('UPGRADE_VER_INFO', '0.8', '0.9.0'));
        $tpl->setVariable('lbl_notes', _t('UPGRADE_VER_NOTES'));
        $tpl->SetVariable('next',      _t('GLOBAL_NEXT'));

        $tpl->ParseBlock('08To090');
        return $tpl->Get();
    }

    /**
     * Does any actions required to finish the stage, such as DB queries.
     *
     * @access  public
     * @return  bool|Jaws_Error  Either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function Run()
    {
        // Connect to database
        require_once JAWS_PATH . 'include/Jaws/DB.php';
        $GLOBALS['db'] = new Jaws_DB($_SESSION['upgrade']['Database']);
        if (Jaws_Error::IsError($GLOBALS['db'])) {
            _log(JAWS_LOG_DEBUG,"There was a problem connecting to the database, please check the details and try again");
            return new Jaws_Error(_t('UPGRADE_DB_RESPONSE_CONNECT_FAILED'), 0, JAWS_ERROR_WARNING);
        }

        _log(JAWS_LOG_DEBUG,"delete all record of session table");
        $sql = 'DELETE FROM [[session]]';
        $res = $GLOBALS['db']->query($sql);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // Create application
        include_once JAWS_PATH . 'include/Jaws.php';
        $GLOBALS['app'] = new Jaws();
        $GLOBALS['app']->loadClass('ACL', 'Jaws_ACL');
        $GLOBALS['app']->loadClass('Registry', 'Jaws_Registry');
        $GLOBALS['app']->loadClass('Translate', 'Jaws_Translate');
        $GLOBALS['app']->Registry->Init();

        // This is needed for most gadgets
        require_once JAWS_PATH . 'include/Jaws/Gadget.php';
        require_once JAWS_PATH . 'include/Jaws/Template.php';
        require_once JAWS_PATH . 'include/Jaws/Header.php';
        $GLOBALS['app']->loadClass('Map', 'Jaws_URLMapping');

        // Input datas
        $timestamp = $GLOBALS['db']->Date();

        // Registry keys
        $plugins = $GLOBALS['app']->Registry->Get('/plugins/parse_text/enabled_items');
        $GLOBALS['app']->Registry->Set('/plugins/parse_text/enabled_items', '');
        $GLOBALS['app']->Registry->NewKey('/plugins/parse_text/admin_enabled_items', $plugins);
        $GLOBALS['app']->Registry->NewKey('/config/global_website', 'true');
        $GLOBALS['app']->Registry->DeleteKey('/config/frontend_ajaxed');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/allowurl_items');
        $GLOBALS['app']->Registry->Set('/version', JAWS_VERSION);
        $GLOBALS['app']->Registry->Set('/last_update', $timestamp);

        // Commit the changes so they get saved
        $GLOBALS['app']->Registry->commit('core');

        // Trying to add missed acl keys
        _log(JAWS_LOG_DEBUG,"trying to add missed acl keys");
        $igadgets = $GLOBALS['app']->Registry->get('/gadgets/enabled_items');
        $igadgets.= ','. $GLOBALS['app']->Registry->get('/gadgets/core_items');
        $igadgets = array_filter(array_map('trim', explode(',', $igadgets)));
        foreach ($igadgets as $ig) {
            $GLOBALS['app']->ACL->NewKey("/ACL/gadgets/$ig/default", 'true');
            $GLOBALS['app']->ACL->NewKey("/ACL/gadgets/$ig/default_admin", 'false');
        }

        // Installing core gadgets
        $gadgets = array('UrlMapper', 'Layout', 'Users');
        foreach ($gadgets as $gadget) {
            $objGadget = $GLOBALS['app']->LoadGadget($gadget, 'Info');
            if (Jaws_Error::IsError($objGadget)) {
                _log(JAWS_LOG_DEBUG,"There was a problem installing core gadget: ".$gadget);
                return $objGadget;
            }

            $result = $objGadget->UpdateGadget();
            if (Jaws_Error::IsError($result)) {
                _log(JAWS_LOG_DEBUG,"There was a problem upgrading core gadget: $gadget");
                return $result;
            }
        }

        _log(JAWS_LOG_DEBUG,"Cleaning previous registry cache data files - step 0.8->0.9.0");
        //Make sure user don't have any data/cache/registry stuff
        $path = JAWS_DATA . 'cache/registry';
        if (!Jaws_Utils::Delete($path, false)) {
            _log(JAWS_LOG_DEBUG,"Can't delete $path");
        }

        _log(JAWS_LOG_DEBUG,"Cleaning previous acl cache data files - step 0.8->0.9.0");
        //Make sure user don't have any data/cache/acl stuff
        $path = JAWS_DATA . 'cache/acl';
        if (!Jaws_Utils::Delete($path, false)) {
            _log(JAWS_LOG_DEBUG,"Can't delete $path");
        }

        return true;
    }

}