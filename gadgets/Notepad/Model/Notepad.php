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
class Notepad_Model_Notepad extends Jaws_Gadget_Model
{
    /**
     * Fetches list of notes including shared notes
     *
     * @access  public
     * @param   int     $parent  Restricts results to a specified node
     * @return  array   Query result
     */
    function GetNotes($user = null, $shared = null, $foreign = null, $query = null)
    {
        $table = Jaws_ORM::getInstance()->table('notepad as note');
        $table->select('note.id', 'user as owner', 'title', 'content',
            'createtime', 'updatetime', 'users.id as user');
        $table->join('notepad_users', 'note.id', 'note_id');
        $table->join('users', 'user_id', 'users.id');

        if ($user !== null){
            $table->where('user_id', $user)->and();
        }

        // if ($shared !== null){
            // $table->where('shared', $shared)->and();
        // }

        // if ($foreign !== null){
            // $flag = $foreign? '<>' : '=';
            // $table->where('user', $table->expr('owner'), $flag)->and();
        // }

        if ($query !== null){
            $query = "%$query%";
            $table->openWhere('title', $query, 'like')->or();
            $table->closeWhere('content', $query, 'like');
        }

        return $table->orderBy('createtime desc', 'title asc')->fetchAll();
    }

    /**
     * Fetches data of passed note
     *
     * @access  public
     * @param   int     $id     Note ID
     * @param   int     $user   User ID
     * @return  mixed   Query result
     */
    function GetNote($id, $user = null)
    {
        $table = Jaws_ORM::getInstance()->table('notepad as note');
        $table->select('note.id', 'user as owner', 'title', 'content',
            'createtime', 'updatetime', 'users.id as user');
        $table->join('notepad_users', 'note.id', 'note_id');
        $table->join('users', 'user_id', 'users.id');
        $table->where('note.id', $id);
        if (!empty($user)) {
            $table->and()->where('user_id', $user);
        }
        return $table->fetchRow();
    }

    /**
     * Checks the user of passed notes
     *
     * @access  public
     * @param   int     $parent  Restricts results to a specified node
     * @return  array   Query result
     */
    function CheckNotes($id_set, $user)
    {
        $table = Jaws_ORM::getInstance()->table('notepad');
        $table->select('id');
        $table->where('id', $id_set, 'in')->and();
        $table->where('user', $user);
        return $table->fetchColumn();
    }

    /**
     * Inserts a new note
     *
     * @access  public
     * @param   array   $data   Note data
     * @return  mixed   Query result
     */
    function Insert($data)
    {
        $data['createtime'] = $data['updatetime'] = time();
        $table = Jaws_ORM::getInstance()->table('notepad');
        $id = $table->insert($data)->exec();

        $data = array(
            'note_id' => $id,
            'user_id' => $data['user']
        );
        $table = Jaws_ORM::getInstance()->table('notepad_users');
        return $table->insert($data)->exec();
    }

    /**
     * Updates note
     *
     * @access  public
     * @param   int     $id     Note ID
     * @param   array   $data   Note data
     * @return  mixed   Query result
     */
    function Update($id, $data)
    {
        $data['updatetime'] = time();
        $table = Jaws_ORM::getInstance()->table('notepad');
        return $table->update($data)->where('id', $id)->exec();
    }

    /**
     * Deletes note(s)
     *
     * @access  public
     * @param   array   $id_set  Set of Note IDs
     * @return  mixed   Query result
     */
    function Delete($id_set)
    {
        $table = Jaws_ORM::getInstance()->table('notepad');
        $res = $table->delete()->where('id', $id_set, 'in')->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        // Delete shares
        $table = Jaws_ORM::getInstance()->table('notepad_users');
        return $table->delete()->where('note_id', $id_set, 'in')->exec();
    }
}