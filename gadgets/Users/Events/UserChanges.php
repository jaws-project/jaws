<?php
/**
 * Activities event
 *
 * @category    Gadget
 * @package     Users
 */
class Users_Events_UserChanges extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     * @access  public
     * @param   string  $shouter    The shouting gadget
     * @param   array   $params     user attributes
     * @return  bool    True
     */
    function Execute($shouter, $params)
    {
        if (!isset($params['action']) || empty($params['action'])) {
            return false;
        }

        if ($params['action'] == 'AddUser') {
            // shout Activity event
            $this->gadget->event->shout('Activities', array('action' => 'AddUser'));
        }

        return true;
    }

}