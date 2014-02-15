<?php
/**
 * Jaws Upgrade Stage - From 1.1.1 to 1.2.0
 *
 * @category    Application
 * @package     UpgradeStage
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_111To120 extends JawsUpgraderStage
{
    /**
     * Builds the upgrader page.
     *
     * @access  public
     * @return  string A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        $tpl = new Jaws_Template(false);
        $tpl->Load('display.html', 'stages/111To120/templates');
        $tpl->SetBlock('111To120');

        $tpl->setVariable('lbl_info',  _t('UPGRADE_VER_INFO', '1.1.1', '1.2.0'));
        $tpl->setVariable('lbl_notes', _t('UPGRADE_VER_NOTES'));
        $tpl->SetVariable('next',      _t('GLOBAL_NEXT'));

        $tpl->ParseBlock('111To120');
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

        // replace requires key name with requirement key name
        $tblReg = Jaws_ORM::getInstance()->table('registry');
        $result = $tblReg->update(array('key_name' => 'requirement'))->where('key_name', 'requires')->exec();
        if (Jaws_Error::isError($result)) {
            _log(JAWS_LOG_ERROR, $result->getMessage());
            return $result;
        }

        return true;
    }

}