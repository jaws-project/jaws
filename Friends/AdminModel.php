<?php
require_once JAWS_PATH . 'gadgets/Friends/Model.php';
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
class Friends_AdminModel extends Friends_Model
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
        $res = $this->gadget->SetRegistry('limit', $limit);
        if ($res || !Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FRIENDS_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
            return true;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FRIENDS_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
        return new Jaws_Error(_t('FRIENDS_ERROR_PROPERTIES_NOT_UPDATED'), _t('FRIENDS_NAME'));
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
        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params           = array();
        $params['friend'] = $xss->filter($friend, true);
        $params['url']    = $xss->filter($url, true);

        $sql = '
            INSERT INTO [[friend]]
                ([friend], [url])
            VALUES
                ({friend}, {url})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FRIENDS_ERROR_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FRIENDS_ERROR_NOT_ADDED'), _t('FRIENDS_NAME'));
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
        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params = array();
        $params['friend'] = $friend;
        $params['url']    = $url;
        $params['id']     = $id;

        $sql = '
            UPDATE [[friend]] SET
                [friend] = {friend},
                [url] = {url}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FRIENDS_ERROR_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FRIENDS_ERROR_NOT_UPDATED'), _t('FRIENDS_NAME'));
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
        $params       = array();
        $params['id'] = $id;
        $sql = 'DELETE FROM [[friend]] WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FRIENDS_ERROR_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FRIENDS_ERROR_NOT_UPDATED'), _t('FRIENDS_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FRIENDS_DELETED'), RESPONSE_NOTICE);
        return true;
    }

}