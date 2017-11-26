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
            $table->select(
                'directory.id:integer', 'directory.parent:integer', 'directory.user:integer',
                'users.username', 'users.nickname', 'is_dir:boolean', 'directory.public:boolean',
                'directory.key:integer', 'directory.title', 'directory.description',
                'user_filename', 'host_filename', 'mime_type', 'file_type', 'file_size', 'directory.hits',
                'directory.published:boolean', 'directory.create_time:integer', 'directory.update_time:integer'
            );
        }

        $table->join('users', 'directory.user', 'users.id', 'left');
        if (isset($params['user']) && !empty($params['user'])) {
            if(is_numeric($params['user'])) {
                $table->where('directory.user', (int)$params['user'])->and();
            } else {
                $table->where('users.username', $params['user'])->and();
            }
        }

        if (isset($params['parent'])) {
            $table->where('parent', $params['parent'])->and();
        }
        if (isset($params['public'])) {
            $table->where('directory.public', $params['public'])->and();
        }
        if (isset($params['is_dir'])) {
            $table->where('is_dir', $params['is_dir'])->and();
        }
        if (!empty($params['file_type'])) {
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
            $table->where('published', (bool)$params['published'])->and();
        }
        if (isset($params['date'])){
            if (!empty($params['date'][0])) {
                $table->where('create_time', $params['date'][0], '>=')->and();
            }
            if (!empty($params['date'][1])) {
                $table->where('create_time', $params['date'][1], '<=')->and();
            }
        }

        if (isset($params['query']) && !empty($params['query'])) {
            $table->openWhere('title', $params['query'], 'like')->or();
            $table->where('description', $params['query'], 'like')->or();
            $table->closeWhere('user_filename', $params['query'], 'like');
        }

        if (!$count && isset($params['limit']) && $params['limit'] > 0) {
            $table->limit($params['limit'], $params['offset']);
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
        $table->select('id', 'parent', 'user', 'title', 'description', 'key:integer',
            'host_filename', 'user_filename', 'mime_type', 'file_type', 'file_size',
            'is_dir:boolean', 'public:boolean', 'published:boolean', 'create_time', 'update_time');
        $fileInfo = $table->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($fileInfo)) {
            return $fileInfo;
        }

        if (!empty($fileInfo)) {
            $fileInfo['tags'] = array();
            // Fetch tags
            if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
                $tModel = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
                $tags = $tModel->GetReferenceTags('Directory', 'file', $id);
                $fileInfo['tags'] = implode(', ', array_filter($tags));
            }
        }
        return $fileInfo;

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
        if (!is_null($filename) && $filename !== '') {
            $thumbnail = "directory/$filename.thumbnail.png";
            if (file_exists(JAWS_DATA . $thumbnail)) {
                $thumbnailURL = $GLOBALS['app']->getDataURL($thumbnail);
            }
        }

        return $thumbnailURL;
    }

    /**
     * Determines file type according to the file extension
     */
    function getFileType($filename)
    {
        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
        if (empty($fileExt)) {
            return Directory_Info::FILE_TYPE_UNKNOWN;
        }
        $FileTypes = array(
            Directory_Info::FILE_TYPE_TEXT    => array('txt', 'doc', 'xml', 'html', 'htm', 'css', 'js', 'php', 'sh'),
            Directory_Info::FILE_TYPE_IMAGE   => array('gif', 'png', 'jpg', 'jpeg', 'raw', 'bmp', 'tiff', 'svg'),
            Directory_Info::FILE_TYPE_AUDIO   => array('wav', 'mp3', 'm4v', 'ogg'),
            Directory_Info::FILE_TYPE_VIDEO   => array('mpg', 'mpeg', 'avi', 'wma', 'rm', 'asf', 'flv', 'mov', 'mp4'),
            Directory_Info::FILE_TYPE_ARCHIVE => array('zip', 'rar', 'tar', 'gz', 'tgz', 'bz2', '7z', '7zip')
        );
        foreach ($FileTypes as $type => $exts) {
            foreach ($exts as $ext) {
                if ($fileExt == $ext) {
                    return $type;
                }
            }
        }
        return Directory_Info::FILE_TYPE_UNKNOWN;
    }

}