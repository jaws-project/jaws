<?php
/**
 * Jaws Upgrade Stage - From 1.1.0 to 1.1.1
 *
 * @category    Application
 * @package     UpgradeStage
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2014-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_110To111 extends JawsUpgrader
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
        $tpl->Load('display.html', 'stages/110To111/templates');
        $tpl->SetBlock('110To111');

        $tpl->setVariable('lbl_info',  $this->t('VER_INFO', '1.1.0', '1.1.1'));
        $tpl->setVariable('lbl_notes', $this->t('VER_NOTES'));
        $tpl->SetVariable('next',      Jaws::t('NEXT'));

        $tpl->ParseBlock('110To111');
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