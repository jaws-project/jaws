<?php
/**
 * Notepad Gadget
 *
 * @category    GadgetModel
 * @package     Notepad
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Notepad_Model_StickyNote extends Jaws_Gadget_Model
{
    /**
     * Fetches list of latest created notes
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  array   Query result
     */
    function GetLatestNotes($user = null, $limit = 1, $shares = null)
    {
        $table = Jaws_ORM::getInstance()->table('notepad as note');
        $table->select('note.id', 'user', 'title', 'content', 'shared',
            'createtime', 'updatetime', 'nickname', 'username');
        $table->join('notepad_users', 'note.id', 'note_id');
        $table->join('users', 'owner_id', 'users.id');

        if ($user !== null){
            $table->and()->where('user_id', $user);
        }

        if ($shares === false){
            $table->and()->where('user', $user);
        }

        $table->orderBy('createtime desc', 'title asc');
        return $table->limit($limit)->fetchAll();
    }
}