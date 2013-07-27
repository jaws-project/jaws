<?php
/**
 * Jaws Upgrade Stage - From 0.8 to 0.9.0
 *
 * @category   Application
 * @package    UpgradeStage
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_08To0903 extends JawsUpgraderStage
{
    /**
     * Builds the upgader page.
     *
     * @access  public
     * @return  string A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        $tpl = new Jaws_Template(false);
        $tpl->Load('display.html', 'stages/08To0903/templates');
        $tpl->SetBlock('08To0903');

        $tpl->setVariable('lbl_info',  _t('UPGRADE_VER_INFO', '0.8', '0.9.0'));
        $tpl->setVariable('lbl_notes', _t('UPGRADE_VER_NOTES'));
        $tpl->SetVariable('next',      _t('GLOBAL_NEXT'));

        $tpl->ParseBlock('08To0903');
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

        // Create application
        include_once JAWS_PATH . 'include/Jaws.php';
        $GLOBALS['app'] = new Jaws();
        $GLOBALS['app']->Registry->Init();

        // Input datas
        $timestamp = $GLOBALS['db']->Date();

        // Registry keys
        $GLOBALS['app']->Registry->update('version', JAWS_VERSION);

        // Trying to add missed acl keys
        _log(JAWS_LOG_DEBUG,"trying to add missed acl keys");
        $installed_gadgets = $GLOBALS['app']->Registry->fetch('gadgets_installed_items');
        $igadgets = array_filter(array_map('trim', explode(',', $installed_gadgets)));
        foreach ($igadgets as $ig) {
            $GLOBALS['app']->ACL->insert('default', '', 1, $ig);
            $GLOBALS['app']->ACL->insert('default_admin', '', 0, $ig);
        }

        // Upgrading core gadgets
        $gadgets = array('UrlMapper', 'Settings', 'Layout', 'Users', 'Policy',/* 'Comments'*/);
        foreach ($gadgets as $gadget) {
            $objGadget = $GLOBALS['app']->LoadGadget($gadget, 'Info');
            if (Jaws_Error::IsError($objGadget)) {
                _log(JAWS_LOG_DEBUG,"There was a problem loading core gadget: ".$gadget);
                return $objGadget;
            }

            $installer = $objGadget->load('Installer');
            if (Jaws_Error::IsError($installer)) {
                _log(JAWS_LOG_DEBUG,"There was a problem loading installer of core gadget: $gadget");
                return $installer;
            }

            if (Jaws_Gadget::IsGadgetInstalled($gadget)) {
                $result = $installer->UpgradeGadget();
            } else {
                if ($gadget == 'Comments') {
                    $result = $installer->InstallGadget(true);
                } else {
                    $result = $installer->InstallGadget();
                }
            }

            if (Jaws_Error::IsError($result)) {
                _log(JAWS_LOG_DEBUG,"There was a problem installing/upgrading core gadget: $gadget");
                return $result;
            }
        }

        return true;
    }

}