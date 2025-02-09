<?php
/**
 * Policy Gadget - Autoload
 *
 * @category   GadgetAutoload
 * @package    Policy
 * @author     Amir Mohammad Saied <amir@gluegadget.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_Hooks_Autoload extends Jaws_Gadget_Hook
{
    /**
     * Autoload load method
     *
     */
    function Execute()
    {
        $this->RequestAccessible();
        $this->BlockIPHook();
        $this->BlockAgentHook();
    }

    /**
     * Block IP hook
     *
     * @access  private
     */
    function RequestAccessible()
    {
        if (!$this->app->session->user->superadmin ||
            !defined('JAWS_GODUSER') || JAWS_GODUSER !== $this->app->session->user->id
        ) {
            $addr = Jaws_Utils::GetRemoteAddress();
            $accessible = $this->gadget->model->load('IP')->IsReguestAccessible(
                ($addr['public']? $addr['client'] : $addr['proxy']),
                JAWS_SCRIPT == 'index'? 1 : 2,
                $this->app->mainRequest['gadget'],
                $this->app->mainRequest['action']
            );
            if (!is_null($accessible) && !$accessible) {
                echo Jaws_HTTPError::Get(403);
                exit;
            }
        }
    }

    /**
     * Block IP hook
     *
     * @access  private
     */
    function BlockIPHook()
    {
        if (!$this->app->session->user->superadmin) {
            $model = $this->gadget->model->load('IP');
            $res   = $model->IsIPBlocked($_SERVER['REMOTE_ADDR'], JAWS_SCRIPT);
            if ($res) {
                echo Jaws_HTTPError::Get(403);
                exit;
            }
        }
    }

    /**
     * Block Agent hook
     *
     * @access  private
     */
    function BlockAgentHook()
    {
        if (!$this->app->session->user->superadmin) {
            $model = $this->gadget->model->load('Agent');
            $res   = $model->IsAgentBlocked($_SERVER["HTTP_USER_AGENT"], JAWS_SCRIPT);
            if ($res) {
                echo Jaws_HTTPError::Get(403);
                exit;
            }
        }
    }
}
