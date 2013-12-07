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
class Friends_Model_Friends extends Jaws_Gadget_Model
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
        $friendTable = Jaws_ORM::getInstance()->table('friend');
        $row = $friendTable->select('id:integer', 'friend', 'url')->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage());
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
        $friendTable = Jaws_ORM::getInstance()->table('friend');
        $row = $friendTable->select('id:integer', 'friend', 'url')->where('friend', $name)->fetchRow();
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage());
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
        $friendTable = Jaws_ORM::getInstance()->table('friend');
        $result = $friendTable->select('id:integer', 'friend', 'url')->orderBy('id desc')->limit(10, $limit)->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }

    /**
     * Get a random list of friends limited bt
     *
     * @access  public
     * @return  mixed   An array of random friends and Jaws_Error on any error
     */
    function GetRandomFriends()
    {
        $friendTable = Jaws_ORM::getInstance()->table('friend');
        $limit = $this->gadget->registry->fetch('limit');
        if (Jaws_Error::IsError($limit) || !$limit) {
            $limit = 10;
        }
        $friendTable->select('id:integer', 'friend', 'url')->orderBy($friendTable->random());
        $result = $friendTable->limit($limit)->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }
}