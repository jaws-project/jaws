<?php
/**
 * Jaws Upgrade Stage - From 0.9.2 to 0.9.3
 *
 * @category    Application
 * @package     UpgradeStage
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_092To093 extends JawsUpgraderStage
{
    /**
     * Builds the upgader page.
     *
     * @access  public
     * @return  string A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        $tpl = new Jaws_Template(false, false);
        $tpl->Load('display.html', 'stages/092To093/templates');
        $tpl->SetBlock('092To093');

        $tpl->setVariable('lbl_info',  _t('UPGRADE_VER_INFO', '0.9.2', '0.9.3'));
        $tpl->setVariable('lbl_notes', _t('UPGRADE_VER_NOTES'));
        $tpl->SetVariable('next',      _t('GLOBAL_NEXT'));

        $tpl->ParseBlock('092To093');
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
        return true;
    }

}