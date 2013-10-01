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
        // $access = ($user === null)? null : $this->CheckAccess($parent, $user);
        // if ($access === false) {
            // return array();
        // }

        $table = Jaws_ORM::getInstance()->table('notepad as note');
        $table->select('note.id', 'user', 'title', 'content',
            'createtime', 'updatetime', 'users.username as owner');
        $table->join('users', 'user', 'users.id');

        if ($user !== null){
            $table->where('user', $user)->and();
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
     * @param   int     $id  Note ID
     * @return  mixed   Query result
     */
    function GetNote($id)
    {
        $table = Jaws_ORM::getInstance()->table('notepad as note');
        $table->select('note.id', 'user', 'title', 'content',
            'createtime', 'updatetime', 'users.username as owner');
        $table->join('users', 'user', 'users.id');
        return $table->where('note.id', $id)->fetchRow();
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
     * Deletes note
     *
     * @access  public
     * @param   int     $id  Note ID
     * @return  mixed   Query result
     */
    function Delete($id)
    {
        $table = Jaws_ORM::getInstance()->table('notepad');
        return $table->delete()->where('id', $id)->exec();
    }
}