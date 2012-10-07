<?php
/**
 * Launcher Gadget
 *
 * @category   GadgetModel
 * @package    Launcher
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LauncherModel extends Jaws_Model
{
    /**
     * Get the execution results of a given script
     *
     * @access  public
     * @param   string  $script     Script name
     * @return  string  Result of the script execution
     */
    function GetLauncher($script, $params)
    {
        // Check if script exists
        if (file_exists(JAWS_PATH . 'gadgets/Launcher/scripts/'. $script . '.php')) {
            require_once JAWS_PATH . 'gadgets/Launcher/scripts/'. $script . '.php';
        } else {
            return new Jaws_Error(_t('LAUNCHER_ERROR_SCRIPT_NOT_EXISTS', $script));
        }

        // Check if function exists and return its execution result.
        if (function_exists($script)) {
            return call_user_func($script, $params);
        } else {
            return new Jaws_Error(_t('LAUNCHER_ERROR_FUNCTION_NOT_EXISTS', $script));
        }
    }


    /**
     * Get all scripts
     *
     * @access  public
     * @return  array   An array of all the scripts
     */
    function GetLaunchers()
    {
        $result = array();
        $path = JAWS_PATH . 'gadgets/Launcher/scripts/';
        $adr = scandir($path);
        foreach($adr as $file) {
            if (substr($file, -4) == '.php') {
                $result[$file] = substr($file, 0, -4);
            }
        }
        sort($result);
        return $result;
    }
}