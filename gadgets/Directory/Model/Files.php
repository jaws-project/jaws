<?php
/**
 * Directory Gadget
 *
 * @category    GadgetModel
 * @package     Directory
 */
class Directory_Model_Files extends Jaws_Gadget_Model
{
    /**
     * Fetches list of files
     *
     * @access  public
     * @param   int     $params     Query params
     * @param   int     $orderBy    Order by
     * @return  array   Array of files or Jaws_Error on error
     */
    function GetFiles($params, $count = false, $orderBy = 1)
    {
        $table = Jaws_ORM::getInstance()->table('directory');
        if ($count) {
            $table->select('count(directory.id):integer');
        } else {
            $table->select('directory.id:integer', 'directory.parent:integer', 'directory.user:integer',
                'is_dir:boolean', 'directory.hidden:boolean', 'directory.title', 'directory.description',
                'user_filename', 'host_filename', 'mime_type', 'file_type', 'file_size', 'directory.hits',
                'directory.create_time:integer', 'directory.update_time:integer');
        }

        if (isset($params['user']) && !empty($params['user'])) {
            if(is_numeric($params['user'])) {
                $table->where('user', (int)$params['user'])->and();
            } else {
                $table->join('users', 'users.id', 'directory.user', 'left');
                $table->where('users.username', $params['user'])->and();
            }
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
            if (!empty($params['file_size'][0])) {
                $table->where('file_size', $params['file_size'][0] * 1024, '>=')->and();
            }
            if (!empty($params['file_size'][1])) {
                $table->where('file_size', $params['file_size'][1] * 1024, '<=')->and();
            }
        }

        if (isset($params['published'])) {
            $table->where('published', $params['published'])->and();
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
//            $table->orderBy('is_dir desc', 'title asc');
        }

        if (!$count && (int)$orderBy > 0) {
            $orders = array(
                1 => 'create_time asc',
                2 => 'create_time desc',
            );
            $orderBy = isset($orders[$orderBy]) ? $orderBy : (int)$this->gadget->registry->fetch('order_type');
            $table->orderBy($orders[$orderBy]);
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
     * Inserts a new file/directory
     *
     * @access  public
     * @param   array   $data    File data
     * @return  mixed   Query result
     */
    function InsertFile($data)
    {
        $data['create_time'] = $data['update_time'] = time();
        $table = Jaws_ORM::getInstance()->table('directory');
        return $table->insert($data)->exec();
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
     * Generate Thumbnail URL from filename
     */
    function GetThumbnailURL($filename)
    {
        $thumbnailURL = '';
        $fileInfo = pathinfo($filename);

        $thumbnailPath = JAWS_DATA . 'directory/' . $fileInfo['filename'] . '.thumbnail.png';
        if (file_exists($thumbnailPath)) {
            $thumbnailURL = $GLOBALS['app']->getDataURL('directory/' . $fileInfo['filename'] . '.thumbnail.png');
        }
        return $thumbnailURL;
    }
}