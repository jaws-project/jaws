<?php
/**
 * Friend Gadget
 *
 * @category   GadgetModel
 * @package    Friend
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FriendsModel extends Jaws_Model
{
    /**
     * Get information of a friend
     *
     * @access  public
     * @param   int     $id     Friend's ID
     * @return  mixed   An array of the information of the friend and Jaws_Error on any error
     */
    function GetFriend($id)
    {
        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params           = array();
        $params['id']     = $xss->parse($id);
        $sql = '
            SELECT
                [id], [friend], [url]
            FROM [[friend]]
            WHERE [id] = {id}';

        $row = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage(), 'SQL');
        }

        if (isset($row['friend'])) {
            return $row;
        }

        return new Jaws_Error(_t('FRIENDS_ERROR_FRIEND_DOES_NOT_EXISTS', $id));
    }

    /**
     * Get information of a friend by its name
     *
     * @access  public
     * @param   string  $name   Friend's name
     * @return  mixed   An array of the information of the friend and Jaws_Error on any error
     */
    function GetFriendByName($name)
    {
        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params           = array();
        $params['name']   = $xss->parse($name);

        $sql = '
            SELECT
                [id], [friend], [url]
            FROM [[friend]]
            WHERE [friend] = {name}';

        $row = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage(), 'SQL');
        }

        if (isset($row['friend'])) {
            return $row;
        }

        return new Jaws_Error(_t('FRIENDS_ERROR_FRIEND_DOES_NOT_EXISTS', $name));
    }

    /**
     * Get the list of friends
     *
     * @access  public
     * @param   int     $limit  data limit
     * @return  mixed   An array of friends and Jaws_Error on any error
     */
    function GetFriendsList($limit = 10)
    {
        $sql = '
            SELECT
                [id], [friend], [url]
            FROM [[friend]]
            ORDER BY [id] DESC';

        $result = $GLOBALS['db']->setLimit(10, $limit);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

    /**
     * Get the total of friends we have
     *
     * @access  public
     * @return  mixed   Total of friends we have or Jaws_Error on error
     */
    function TotalOfData()
    {
        $sql =
             'SELECT
               COUNT([id]) AS total
              FROM [[friend]]';

        $howMany = $GLOBALS['db']->queryOne($sql);

        return Jaws_Error::IsError($howMany) ? 0 : $howMany;
    }

    /**
     * Get a random list of friends limited bt
     *
     * @access  public
     * @return  mixed   An array of random friends and Jaws_Error on any error
     */
    function GetRandomFriends()
    {
        $GLOBALS['db']->dbc->loadModule('Function', null, true);
        $rand = $GLOBALS['db']->dbc->function->random();
        $sql = '
            SELECT
                [id], [friend], [url]
            FROM [[friend]]
            ORDER BY '. $rand;

        $limit = $GLOBALS['app']->Registry->Get('/gadgets/Friends/limit');
        if (Jaws_Error::IsError($limit) || !$limit) {
            $limit = 10;
        }

        $result = $GLOBALS['db']->setLimit($limit);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

}