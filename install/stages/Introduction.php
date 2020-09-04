<?php
/**
 * The introduction page for the installer.
 *
 * @category   Application
 * @package    Install
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Installer_Introduction extends JawsInstaller
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
        $tpl->Load('display.html', 'stages/Introduction/templates');
        $tpl->SetBlock('Introduction');
        $tpl->SetVariable('welcome',    $this->t('INTRO_WELCOME'));
        $tpl->SetVariable('title_info', $this->t('INTRO_INSTALLER'));
        $tpl->SetVariable('db_info',    $this->t('INTRO_DATABASE'));
        $tpl->SetVariable('ftp_info',   $this->t('INTRO_FTP'));
        $tpl->SetVariable('mail_info',  $this->t('INTRO_MAIL'));
        $tpl->SetVariable('language',   Jaws::t('LANGUAGE'));
        $tpl->SetVariable('next',       Jaws::t('NEXT'));

        $langs = Jaws_Utils::GetLanguagesList();
        $selected_lang = isset($_SESSION['install']['language'])? $_SESSION['install']['language'] : 'en';
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