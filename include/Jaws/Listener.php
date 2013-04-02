<?php
/**
 * Class that takes care of 'listening': creating the objects and
 * executing methods when an event occurs
 *
 * @category   Listener
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Listener
{
    /**
     * Add a new listener and saves it in the DB
     *
     * @access  public
     * @param   string  $gadget Gadget name that listens
     * @param   string  $event  Event name
     * @return  bool    True if listener was added, otherwise returns Jaws_Error
     */
    function AddListener($gadget, $event)
    {
        $params = array();
        $params['gadget'] = $gadget;
        $params['event']  = $event;

        $sql = '
            INSERT INTO [[listeners]]
                ([gadget], [event])
            VALUES
                ({gadget}, {event})';

        $res = $GLOBALS['db']->query($sql, $params);
        return $res;
    }

    /**
     * Shouts a call to the listener object that will act inmediatly.
     *
     * @access  public
     * @param   string  $event  Event name
     * @param   mixed   $params Event param(s)
     * @param   string  $gadget If set, returns listener result of this gadget
     * @return  mixed   True if successfully, otherwise returns Jaws_Error
     */
    function Shout($event, $params = array(), $gadget = '')
    {
        $listeners = $this->GetEventListeners($event);
        if (Jaws_Error::IsError($listeners)) {
            return $listeners;
        }

        $result = null;
        foreach ($listeners as $listener) {
            if (Jaws_Gadget::IsGadgetInstalled($listener['gadget'])) {
                $objGadget = $GLOBALS['app']->LoadGadget($listener['gadget'], 'Info');
                if (Jaws_Error::IsError($objGadget)) {
                    continue;
                }
                $objEvent = $objGadget->load('Event')->load($event);
                if (Jaws_Error::IsError($objEvent)) {
                    continue;
                }

                if (is_array($params)) {
                    $response = call_user_func_array(array($objEvent, 'Execute'), $params);
                } else {
                    $response = $objEvent->Execute($params);
                }

                // return listener result
                if ($gadget == $listener['gadget']) {
                    $result = $response;
                }
            }
        }

        return $result;
    }

    /**
     * Get listener information
     *
     * @access  public
     * @param   int     Listener ID
     * @return  array   An array with information of a listener or Jaws_Error on failure
     */
    function GetListener($id)
    {
        $sql = '
            SELECT
                [id], [gadget], [event]
            FROM [[listeners]]
            WHERE  [id] = {id}';

        $res = $GLOBALS['db']->queryAll($sql, array('id' => $id));
        return $res;
    }

    /**
     * Gets a list of all listener gadgets
     *
     * @access  public
     * @return  array   An array of all listener gadgets or Jaws_Error on failure
     */
    function GetListeners()
    {
        $sql = '
            SELECT
                [id], [gadget], [event]
            FROM [[listeners]]';

        $res = $GLOBALS['db']->queryRow($sql);
        return $res;
    }

    /**
     * Gets a list of all gadgets that are waiting an event
     *
     * @access  public
     * @param   string  $event  Event name
     * @return  array   An array of all gadgets that match an event or Jaws_Error on failure
     */
    function GetEventListeners($event)
    {
        $sql = '
            SELECT
                [id], [gadget]
            FROM [[listeners]]
            WHERE [event] = {event}';

        $res = $GLOBALS['db']->queryAll($sql, array('event' => $event));
        return $res;
    }

    /**
     * Deletes a shouter
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @param   string  $event   Event name
     * @return  bool    True if listener was deleted, otherwise returns Jaws_Error
     */
    function DeleteListener($gadget, $event = '')
    {
        $params = array();
        $params['gadget'] = $gadget;
        $params['event']  = $event;

        $sql = 'DELETE FROM [[listeners]] WHERE [gadget] = {gadget}';
        if (!empty($event)) {
            $sql .= ' AND [event] = {event}';
        }

        $res = $GLOBALS['db']->query($sql, $params);
        return $res;
    }

}