<?php
/**
 * Directory Gadget
 *
 * @category    GadgetModel
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Model_Admin_Files extends Jaws_Gadget_Model
{
    /**
     * Fetches list of files
     *
     * @access  public
     * @param   int     $params  Query params
     * @return  array   Array of files or Jaws_Error on error
     */
    function GetFiles($params, $count = false)
    {
        $table = Jaws_ORM::getInstance()->table('directory');
        if ($count) {
            $table->select('count(id):integer');
        } else {
            $table->select('id', 'parent', 'user', 'is_dir:boolean', 'hidden:boolean',
                'title', 'description', 'user_filename', 'host_filename', 'mime_type',
                'file_type', 'file_size', 'hits', 'create_time', 'update_time');
        }

        if (isset($params['parent'])) {
            $table->where('parent', $params['parent'])->and();
        }

        if (isset($params['hidden'])) {
            $table->where('hidden', $params['hidden'])->and();
        }

        if (isset($params['is_dir'])) {
            $table->where('is_dir', $params['is_dir'])->and();
        }

        if (isset($params['file_type'])) {
            $types = explode(',', $params['file_type']);
            $table->where('file_type', $types, 'in')->and();
        }

        if (isset($params['file_size'])) {
            if (!empty($params['size'][0])) {
                $table->where('file_size', $params['file_size'][0] * 1024, '>=')->and();
            }
            if (!empty($params['size'][1])) {
                $table->where('file_size', $params['file_size'][1] * 1024, '<=')->and();
            }
        }

        if (isset($params['date'])){
            if (!empty($params['date'][0])) {
                $table->where('create_time', $params['date'][0], '>=')->and();
            }
            if (!empty($params['date'][1])) {
                $table->where('create_time', $params['date'][1], '<=')->and();
            }
        }

        if (isset($params['query']) && !empty($params['query'])){
            $table->openWhere('title', $params['query'], 'like')->or();
            $table->where('description', $params['query'], 'like')->or();
            $table->closeWhere('user_filename', $params['query'], 'like');
        }

        if (!$count && isset($params['limit']) && $params['limit'] > 0) {
            $table->limit($params['limit'], $params['offset']);
            $table->orderBy('is_dir desc', 'title asc');
        }

        return $count? $table->fetchOne() : $table->fetchAll();
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
        $table->select('id', 'parent', 'user', 'title', 'description',
            'host_filename', 'user_filename', 'mime_type', 'file_type', 'file_size',
            'is_dir:boolean', 'hidden:boolean', 'create_time', 'update_time');
        return $table->where('id', $id)->fetchRow();
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
            $path[] = array(
                'id' => $parent['id'],
                'title' => $parent['title']
            );
            $this->GetPath($parent['parent'], $path);
        }
    }

    /**
     * Inserts a new file/directory
     *
     * @access  public
     * @param   array   $data    File data
     * @return  mixed   Query result
     */
    function Insert($data)
    {
        $data['create_time'] = $data['update_time'] = time();
        $table = Jaws_ORM::getInstance()->table('directory');
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
            $files = $this->GetFiles(array('parent' => $data['id']));
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
        $res = $table->exec();
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        // Delete from disk
        if (!$data['is_dir']) {
            $filename = JAWS_DATA . 'directory/' . $data['host_filename'];
            if (file_exists($filename)) {
                if (!Jaws_Utils::delete($filename)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Updates parent of the file/directory
     *
     * @access  public
     * @param   int     $id      File ID
     * @param   int     $parent  New file parent
     * @return  mixed   Query result
     */
    function Move($id, $parent)
    {
        $table = Jaws_ORM::getInstance()->table('directory');
        $table->update(array('parent' => $parent));
        return $table->where('id', $id)->exec();
    }
}