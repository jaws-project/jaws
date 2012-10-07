<?php
/**
 * Filebrowser Gadget
 *
 * @category   GadgetModel
 * @package    FileBrowser
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowserModel extends Jaws_Model
{
    /**
     * Get root dir
     *
     * @access  public
     * @return  string  The root directory
     */
    function GetFileBrowserRootDir()
    {
        static $root_dir;
        if (!isset($root_dir)) {
            $root_dir = trim($GLOBALS['app']->Registry->Get('/gadgets/FileBrowser/root_dir'));
            $root_dir = JAWS_DATA . $root_dir;
            $root_dir = str_replace('..', '', $root_dir);

            require_once 'File/Util.php';
            $root_dir = File_Util::realpath($root_dir) . DIRECTORY_SEPARATOR;
            if (!File_Util::pathInRoot($root_dir, JAWS_DATA)) {
                Jaws_Error::Fatal(_t('FILEBROWSER_ERROR_DIRECTORY_DOES_NOT_EXISTS'), __FILE__, __LINE__);
            }
        }

        return $root_dir;
    }

    /**
     * Get files of the current root dir
     *
     * @access  public
     * @param   string  $current_dir Current directory
     * @return  array   A list of directories or files of a certain directory
     */
    function GetCurrentRootDir($current_dir)
    {
        if (!is_dir($this->GetFileBrowserRootDir() . $current_dir)) {
            return new Jaws_Error(_t('FILEBROWSER_ERROR_DIRECTORY_DOES_NOT_EXISTS'),
                                 _t('FILEBROWSER_NAME'));
        }

        if (trim($current_dir) != '') {
            $path = $current_dir;
            if (substr($path, -1) != '/') {
                $path .= '/';
            }
        } else {
            $path = '/';
        }

        $path = str_replace('..', '', $path);

        $tree = array();
        $tree['/'] = '/';

        if (!empty($path)) {
            $parent_path = substr(strrev($path), 1);
            if (strpos($parent_path, '/')) {
                $parent_path = strrev(substr($parent_path, strpos($parent_path, '/'), strlen($parent_path)));
            } else {
                $parent_path = '';
            }

            $vpath = '';
            foreach (explode('/', $path) as $k) {
                if ($k != '') {
                    $vpath .= '/'.$k;
                    $tree[$vpath] = $k;
                }
            }
        } else {
            $tree[] = $path;
        }

        return $tree;
    }

    /**
     * Gets information of the directory content
     *
     * @access  public
     * @param   string  $path Where to read
     * @return  array   A list of properties of files and directories of a certain path and Jaws_Error on failure
     */
    function DBFileInfo($path, $file)
    {
        if (!empty($path) && $path != '/') {
            if (substr($path, -1) != '/') {
                $path .= '/';
            }
        } else {
            $path = '';
        }
        $path = str_replace('..', '', $path);

        $sql = 'SELECT
                    [id], [path], [filename], [title], [description], [fast_url], [hits]
                FROM [[filebrowser]]
                WHERE
                    [path]     = {path}
                  AND
                    [filename] = {file}';

        $params = array();
        $params['path'] = $path;
        $params['file'] = $file;

        $res = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::isError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('FILEBROWSER_NAME'));
        }

        return $res;
    }

    /**
     * Get file properties by id or fast_url
     *
     * @access  public
     * @param   mixed   $id Fast url or file id
     * @return  array   A list of properties of file
     */
    function DBFileInfoByIndex($id)
    {
        $sql = '
          SELECT
              [id], [path], [filename], [title], [description], [fast_url], [hits]
          FROM [[filebrowser]]';

        if (is_numeric($id)) {
            $sql .= ' WHERE [id] = {id}';
        } else {
            $sql .= ' WHERE [fast_url] = {id}';
        }

        $params = array();
        $params['id'] = $id;

        $res = $GLOBALS['db']->queryRow($sql, $params);
        return $res;
    }

    /**
     * Get file properties
     *
     * @access  public
     * @param   string  $path Where to read
     * @return  array   A list of properties of file
     */
    function GetFileProperties($path, $fname)
    {
        static $root_dir;
        if (!isset($root_dir)) {
            $root_dir = trim($GLOBALS['app']->Registry->Get('/gadgets/FileBrowser/root_dir'));
        }

        $file = array();
        $file['filename'] = $fname;
        $file['is_dir'] = false;

        //Get url
        if ($path == '/') {
            $path = '';
        }
        $url = $GLOBALS['app']->getDataURL(str_replace('//', '/', $root_dir.'/'.$path.'/'.$fname), false);
        $file['url'] = $url;
        $file['relative'] = str_replace('//', '/', $path.'/'.$fname);

        //Get the extension
        //require 'MIME/Type.php';
        //var_dump(MIME_Type::autoDetect($filepath));
        $ext = strtolower(strrev(substr(strrev($fname), 0, strpos(strrev($fname), '.'))));

        //Get the icon
        $theme = $GLOBALS['app']->GetTheme();
        $iconName   = $this->getExtImage($ext);
        $image_path = $theme['path'] . 'FileBrowser/';
        $image_url  = $theme['url']  . 'FileBrowser/';
        if (is_file($image_path . $iconName)) {
            $file['icon'] = $image_url . $iconName;
        } else {
            //Is icon does not exists..
            if (!is_file(JAWS_PATH . 'gadgets/FileBrowser/images/'.$iconName)) {
                $icon =  'gadgets/FileBrowser/images/mimetypes/text-x-generic.png';
            } else {
                $icon =  'gadgets/FileBrowser/images/'.$iconName;
            }
            $file['icon'] = $icon;
        }

        //Set the extension
        $file['ext'] = $ext;
        //Fullpath
        $filepath = $this->GetFileBrowserRootDir() . $path . $fname;
        $file['fullpath'] = $filepath;
        //Set the icon
        $file['mini_icon'] = 'gadgets/FileBrowser/images/mini_file.png';

        //Get $date
        $file['date'] = @filemtime($filepath);

        //Set the file size
        $file['size'] = Jaws_Utils::FormatSize(@filesize($filepath));
        //Set the curr dir name
        $file['dirname'] = $path;
        $dbFile = $this->DBFileInfo($path, $fname);
        if (Jaws_Error::IsError($dbFile) || empty($dbFile)) {
            $file['id']          = 0;
            $file['title']       = $fname;
            $file['description'] = '';
            $file['fast_url']    = '';
            $file['hits']        = '';
        } else {
            $file['id']          = $dbFile['id'];
            $file['title']       = $dbFile['title'];
            $file['description'] = $dbFile['description'];
            $file['fast_url']    = $dbFile['fast_url'];
            $file['hits']        = $dbFile['hits'];
        }

        return $file;
    }

    /**
     * Get dir properties
     *
     * @access  public
     * @param   string  $path Where to read
     * @return  array   A list of properties directory
     */
    function GetDirProperties($path, $dirname)
    {
        static $root_dir;
        if (!isset($root_dir)) {
            $root_dir = trim($GLOBALS['app']->Registry->Get('/gadgets/FileBrowser/root_dir'));
        }

        //Set the filename
        $dir['filename'] = $dirname;
        //Set is_dir to true(tutru)
        $dir['is_dir'] = true;

        //Get url
        if (empty($path)) {
            $dir['relative'] = str_replace('//', '/', $dirname);
            $url = $GLOBALS['app']->Map->GetURLFor('FileBrowser', 'Display', array('path' => $dirname));
        } else {
            $dir['relative'] = str_replace('//', '/', $path.'/'.$dirname);
            $url = $GLOBALS['app']->Map->GetURLFor('FileBrowser', 'Display',
                                                   array('path' => str_replace('//', '/', $path.'/'.$dirname)));
        }
        $dir['url'] = $url;

        //Set the size(default for dirs)
        $dir['size'] = '-';

        //Fullpath
        $filepath = $this->GetFileBrowserRootDir() . $path . $dirname;
        $dir['fullpath'] = $filepath;

        //Get $date
        $dir['date'] = @filemtime($filepath);

        //Set the curr dir name
        $dir['dirname'] = $path;
        //Is shared?
        $dir['is_shared'] = file_exists($dir['fullpath'].'/.jaws_virtual');
        //hold.. is it shared?
        if ($dir['is_shared']) {
            $dir['icon'] = 'gadgets/FileBrowser/images/folder-remote.png';
            $dir['mini_icon'] =  'gadgets/FileBrowser/images/folder-remote.png';
        } else {
            $dir['icon'] = 'gadgets/FileBrowser/images/folder.png';
            $dir['mini_icon'] = 'gadgets/FileBrowser/images/mini_folder.png';
        }

        $dbDir = $this->DBFileInfo($path, $dirname);
        if (Jaws_Error::IsError($dbDir) || empty($dbDir)) {
            $dir['id']          = 0;
            $dir['title']       = $dirname;
            $dir['description'] = '';
            $dir['fast_url']    = '';
            $dir['hits']        = '-';
        } else {
            $dir['id']          = $dbDir['id'];
            $dir['title']       = $dbDir['title'];
            $dir['description'] = $dbDir['description'];
            $dir['fast_url']    = $dbDir['fast_url'];
            $dir['hits']        = '-';
        }

        return $dir;
    }

    /**
     * Gets the directory content
     *
     * @access  public
     * @param   string  $path Where to read
     * @return  array   A list of properties of files and directories of a certain path and Jaws_Error on failure
     */
    function ReadDir($path, $limit = 0, $offset = 0, $order = '')
    {
        if (!empty($path) && $path != '/') {
            if (substr($path, -1) != '/') {
                $path .= '/';
            }
        } else {
            $path = '';
        }

        $path = str_replace('..', '', $path);
        $folder = $this->GetFileBrowserRootDir() . $path;
        if (!file_exists($folder) || !$adr = scandir($folder)) {
            if (isset($GLOBALS['app']->Session)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FILEBROWSER_ERROR_CANT_OPEN_DIRECTORY', $path),
                                                      RESPONSE_ERROR);
            }
            return new Jaws_Error(_t('FILEBROWSER_ERROR_CANT_OPEN_DIRECTORY', $path),  _t('FILEBROWSER_NAME'));
        }

        $files = array();
        $file_counter = -1;
        $date_obj = $GLOBALS['app']->loadDate();
        foreach ($adr as $file) {
            //we should return only 'visible' files, not hidden files
            if ($file{0} != '.') {
                $file_counter ++;
                $filepath = $this->GetFileBrowserRootDir() . $path . $file;
                if (is_dir($filepath)) {
                    $files[$file_counter] = $this->GetDirProperties($path, $file);
                } else {
                    $files[$file_counter] = $this->GetFileProperties($path, $file);
                }
            }
        }

        $files = $this->SortFiles($files, $order);
        if (empty($limit)) {
            return $files;
        }

        return array_slice($files, $offset, $limit);
    }

    /**
     * Gets Count of items in directory
     *
     * @access  public
     * @param   string  $path Where to check
     * @return  array   Count of items in directory
     */
    function GetDirContentsCount($path)
    {
        if (!empty($path) && $path != '/') {
            if (substr($path, -1) != '/') {
                $path .= '/';
            }
        } else {
            $path = '';
        }

        $path = str_replace('..', '', $path);
        $folder = $this->GetFileBrowserRootDir() . $path;
        if (file_exists($folder) && $adr = scandir($folder)) {
            return count($adr) - 2;
        }

        return 0;
    }

    /**
     * Performs case insensitive sort based on filename.
     * Directories first, followed by files.
     *
     * @access public
     * @param array $files The filesystem array
     * @return array the sorted filesystem array
     */
    function SortFiles($files, $order = '')
    {
        if (empty($files)) {
            return $files;
        }

        require_once JAWS_PATH.'include/Jaws/ArraySort.php';
        $files = Jaws_ArraySort::SortBySecondIndex($files, 'is_dir', false, true);

        $filesStart = count($files);
        foreach ($files as $pos => $item) {
            if (!$item['is_dir']) {
                $filesStart = $pos;
                break;
            }
        }
        $dirs = array_splice($files, 0, $filesStart);

        if (empty($order)) {
            $order = $GLOBALS['app']->Registry->Get('/gadgets/FileBrowser/order_type');
        }
        $order = explode(',', $order);
        $indexs = array('title', 'filename', 'date');
        if (!isset($order[0]) || !in_array($order[0], $indexs)) {
            $order[0] = 'filename';
            $order[1] = false;
        } else {
            $order[1] = trim($order[1]) == 'true';
        }

        if (!empty($files)) {
            $files = Jaws_ArraySort::SortBySecondIndex($files, $order[0], false, $order[1]);
        }

        if (!empty($dirs)) {
            $dirs = Jaws_ArraySort::SortBySecondIndex($dirs, 'filename', false);
        }

        return array_merge($dirs, $files);
    }

    /**
     *
     */
    function getExtImage($ext)
    {
        $ext = strtolower($ext);

        $exts = array();
        $exts['audio-x-generic'] = array(
            'mp3', 'wav', 'aac', 'flac',
            'ogg', 'wma', 'cda', 'voc', 'midi',
            'ac3', 'bonk', 'mod'
        );

        $exts['image-x-generic'] = array(
            'gif', 'png', 'jpg', 'jpeg', 'raw',
            'bmp', 'tiff', 'swf', 'svg'
        );

        $exts['package-x-generic'] = array(
            'tar', 'tar.gz', 'zip', 'gzip', 'rar',
            'rpm', 'deb', 'iso', 'bz2', 'bak', 'gz'
        );

        $exts['video-x-generic'] = array(
            'mpg', 'mpeg', 'avi', 'wma', 'rm',
            'asf', 'flv', 'mov'
        );

        $exts['text-x-generic'] = array(
            'txt', 'pdf', ''
        );

        $exts['text-html'] = array(
            'html', 'htm'
        );

        $exts['text-x-script'] = array(
            'sh', 'pl', 'php', 'asp', 'jsp',
            'py', 'c', 'css'
        );

        $exts['application-x-executable'] = array(
            'exe'
        );

        $exts['x-office-document-template'] = array(
            'stw', 'ott',
        );

        $exts['x-office-document'] = array(
            'doc', 'sxw', 'odt', 'rtf', 'sdw'
        );

        $exts['x-office-presentation-template'] = array(
            'pot', 'otp', 'sti'
        );

        $exts['x-office-presentation'] = array(
            'ppt', 'odp', 'sxi'
        );

        $exts['x-office-spreadsheet-template'] = array(
            'xlt', 'ots', 'stc'
        );

        $exts['x-office-spreadsheet'] = array(
            'xls', 'ods', 'sxc', 'sdc'
        );

        $exts['x-office-drawing-template'] = array(
        );

        $exts['x-office-drawing'] = array(
            'sxd', 'sda', 'sdd', 'odg'
        );

        $found = false;
        foreach ($exts as $key => $data) {
            if (in_array($ext, $data)) {
                return 'mimetypes/' . $key . '.png';
            }
        }


        return false;
    }

    /**
     * Get entry pager numbered links
     *
     * @access  public
     * @param   int     $page      Current page number
     * @param   int     $page_size Entries count per page
     * @param   int     $total     Total entries count
     * @return  array   array with numbers of pages
     */
    function GetEntryPagerNumbered($page, $page_size, $total)
    {
        $tail = 1;
        $paginator_size = 4;
        $pages = array();
        if ($page_size == 0) {
            return $pages;
        }

        $npages = ceil($total / $page_size);

        if ($npages < 2) {
            return $pages;
        }

        // Previous
        if ($page == 1) {
            $pages['previous'] = false;
        } else {
            $pages['previous'] = $page - 1;
        }

        if ($npages <= ($paginator_size + $tail)) {
            for ($i = 1; $i <= $npages; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }
        } elseif ($page < $paginator_size) {
            for ($i = 1; $i <= $paginator_size; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }

            $pages['separator2'] = true;

            for ($i = $npages - ($tail - 1); $i <= $npages; $i++) {
                $pages[$i] = $i;
            }
            
        } elseif ($page > ($npages - $paginator_size + $tail)) {
            for ($i = 1; $i <= $tail; $i++) {
                $pages[$i] = $i;
            }

            $pages['separator1'] = true;

            for ($i = $npages - $paginator_size + ($tail - 1); $i <= $npages; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }
        } else {
            for ($i = 1; $i <= $tail; $i++) {
                $pages[$i] = $i;
            }

            $pages['separator1'] = true;

            $start = floor(($paginator_size - $tail)/2);
            $end = ($paginator_size - $tail) - $start;
            for ($i = $page - $start; $i < $page + $end; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }

            $pages['separator2'] = true;

            for ($i = $npages - ($tail - 1); $i <= $npages; $i++) {
                $pages[$i] = $i;
            }
            
        }

        // Next
        if ($page == $npages) {
            $pages['next'] = false;
        } else {
            $pages['next'] = $page + 1;
        }

        $pages['total'] = $total;

        return $pages;
    }

    /**
     * Increment download hits
     *
     * @access  public
     * @param   int     $fid File ID
     * @return  boolean True if hits was successfully increment and false on error
     */
    function HitFileDownload($fid)
    {
        $sql = '
            UPDATE [[filebrowser]] SET
                [hits] = [hits] + 1
            WHERE
                [id] = {fid}';
        $result = $GLOBALS['db']->query($sql, array('fid' => $fid));
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

}