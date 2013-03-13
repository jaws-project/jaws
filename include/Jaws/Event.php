<?php
/**
 * Class that takes care of 'listening': creating the objects and
 * executing methods when an event occurs
 *
 * @category   Event
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Event
{
    /**
     * Add a new listener and saves it in the DB
     *
     * @access  public
     * @param   string  $gadget Gadget name that listens
     * @param   string  $event  Event name
     * @param   string  $method Gadget method that will be executed
     * @return  bool    True if listener was added, otherwise returns Jaws_Error
     */
    function AddListener($gadget, $event, $method)
    {
        $params = array();
        $params['gadget'] = $gadget;
        $params['event']  = $event;
        $params['method'] = $method;

        $sql = '
            INSERT INTO [[listeners]]
                ([gadget], [event], [method])
            VALUES
                ({gadget}, {event}, {method})';

        $res = $GLOBALS['db']->query($sql, $params);
        return $res;
    }

    /**
     * Shouts a call to the listener object that will act inmediatly.
     *
     * @access  public
     * @param   string  $event  Event name
     * @param   mixed   $params Event param(s)
     * @param   string  $gadget If set method return listener result of this gadget
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
                $gModel = $GLOBALS['app']->LoadGadget($listener['gadget'], 'AdminModel');
                if (Jaws_Error::IsError($gModel)) {
                    return $gModel;
                }

                if (method_exists($gModel, $listener['method'])) {
                    if (is_array($params)) {
                        $response = call_user_func_array(array($gModel, $listener['method']), $params);
                    } else {
                        $response = $gModel->$listener['method']($params);
                    }

                    // return listener result
                    if ($gadget == $listener['gadget']) {
                        $result = $response;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get information (which gadget is listening, which call is waiting form
     * and which method is going to be executed)
     *
     * @access  public
     * @param   int     Listener's ID
     * @return  array   An array with information of a listener or Jaws_Error on failure
     */
    function GetListener($id)
    {
        $sql = '
            SELECT
                [id], [gadget], [method], [event]
            FROM [[listeners]]
            WHERE  [id] = {id}';

        $res = $GLOBALS['db']->queryAll($sql, array('id' => $id));
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED', 'GetListeners'),
                                  __FUNCTION__);
        }

        return $res;
    }

    /**
     * Gets a list of all listener gadgets
     *
     * @access  public
     * @return  array   An array of all listener gadgets or Jaws_Error on failure
     */
    function GetListeners($call)
    {
        $sql = '
            SELECT
                [id], [gadget], [method], [event]
            FROM [[listeners]]';

        $res = $GLOBALS['db']->queryRow($sql);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED', 'GetListener'),
                                  __FUNCTION__);
        }

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
                [id], [gadget], [method]
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
     * @param   string  $method  Gadget method name
     * @return  bool    True if listener was deleted, otherwise returns Jaws_Error
     */
    function DeleteListener($gadget, $method = '')
    {
        $params = array();
        $params['gadget'] = $gadget;
        $params['method'] = $method;

        $sql = 'DELETE FROM [[listeners]] WHERE [gadget] = {gadget}';
        if (!empty($method)) {
            $sql .= ' AND [method] = {method}';
        }

        $res = $GLOBALS['db']->query($sql, $params);
        return $res;
    }

}