<?php
/**
 * Customize Stage
 *
 * @category   Application
 * @package    InstallStage
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Installer_Customize extends JawsInstallerStage
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
            $_SESSION['use_log'] = $use_log === 'yes'? JAWS_LOG_DEBUG : false;
        }
        _log(JAWS_LOG_DEBUG,"Generating new installation key");

        $tpl = new Jaws_Template(false);
        $tpl->Load('display.html', 'stages/Customize/templates');
        $tpl->SetBlock('Customize');

        $tpl->SetVariable('customize_info', _t('INSTALL_CUSTOMIZE_INFO'));

        $paths = array('jaws_data', 'jaws_base_data', 'jaws_themes', 'jaws_base_themes', 'jaws_cache');
        foreach ($paths as $path) {
            $upper_path = strtoupper($path);
            $tpl->SetVariable($path, constant($upper_path));
            $tpl->SetVariable("checked_$path", isset($_SESSION[$upper_path])? 'checked="checked"' : '');
            $tpl->SetVariable("disabled_$path", isset($_SESSION[$upper_path])? '' : 'disabled="disabled"');
        }

        $tpl->SetVariable('next', _t('GLOBAL_NEXT'));
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
        $paths = array('jaws_data', 'jaws_base_data', 'jaws_themes', 'jaws_base_themes', 'jaws_cache');
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