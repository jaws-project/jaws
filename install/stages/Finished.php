<?php
/**
 * The finish page for the installer.
 *
 * @category    Application
 * @package     InstallStage
 * @author      Jon Wood <jon@substance-it.co.uk>
 * @copyright   2005-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Installer_Finished extends JawsInstallerStage
{
    /**
     * Builds the installer page.
     *
     * @access  public
     * @return  string      A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        $tpl = new Jaws_Template(false, false);
        $tpl->Load('display.html', 'stages/Finished/templates');
        $tpl->SetBlock('Finished');

        $base_url = Jaws_Utils::getBaseURL('', true);
        $tpl->setVariable('lbl_info',    _t('INSTALL_FINISH_INFO'));
        $tpl->setVariable('lbl_choices', _t('INSTALL_FINISH_CHOICES', "$base_url/", "$base_url/admin.php"));
        $tpl->setVariable('lbl_thanks',  _t('INSTALL_FINISH_THANKS'));
        $tpl->SetVariable('move_log',    _t('INSTALL_FINISH_MOVE_LOG'));

        $tpl->ParseBlock('Finished');

        // Kill the session
        session_destroy();

        return $tpl->Get();
    }
}