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
     * Fetches list of files including shared files
     *
     * @access  public
     * @param   int     $parent  Restricts results to a specified node
     * @return  array   Array of files or Jaws_Error on error
     */
    function GetFiles($parent = 0, $user = null, $shared = null, $foreign = null)
    {
        $access = ($user === null)? null : $this->CheckAccess($parent, $user);
        if ($access === false) {
            return array();
        }

        $table = Jaws_ORM::getInstance()->table('directory');
        $table->select('id', 'parent', 'user', 'is_dir:boolean', 'title',
            'description', 'filename', 'filetype', 'filesize', 'url',
            'shared:boolean', 'owner', 'reference', 'createtime', 'updatetime');
        $table->where('parent', $parent)->and();

        if ($access !== true && $user !== null){
            $table->where('user', $user)->and();
        }

        if ($shared !== null){
            $table->where('shared', $shared)->and();
        }

        if ($foreign !== null){
            $flag = $foreign? '<>' : '=';
            $table->where('user', $table->expr('owner'), $flag);
        }

        return $table->orderBy('is_dir desc', 'title asc')->fetchAll();
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
        $table = Jaws_ORM::getInstance()->table('directory');
        $table->select('id', 'parent', 'user', 'is_dir:boolean', 'title',
            'description', 'filename', 'filetype', 'filesize', 'url',
            'shared:boolean', 'owner', 'reference', 'createtime', 'updatetime');
        return $table->where('id', $id)->fetchRow();
    }

    /**
     * Checks user access to files including shared files
     *
     * @access  public
     * @param   int     $id  File ID
     * @return  bool    True or false
     */
    function CheckAccess($id, $user)
    {
        if ($id === 0) {
            return null; // root is neutral
        } else {
            $table = Jaws_ORM::getInstance()->table('directory');
            $table->select('user:integer', 'parent:integer');
            $data = $table->where('id', $id)->fetchRow();
            if ($data['user'] === $user) {
                return true;
            }
        }

        // Check for shared files
        $table = Jaws_ORM::getInstance()->table('directory');
        $table->select('count(id):integer');
        $table->where('user', $user)->and();
        $table->where('reference', $id);
        $count = $table->fetchOne();
        if ($count > 0) {
            return true;
        }

        if ($data['parent'] !== 0) {
            return $this->CheckAccess($data['parent'], $user);
        }

        return false;
    }

    /**
     * Fetches path of a file/directory
     *
     * @access  public
     * @param   int     $id     File ID
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
     * @param   array   $data    File data
     * @return  mixed   True on successful insert, Jaws_Error otherwise
     */
    function Insert($data)
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
    function Update($id, $data)
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
    function Delete($id)
    {
        $table = Jaws_ORM::getInstance()->table('directory');
        return $table->delete()->where('id', $id)->exec();
    }
}