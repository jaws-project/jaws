<?php
/**
 * Policy Gadget - Autoload
 *
 * @category   GadgetAutoload
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class PolicyAutoload
{
    /**
     * Autoload load method
     *
     */
    function Execute()
    {
        $this->BlockIPHook();
        $this->BlockAgentHook();
    }

    /**
     * Block IP hook
     *
     * @access  private
     */
    function BlockIPHook()
    {
        $model  = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        $res    = $model->IsIPBlocked($_SERVER['REMOTE_ADDR']);
        if ($res) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            echo Jaws_HTTPError::Get(403);
            exit;
        }
    }

    /**
     * Block Agent hook
     *
     * @access  private
     */
    function BlockAgentHook()
    {
        $model = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        $res   = $model->IsAgentBlocked($_SERVER["HTTP_USER_AGENT"]);
        if ($res) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            echo Jaws_HTTPError::Get(403);
            exit;
        }
    }
}
