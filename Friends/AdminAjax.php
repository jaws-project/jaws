<?php
/**
 * Friends AJAX API
 *
 * @category   Ajax
 * @package    Friend
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Friends_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Friends_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->loadModel('AdminModel');
    }

    /**
     * Get information of a friend
     *
     * @access  public
     * @param   string  $friend     Friend's name
     * @return  mixed   Friend information or False on error
     */
    function GetFriend($friend)
    {
        $friendInfo = $this->_Model->GetFriend($friend);
        if (Jaws_Error::IsError($friendInfo)) {
            return false; //we need to handle errors on ajax
        } else {
            return $friendInfo;
        }
    }

    /**
     * Add a friend
     *
     * @access  public
     * @param   string  $friend  Friend's name
     * @param   string  $url     Friend's URL
     * @return  array   Response array (notice or error)
     */
    function NewFriend($friend, $url)
    {
        $this->gadget->CheckPermission('AddFriend');
        $this->_Model->NewFriend($friend, $url);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update friend's information
     *
     * @access  public
     * @param   string  $old     Friend's OLD name
     * @param   string  $friend  Friend's name
     * @param   string  $url     Friend's URL
     * @return  array   Response array (notice or error)
     */
    function UpdateFriend($old, $friend, $url)
    {
        $this->gadget->CheckPermission('EditFriend');
        $this->_Model->UpdateFriend($old, $friend, $url);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Delete a friend
     *
     * @access  public
     * @param   string  $friend  Friend's name
     * @return  array   Response array (notice or error)
     */
    function DeleteFriend($friend)
    {
        $this->gadget->CheckPermission('DeleteFriend');
        $this->_Model->DeleteFriend($friend);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update the properties
     *
     * @access  public
     * @param   int     $limit  Limit random
     * @return  array   Response array
     */
    function UpdateProperties($limit)
    {
        $this->gadget->CheckPermission('UpdateProperties');
        $this->_Model->UpdateProperties($limit);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Get data from DB
     *
     * @access  public
     * @param   int     $limit  limit data
     * @return  array   data array
     */
    function GetData($limit = 0)
    {
        $gadget = $GLOBALS['app']->LoadGadget('Friends', 'AdminHTML');
        if (!is_numeric($limit)) {
            $limit = 0;
        }
        return $gadget->GetFriends($limit);
    }

}
