<?php
/**
 * Main methods of Ajax services
 *
 * @category   Ajax
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Ajax
{
    /**
     * Model
     *
     * @access  private
     * @var     Jaws_Model
     */
    var $_Model;

    /**
     * Constructor
     *
     * @access  public
     * @param   object  $model  Jaws_Model reference
     * @return  void
     */
    function Jaws_Ajax(&$model)
    {
        $this->_Model =& $model;
    }

    /**
     * Check the session permission:
     *
     *  - If user has privileges to execute the task
     *  - If session object exists
     *  - If session stills active
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $task       Task name
     * @param   bool    $together   And/Or tasks permission result, default true
     */
    function CheckSession($gadget, $task, $together = true)
    {
        $this->CheckSessionExistence();
        $this->CheckSessionLife();
        if (!$GLOBALS['app']->Session->GetPermission($gadget, $task, $together)) {
            trigger_error('[NOPERMISSION] - You do not have permission to execute this task', E_USER_ERROR);
        }
    }

    /**
     * Get the session permission:
     *
     *  - If user has privileges to execute the task
     *  - If session object exists
     *  - If session stills active
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $task       Task name
     * @param   bool    $together   And/Or tasks permission result, default true
     */
    function GetPermission($gadget, $task, $together = true)
    {
        return (
            $this->GetSessionExistence() &&
            $this->IsSessionAlive() &&
            $GLOBALS['app']->Session->GetPermission($gadget, $task, $together)
        );
    }

    /**
     * Check if session object exists
     *
     * @access   private
     */
    function CheckSessionExistence()
    {
        if (!isset($GLOBALS['app']->Session)) {
            trigger_error('[NOSESSION] - Session does not exists', E_USER_ERROR);
        }
    }

    /**
     * Gets the existence of the session status
     *
     * @access   private
     * @return  bool
     */
    function GetSessionExistence()
    {
        return isset($GLOBALS['app']->Session) ? true : false;
    }

    /**
     * Check if session stills active
     *
     * @access  private
     */
    function CheckSessionLife()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            trigger_error('[NOTLOGGED] - User not logged', E_USER_ERROR);
        }
    }

    /**
     * Gets the session status
     *
     * @access  private
     * @return  bool
     */
    function IsSessionAlive()
    {
        return $GLOBALS['app']->Session->Logged() ? true : false;
    }

}