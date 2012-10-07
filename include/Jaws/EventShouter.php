<?php
/**
 * Class that takes care of 'shouting' events to the Jaws environment so
 * other gadgets can 'hear' it. The event stuff can be something like
 * Tarzan, where EventShouter (this class) is Tarzan and the EventListener
 * are his monkey friends...
 *
 * @category   Event
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_EventShouter
{
    /**
     * Creates a new shouter and saves it in the DB
     *
     * @access  public
     * @param   string  $gadget  Gadget name that shouts
     * @param   string  $call    Call name
     * @return  boolean True if shouter was added, otherwise returns Jaws_Error
     */
    function NewShouter($gadget, $call)
    {
        $params = array();
        $params['gadget'] = $gadget;
        $params['call']   = $call;

        $sql = '
            INSERT INTO [[shouters]]
                ([gadget], [event])
            VALUES
                ({gadget}, {call})';

        $rs = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($rs)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_EVENTS_NOT_ADDED'),
                                     __FUNCTION__);
        }

        return true;
    }

    /**
     * Deletes a shouter
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @param   string  $call    Call name
     * @return  boolean True if shouter was deleted, otherwise returns Jaws_Error
     */
    function DeleteShouter($gadget, $call)
    {
        $params = array();
        $params['gadget'] = $gadget;
        $params['call']   = $call;

        $sql = '
            DELETE FROM [[shouters]]
            WHERE
                [gadget] = {gadget}
              AND
                [event] = {call}';

        $rs = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($rs)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_EVENTS_NOT_DELETED'),
                                     __FUNCTION__);
        }

        return true;
    }

    /**
     * Shouts a call to the listener object that will act inmediatly.
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @param   mixed   $param   Param that is send to the listener, can be a
     *                           string, int, array, object, etc.
     * @return  boolean True if shouter didn't returned a Jaws_Error, otherwise returns Jaws_Error
     */
    function Shout($call, $param)
    {
        $GLOBALS['app']->loadClass('Listener', 'Jaws_EventListener');
        $res = $GLOBALS['app']->Listener->Listen($call, $param);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_EVENTS_LISTENER_ERROR'),
                                     __FUNCTION__);
        }

        return $res;
    }

    /**
     * Get information of a shouter (which gadget is shouting and the shout call)
     *
     * @access  public
     * @param   int     Shouter's ID
     * @return  array   An array with information of a shouter or Jaws_Error on failure
     */
    function GetShouter($id)
    {
        $sql = '
            SELECT
                [gadget], [event]
            FROM [[shouters]]
            WHERE [id] = {id}';

        $res = $GLOBALS['db']->queryAll($sql, array('id' => $id));
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED', 'GetShouter'),
                                     __FUNCTION__);
        }

        return $res;
    }

    /**
     * Gets a list of all shouter gadgets
     *
     * @access  public
     * @return  array   An array of all shouter gadgets or Jaws_Error on failure
     */
    function GetShouters()
    {
        $sql = '
            SELECT
                [id], [gadget], [event]
            FROM [[shouters]]';

        $res = $GLOBALS['db']->queryRow($sql);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED', 'GetShouters'),
                                     __FUNCTION__);
        }

        return $res;
    }
}