<?php
/**
 * Launcher Gadget
 *
 * @category   Gadget
 * @package    Launcher
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LauncherHTML extends Jaws_GadgetHTML
{
    /**
     * Calls default action(display)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DefaultAction()
    {
        return $this->DisplayMain('defaultscript');
    }
    
    /**
     * Show a Launcher
     *
     * @access  public
     * @param   string  $script     script name (optional)
     * @return  string  Script content
     */
    function DisplayMain($script = null)
    {
        $request =& Jaws_Request::getInstance();
        if (is_null($script)) {
            $script = $request->get('script', 'get');
        }
        $model  = $GLOBALS['app']->LoadGadget('Launcher', 'Model');
        $params = $request->get('params', 'get');
        $html   = $model->GetLauncher($script, $params);
        if (Jaws_Error::IsError($html)) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(404);
        }
        return $html;
    }

}