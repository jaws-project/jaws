<?php
/**
 * Jaws Upgrade Stage - From 1.6.0 to 1.7.0
 *
 * @category    Application
 * @package     UpgradeStage
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_160To170 extends JawsUpgrader
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
        $tpl->Load('display.html', 'stages/160To170/templates');
        $tpl->SetBlock('160To170');

        $tpl->setVariable('lbl_info',  $this::t('VER_INFO', '1.6.0', '1.7.0'));
        $tpl->setVariable('lbl_notes', $this::t('VER_NOTES'));
        $tpl->SetVariable('next',      Jaws::t('NEXT'));

        $tpl->ParseBlock('160To170');
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
        require_once ROOT_JAWS_PATH . 'include/Jaws/DB.php';
        $objDatabase = Jaws_DB::getInstance('default', $_SESSION['upgrade']['Database']);
        if (Jaws_Error::IsError($objDatabase)) {
            _log(
                JAWS_DEBUG,
                "There was a problem connecting to the database, please check the details and try again"
            );
            return new Jaws_Error($this::t('DB_RESPONSE_CONNECT_FAILED'), 0, JAWS_ERROR_WARNING);
        }

        // delete old sessions
        $result = Jaws_ORM::getInstance()->table('session')->delete()->exec();
        if (Jaws_Error::IsError($result)) {
            _log(JAWS_DEBUG, $result->getMessage());
            // do nothing
        }

        $schema_array = array(
            '1.6.0' => '1.6.1',
            '1.6.1' => '1.6.2',
            '1.6.2' => '1.6.3',
            '1.6.3' => 'schema'
        );
        foreach ($schema_array as $old => $new) {
            // upgrade core database schema
            $old_schema = ROOT_JAWS_PATH . "upgrade/Resources/schema/$old.xml";
            $new_schema = ROOT_JAWS_PATH . "upgrade/Resources/schema/$new.xml";
            if (!Jaws_FileManagement_File::file_exists($old_schema)) {
                return new Jaws_Error(Jaws::t('ERROR_SQLFILE_NOT_EXISTS', "$old.xml"),0 , JAWS_ERROR_ERROR);
            }

            if (!Jaws_FileManagement_File::file_exists($new_schema)) {
                return new Jaws_Error(Jaws::t('ERROR_SQLFILE_NOT_EXISTS', "$new.xml"),0 , JAWS_ERROR_ERROR);
            }

            _log(JAWS_DEBUG,"Upgrading core schema");
            $result = Jaws_DB::getInstance()->installSchema($new_schema, array(), $old_schema);
            if (Jaws_Error::isError($result)) {
                _log(JAWS_ERROR, $result->getMessage());
                if ($result->getCode() !== MDB2_ERROR_ALREADY_EXISTS) {
                    return new Jaws_Error($result->getMessage(), 0, JAWS_ERROR_ERROR);
                }
            }
        }

        // Create application
        include_once ROOT_JAWS_PATH . 'include/Jaws.php';
        Jaws::getInstance()->registry->init();

        // Upgrading core gadgets
        $gadgets = array('Settings', 'Users');
        foreach ($gadgets as $gadget) {
            $objGadget = Jaws_Gadget::getInstance($gadget);
            if (Jaws_Error::IsError($objGadget)) {
                _log(JAWS_DEBUG,"There was a problem loading core gadget: ".$gadget);
                return $objGadget;
            }

            $installer = $objGadget->installer->load();
            if (Jaws_Error::IsError($installer)) {
                _log(JAWS_DEBUG,"There was a problem loading installer of core gadget: $gadget");
                return $installer;
            }

            if (Jaws_Gadget::IsGadgetInstalled($gadget)) {
                $result = $installer->UpgradeGadget();
            } else {
                continue;
                //$result = $installer->InstallGadget();
            }

            if (Jaws_Error::IsError($result)) {
                _log(JAWS_DEBUG,"There was a problem installing/upgrading core gadget: $gadget");
                return $result;
            }
        }

        return true;
    }

}