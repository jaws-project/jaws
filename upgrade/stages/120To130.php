<?php
/**
 * Jaws Upgrade Stage - From 1.2.0 to 1.3.0
 *
 * @category    Application
 * @package     UpgradeStage
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_120To130 extends JawsUpgraderStage
{
    /**
     * Builds the upgrader page.
     *
     * @access  public
     * @return  string A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        $tpl = new Jaws_Template(false, false);
        $tpl->Load('display.html', 'stages/120To130/templates');
        $tpl->SetBlock('120To130');

        $tpl->setVariable('lbl_info',  _t('UPGRADE_VER_INFO', '1.2.0', '1.3.0'));
        $tpl->setVariable('lbl_notes', _t('UPGRADE_VER_NOTES'));
        $tpl->SetVariable('next',      _t('GLOBAL_NEXT'));

        $tpl->ParseBlock('120To130');
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
        $objDatabase = Jaws_DB::getInstance('default', $_SESSION['upgrade']['Database']);
        if (Jaws_Error::IsError($objDatabase)) {
            _log(JAWS_LOG_DEBUG,"There was a problem connecting to the database, please check the details and try again");
            return new Jaws_Error(_t('UPGRADE_DB_RESPONSE_CONNECT_FAILED'), 0, JAWS_ERROR_WARNING);
        }

        // upgrade core database schema
        $old_schema = JAWS_PATH . 'upgrade/Resources/schema/1.2.0.xml';
        $new_schema = JAWS_PATH . 'upgrade/Resources/schema/schema.xml';
        if (!file_exists($old_schema)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', '1.2.0.xml'),0 , JAWS_ERROR_ERROR);
        }

        if (!file_exists($new_schema)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', 'schema.xml'),0 , JAWS_ERROR_ERROR);
        }

        _log(JAWS_LOG_DEBUG,"Upgrading core schema");
        $result = Jaws_DB::getInstance()->installSchema($new_schema, array(), $old_schema);
        if (Jaws_Error::isError($result)) {
            _log(JAWS_LOG_ERROR, $result->getMessage());
            if ($result->getCode() !== MDB2_ERROR_ALREADY_EXISTS) {
                return new Jaws_Error($result->getMessage(), 0, JAWS_ERROR_ERROR);
            }
        }

        // Create application
        include_once JAWS_PATH . 'include/Jaws.php';
        $GLOBALS['app'] = jaws();
        $GLOBALS['app']->Registry->Init();

        // Upgrading core gadgets
        $gadgets = array('UrlMapper', 'Settings', 'Policy', 'Users');
        foreach ($gadgets as $gadget) {
            $objGadget = Jaws_Gadget::getInstance($gadget);
            if (Jaws_Error::IsError($objGadget)) {
                _log(JAWS_LOG_DEBUG,"There was a problem loading core gadget: ".$gadget);
                return $objGadget;
            }

            $installer = $objGadget->installer->load();
            if (Jaws_Error::IsError($installer)) {
                _log(JAWS_LOG_DEBUG,"There was a problem loading installer of core gadget: $gadget");
                return $installer;
            }

            if (Jaws_Gadget::IsGadgetInstalled($gadget)) {
                $result = $installer->UpgradeGadget();
            } else {
                continue;
                //$result = $installer->InstallGadget();
            }

            if (Jaws_Error::IsError($result)) {
                _log(JAWS_LOG_DEBUG,"There was a problem installing/upgrading core gadget: $gadget");
                return $result;
            }
        }

        return true;
    }

}