<?php
/**
 * Launcher Gadget
 *
 * @category   GadgetModel
 * @package    Launcher
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Launcher_Model_Scripts extends Jaws_Gadget_Model
{
    /**
     * Get all scripts
     *
     * @access  public
     * @return  array   An array of all the scripts
     */
    function GetScripts()
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