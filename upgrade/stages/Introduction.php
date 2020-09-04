<?php
/**
 * Introduction Stage
 *
 * @category   Application
 * @package    UpgradeStage
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @copyright  2005-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Upgrader_Introduction extends JawsUpgrader
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
        $tpl->SetVariable('welcome',    $this->t('INTRO_WELCOME'));
        $tpl->SetVariable('title_info', $this->t('INTRO_UPGRADER'));
        $tpl->SetVariable('db_info',    $this->t('INTRO_DATABASE'));
        $tpl->SetVariable('ftp_info',   $this->t('INTRO_FTP'));
        $tpl->SetVariable('language',   Jaws::t('LANGUAGE'));
        $tpl->SetVariable('next',       Jaws::t('NEXT'));
        if (is_writable(ROOT_JAWS_PATH . 'data/logs') && is_dir(ROOT_JAWS_PATH . 'data/logs')) {
            $tpl->SetVariable('log_use', $this->t('INTRO_LOG', 'data/logs/upgrade.txt'));
            $tpl->SetBlock('Introduction/logcheckbox');
            $tpl->ParseBlock('Introduction/logcheckbox');
        } else {
            $tpl->SetVariable('log_use', $this->t('INTRO_LOG_ERROR', 'data/logs'));
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