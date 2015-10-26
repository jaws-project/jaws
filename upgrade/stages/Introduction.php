<?php
/**
 * Introduction Stage
 *
 * @category   Application
 * @package    UpgradeStage
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_Introduction extends JawsUpgraderStage
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
        $tpl->Load('display.html', 'stages/Introduction/templates');
        $tpl->SetBlock('Introduction');
        $tpl->SetVariable('welcome',    _t('UPGRADE_INTRO_WELCOME'));
        $tpl->SetVariable('title_info', _t('UPGRADE_INTRO_UPGRADER'));
        $tpl->SetVariable('db_info',    _t('UPGRADE_INTRO_DATABASE'));
        $tpl->SetVariable('ftp_info',   _t('UPGRADE_INTRO_FTP'));
        $tpl->SetVariable('language',   _t('GLOBAL_LANGUAGE'));
        $tpl->SetVariable('next',       _t('GLOBAL_NEXT'));
        if (is_writable(JAWS_PATH . 'data/logs') && is_dir(JAWS_PATH . 'data/logs')) {
            $tpl->SetVariable('log_use', _t('UPGRADE_INTRO_LOG', 'data/logs/upgrade.txt'));
            $tpl->SetBlock('Introduction/logcheckbox');
            $tpl->ParseBlock('Introduction/logcheckbox');
        } else {
            $tpl->SetVariable('log_use', _t('UPGRADE_INTRO_LOG_ERROR', 'data/logs'));
        }

        $langs = Jaws_Utils::GetLanguagesList();
        $selected_lang = isset($_SESSION['upgrade']['language'])? $_SESSION['upgrade']['language'] : 'en';
        foreach ($langs as $code => $fullname) {
            $tpl->SetBlock('Introduction/lang');
            $tpl->SetVariable('selected', $code == $selected_lang? 'selected="selected"': '');
            $tpl->SetVariable('code', $code);
            $tpl->SetVariable('fullname', $fullname);
            $tpl->ParseBlock('Introduction/lang');
        }

        $tpl->ParseBlock('Introduction');
        return $tpl->Get();
    }
}