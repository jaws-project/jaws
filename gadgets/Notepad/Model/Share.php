<?php
/**
 * Notepad Gadget
 *
 * @category    GadgetModel
 * @package     Notepad
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Notepad_Model_Share extends Jaws_Gadget_Model
{
    /**
     * Fetches list of users the note is shared for
     *
     * @access  public
     * @param   int     $id  Note ID
     * @return  mixed   Query result
     */
    function GetNoteUsers($id)
    {
        $table = Jaws_ORM::getInstance()->table('notepad_users');
        $table->select('user_id', 'username', 'nickname');
        $table->join('users', 'user_id', 'users.id');
        $table->where('note_id', $id);
        return $table->fetchAll();
    }

    /**
     * Updates users of a note
     *
     * @access  public
     * @param   int     $id     Note ID
     * @param   array   $users  Set of User IDs
     * @return  mixed   True or Jaws_Error
     */
    function UpdateNoteUsers($id, $users)
    {
        // Delete current users except owner
        $uid = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $table = Jaws_ORM::getInstance()->table('notepad_users');
        $table->delete()->where('note_id', $id)->and();
        $res = $table->where('user_id', $uid, '<>')->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }
        if (empty($users)) {
            return $res;
        }

        // Insert users
        foreach ($users as &$user) {
            $user = array('note_id' => $id, 'user_id' => $user);
        }
        $table = Jaws_ORM::getInstance()->table('notepad_users');
        $table->insertAll(array('note_id', 'user_id'), $users);
        return $table->exec();
    }
}