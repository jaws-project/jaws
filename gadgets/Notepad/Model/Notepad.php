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
     * Fetches data of a file or directory
     *
     * @access  public
     * @param   int     $id  File ID
     * @return  mixed   Array of file data or Jaws_Error on error
     */
    function GetFile($id)
    {
        $table = Jaws_ORM::getInstance()->table('directory as dir');
        // $table->select('dir.id', 'parent', 'user', 'is_dir:boolean', 'title',
            // 'description', 'filename', 'filetype', 'filesize', 'dir.url', 'shared:boolean',
            // 'dir.public:boolean', 'owner', 'reference', 'createtime', 'updatetime', 'users.username');
        // $table->join('users', 'owner', 'users.id');
        $table->select('id', 'parent', 'user', 'is_dir:boolean', 'title',
            'description', 'filename', 'filetype', 'filesize', 'url', 'shared:boolean',
            'public:boolean', 'owner', 'reference', 'createtime', 'updatetime');
        return $table->where('dir.id', $id)->fetchRow();
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
     * Updates file/directory
     *
     * @access  public
     * @param   int     $id     File ID
     * @param   array   $data   File data
     * @return  mixed   Query result
     */
    function Update($id, $data)
    {
        $table = Jaws_ORM::getInstance()->table('directory');
        return $table->update($data)->where('id', $id)->exec();
    }

    /**
     * Deletes file/directory
     *
     * @access  public
     * @param   int     $id  File ID
     * @return  mixed   Query result
     */
    function Delete($data)
    {
        if ($data['is_dir']) {
            $files = $this->GetFiles($data['id'], $data['user']);
            if (Jaws_Error::IsError($files)) {
                return false;
            }
            foreach ($files as $file) {
                $this->Delete($file);
            }
        }

        // Delete file/folder and related shortcuts
        $table = Jaws_ORM::getInstance()->table('directory');
        $table->delete()->where('id', $data['id']);
        $table->or()->where('reference', $data['id']);
        $res = $table->exec();
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        // Delete from disk
        if (!$data['is_dir']) {
            $filename = $GLOBALS['app']->getDataURL('directory/' . $data['user'] . '/' . $data['filename']);
            if (file_exists($filename)) {
                if (!Jaws_Utils::delete($filename)) {
                    return false;
                }
            }
        }

        return true;
    }
}