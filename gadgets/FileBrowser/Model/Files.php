<?php
/**
 * Filebrowser Gadget
 *
 * @category   GadgetModel
 * @package    FileBrowser
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_Model_Files extends Jaws_Gadget_Model
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
            $root_dir = trim($this->gadget->registry->fetch('root_dir'));
            $root_dir = JAWS_DATA . trim($root_dir, "\\/");
            $root_dir = str_replace('..', '', $root_dir);

            require_once PEAR_PATH. 'File/Util.php';
            $root_dir = File_Util::realpath($root_dir). '/';
            if (!File_Util::pathInRoot($root_dir, JAWS_DATA)) {
                Jaws_Error::Fatal(_t('FILEBROWSER_ERROR_DIRECTORY_DOES_NOT_EXISTS'), __FILE__, __LINE__);
            }
        }

        return $root_dir;
    }


    /**
     * Get file properties by id or fast_url
     *
     * @access  public
     * @param   mixed   $id     Fast url or file id
     * @return  array   A list of properties of file
     */
    function DBFileInfoByIndex($id)
    {
        $table = Jaws_ORM::getInstance()->table('filebrowser');
        $table->select('id:integer', 'path', 'filename', 'title', 'description', 'fast_url', 'hits:integer');

        if (is_numeric($id)) {
            $table->where('id', $id);
        } else {
            $table->where('fast_url', $id);
        }

        $res = $table->fetchRow();
        return $res;
    }

    /**
     * Get file properties
     *
     * @access  public
     * @param   string  $path   Where to read
     * @param   string  $fname
     * @return  array   A list of properties of file
     */
    function GetFileProperties($path, $fname)
    {
        static $root_dir;
        if (!isset($root_dir)) {
            $root_dir = trim($this->gadget->registry->fetch('root_dir'));
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
            if (!is_file(JAWS_PATH. 'images/'.$iconName)) {
                $icon = 'images/mimetypes/text-generic.png';
            } else {
                $icon = 'images/'.$iconName;
            }
            $file['icon'] = $icon;
        }

        //Set the extension
        $file['ext'] = $ext;
        //Fullpath
        $filepath = $this->GetFileBrowserRootDir(). $path. '/'. $fname;
        $file['fullpath'] = $filepath;
        //Set the icon
        $file['mini_icon'] = 'gadgets/FileBrowser/Resources/images/mini_file.png';

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
     * Gets information of the directory content
     *
     * @access  public
     * @param   string  $path   Where to read
     * @param   string  $file
     * @return  mixed   A list of properties of files and directories of a certain path and Jaws_Error on failure
     */
    function DBFileInfo($path, $file)
    {
        $path = trim($path, '/');
        $path = str_replace('..', '', $path);

        $table = Jaws_ORM::getInstance()->table('filebrowser');
        $table->select('id:integer', 'path', 'filename', 'title', 'description', 'fast_url', 'hits:integer');
        $res = $table->where('path', $path)->and()->where('filename', $file)->fetchRow();
        if (Jaws_Error::isError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'));
        }

        return $res;
    }

    /**
     * Performs case insensitive sort based on filename.
     * Directories first, followed by files.
     *
     * @access  public
     * @param   array   $files  The filesystem array
     * @param   int     $order
     * @return  array   the sorted filesystem array
     */
    function SortFiles($files, $order = '')
    {
        if (empty($files)) {
            return $files;
        }

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
            $order = $this->gadget->registry->fetch('order_type');
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
     * Retrieves extension
     *
     * @access  public
     * @param   string  $ext
     * @return  mixed   extension string or false on error
     */
    function getExtImage($ext)
    {
        $ext = strtolower($ext);

        $exts = array();
        $exts['font-generic'] = array('ttf', 'otf', 'fon', 'pfa', 'afm', 'pfb');
        $exts['audio-generic'] = array(
            'mp3', 'wav', 'aac', 'flac', 'ogg', 'wma', 'cda', 'voc', 'midi', 'ac3', 'bonk', 'mod'
        );
        $exts['image-generic'] = array('gif', 'png', 'jpg', 'jpeg', 'raw', 'bmp', 'tiff', 'svg');
        $exts['package-generic'] = array(
            'tar', 'tar.gz', 'tgz', 'zip', 'gzip', 'rar', 'rpm', 'deb', 'iso', 'bz2', 'bak', 'gz'
        );
        $exts['video-generic'] = array('mpg', 'mpeg', 'avi', 'wma', 'rm', 'asf', 'flv', 'mov');
        $exts['help-contents'] = array('hlp', 'chm', 'manual', 'man');
        $exts['text-generic'] = array('txt', '');
        $exts['text-html'] = array('html', 'htm', 'mht');
        $exts['text-java'] = array('jsp', 'java', 'jar');
        $exts['text-python'] = array('py');
        $exts['text-script'] = array('sh', 'pl', 'asp', 'c', 'css', 'htaccess');
        $exts['office-document-template'] = array('stw', 'ott');
        $exts['office-document'] = array('doc', 'docx', 'sxw', 'odt', 'rtf', 'sdw');
        $exts['office-presentation-template'] = array('pot', 'otp', 'sti');
        $exts['office-presentation'] = array('ppt', 'odp', 'sxi');
        $exts['office-spreadsheet-template'] = array('xlt', 'ots', 'stc');
        $exts['office-spreadsheet'] = array('xls', 'ods', 'sxc', 'sdc');
        $exts['office-drawing-template'] = array();
        $exts['office-drawing'] = array('sxd', 'sda', 'sdd', 'odg');
        $exts['application-executable'] = array('exe');
        $exts['application-php'] = array('php', 'phps');
        $exts['application-rss+xml'] = array('xml', 'rss', 'atom', 'rdf');
        $exts['application-pdf'] = array('pdf');
        $exts['application-flash'] = array('swf');
        $exts['application-ruby'] = array('rb');

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
     * @param   int     $fid    File ID
     * @return  bool    True if hits was successfully increment and false on error
     */
    function HitFileDownload($fid)
    {
        $table = Jaws_ORM::getInstance()->table('filebrowser');
        $result = $table->update(
            array(
                'hits' => $table->expr('hits + ?', 1)
            )
        )->where('id', $fid)->exec();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

}