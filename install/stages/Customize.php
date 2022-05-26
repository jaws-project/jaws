<?php
/**
 * Customize Stage
 *
 * @category   Application
 * @package    InstallStage
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2014-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Installer_Customize extends JawsInstaller
{
    /**
     * Builds the installer page.
     *
     * @access  public
     * @return  string A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        $request = Jaws_Request::getInstance();
        $use_log = $request->fetch('use_log', 'post');
        //Set main session-log vars
        if (isset($use_log)) {
            $_SESSION['use_log'] = $use_log === 'yes'? JAWS_DEBUG : false;
        }
        _log(JAWS_DEBUG,"Generating new installation key");

        $tpl = new Jaws_Template(false, false);
        $tpl->Load('display.html', 'stages/Customize/templates');
        $tpl->SetBlock('Customize');

        $tpl->SetVariable('customize_info', $this::t('CUSTOMIZE_INFO'));

        $paths = array('data_path', 'base_data_path', 'themes_path', 'base_themes_path', 'cache_path');
        foreach ($paths as $path) {
            $upper_path = strtoupper($path);
            $tpl->SetVariable($path, constant($upper_path));
            $tpl->SetVariable("checked_$path", isset($_SESSION[$upper_path])? 'checked="checked"' : '');
            $tpl->SetVariable("disabled_$path", isset($_SESSION[$upper_path])? '' : 'disabled="disabled"');
        }

        $tpl->SetVariable('next', Jaws::t('NEXT'));
        $tpl->ParseBlock('Customize');
        return $tpl->Get();
    }

    /**
     * Does any actions required to finish the stage.
     *
     * @access  public
     * @return  bool|Jaws_Error  Either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function Run()
    {
        $paths = array('data_path', 'base_data_path', 'themes_path', 'base_themes_path', 'cache_path');
        $request = Jaws_Request::getInstance();
        $postedData = $request->fetch($paths, 'post');
        $postedData = array_filter($postedData);

        foreach ($paths as $path) {
            if (isset($postedData[$path])) {
                $_SESSION[strtoupper($path)] = $postedData[$path];
            } else {
                unset($_SESSION[strtoupper($path)]);
            }
        }

        return true;
    }

}