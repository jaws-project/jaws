<?php
/**
 * Friend Gadget
 *
 * @category   GadgetModel
 * @package    Friend
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Friends_Model_Admin_Friends extends Jaws_Gadget_Model
{
    /**
     * Set properties of the gadget
     *
     * @access  public
     * @param   int     $limit  Limit
     * @return  mixed   True if change is successful, if not, returns Jaws_Error on any error
     */
    function UpdateProperties($limit)
    {
        $res = $this->gadget->registry->update('limit', $limit);
        if ($res || !Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FRIENDS_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
            return true;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FRIENDS_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
        return new Jaws_Error(_t('FRIENDS_ERROR_PROPERTIES_NOT_UPDATED'));
    }

    /**
     * Create a new Friend
     *
     * @access  public
     * @param   string  $friend Friend name
     * @param   string  $url    Friend's url
     * @return  mixed   True if query is successful, if not, returns Jaws_Error on any error
     */
    function NewFriend($friend, $url)
    {
        $params['friend'] = Jaws_XSS::filter($friend);
        $params['url']    = Jaws_XSS::filter($url);
        $friendTable = Jaws_ORM::getInstance()->table('friend');
        $result = $friendTable->insert($params)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FRIENDS_ERROR_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FRIENDS_ERROR_NOT_ADDED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FRIENDS_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Update Friend profile
     *
     * @access  public
     * @param   itn     $id         Friend's ID
     * @param   string  $friend     Friend's Name
     * @param   string  $url        Friend's Url
     * @return  mixed   True if query is successful, if not, returns Jaws_Error on any error
     */
    function UpdateFriend($id, $friend, $url)
    {
        $params['friend'] = $friend;
        $params['url']    = $url;

        $friendTable = Jaws_ORM::getInstance()->table('friend');
        $result = $friendTable->update($params)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FRIENDS_ERROR_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FRIENDS_ERROR_NOT_UPDATED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FRIENDS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Delete a friend from the DB
     *
     * @access  public
     * @param   itn     $id         Friend's ID
     * @return  mixed   True if query is successful, if not, returns Jaws_Error on any error
     */
    function DeleteFriend($id)
    {
        $friendTable = Jaws_ORM::getInstance()->table('friend');
        $result = $friendTable->delete()->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FRIENDS_ERROR_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FRIENDS_ERROR_NOT_UPDATED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FRIENDS_DELETED'), RESPONSE_NOTICE);
        return true;
    }
}