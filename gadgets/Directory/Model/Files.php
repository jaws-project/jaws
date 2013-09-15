<?php
/**
 * Directory Gadget
 *
 * @category    GadgetModel
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Model_Files extends Jaws_Gadget_Model
{
    /**
     * Fetches list of files and directories
     *
     * @access  public
     * @param   int     $parent     Restrict result to a specified node
     * @return  array   Array of files or Jaws_Error on error
     */
    function GetFiles($user = null, $parent = null, $shared = null, $foreign = null)
    {
        $table = Jaws_ORM::getInstance()->table('directory');
        $table->select('id', 'parent', 'user', 'is_dir:boolean', 'title',
            'description', 'filename', 'filetype', 'filesize', 'url',
            'shared:boolean', 'owner', 'reference', 'createtime', 'updatetime');

        if ($user !== null){
            $table->where('user', $user);
        }

        if ($parent !== null){
            $table->and()->where('parent', $parent);
        }

        if ($shared !== null){
            $table->and()->where('shared', $shared);
        }

        if ($foreign !== null){
            $flag = $foreign? '<>' : '=';
            $table->and()->where('user', $table->expr('owner'), $flag);
        }

        return $table->orderBy('is_dir desc', 'title asc')->fetchAll();
    }

    /**
     * Fetches data of a file/directory
     *
     * @access  public
     * @param   int     $id  File ID
     * @return  mixed   Array of file data or Jaws_Error on error
     */
    function GetFile($id)
    {
        $table = Jaws_ORM::getInstance()->table('directory');
        $table->select('id', 'parent', 'user', 'is_dir:boolean', 'title',
            'description', 'filename', 'filetype', 'filesize', 'url',
            'shared:boolean', 'owner', 'reference', 'createtime', 'updatetime');
        return $table->where('id', $id)->fetchRow();
    }

    /**
     * Fetches path of a file/directory
     *
     * @access  public
     * @param   int     $id     File or Directory ID
     * @param   array   $path   Directory hierarchy
     * @return  void
     */
    function GetPath($id, &$path)
    {
        $table = Jaws_ORM::getInstance()->table('directory');
        $table->select('id', 'parent', 'title');
        $parent = $table->where('id', $id)->fetchRow();
        if (!empty($parent)) {
            $path[] = array('id' => $parent['id'], 'title' => $parent['title']);
            $this->GetPath($parent['parent'], $path);
        }
    }

    /**
     * Inserts a new file/directory
     *
     * @access  public
     * @param   array  $data    File data
     * @return  mixed   True on successful insert, Jaws_Error otherwise
     */
    function InsertFile($data)
    {
        $data['createtime'] = $data['updatetime'] = time();
        $table = Jaws_ORM::getInstance()->table('directory');
        return $table->insert($data)->exec();
    }

    /**
     * Updates file/directory
     *
     * @access  public
     * @param   int     $id     File ID
     * @param   array   $data   File data
     * @return  mixed   True on successful update and Jaws_Error on error
     */
    function UpdateFile($id, $data)
    {
        $data['updatetime'] = time();
        $table = Jaws_ORM::getInstance()->table('directory');
        return $table->update($data)->where('id', $id)->exec();
    }

    /**
     * Deletes file/directory
     *
     * @access  public
     * @param   int     $id  File ID
     * @return  mixed   Array of file data or Jaws_Error on error
     */
    function DeleteFile($id)
    {
        $table = Jaws_ORM::getInstance()->table('directory');
        return $table->delete()->where('id', $id)->exec();
    }
}