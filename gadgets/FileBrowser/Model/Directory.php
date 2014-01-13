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
class FileBrowser_Model_Directory extends Jaws_Gadget_Model
{
    /**
     * Get files of the current root dir
     *
     * @access  public
     * @param   string  $path    Current directory
     * @return  array   A list of directories or files of a certain directory
     */
    function GetCurrentRootDir($path)
    {
        $path = trim($path, '/');
        $path = str_replace('..', '', $path);

        $fModel = $this->gadget->model->load('Files');
        if (!is_dir($fModel->GetFileBrowserRootDir() . $path)) {
            return Jaws_Error::raiseError(
                _t('FILEBROWSER_ERROR_DIRECTORY_DOES_NOT_EXISTS'),
                404,
                JAWS_ERROR_NOTICE
            );
        }

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
     * Get dir properties
     *
     * @access  public
     * @param   string  $path       Where to read
     * @param   string  $dirname
     * @return  array   A list of properties directory
     */
    function GetDirProperties($path, $dirname)
    {
        $fModel = $this->gadget->model->load('Files');

        static $root_dir;
        if (!isset($root_dir)) {
            $root_dir = trim($this->gadget->registry->fetch('root_dir'));
        }

        //Set the filename
        $dir['filename'] = $dirname;
        //Set is_dir to true(tutru)
        $dir['is_dir'] = true;

        //Get url
        if (empty($path)) {
            $dir['relative'] = str_replace('//', '/', $dirname);
            $url = $this->gadget->urlMap('Display', array('path' => $dirname));
        } else {
            $dir['relative'] = str_replace('//', '/', $path.'/'.$dirname);
            $url = $this->gadget->urlMap(
                'Display',
                array('path' => str_replace('//', '/', $path.'/'.$dirname))
            );
        }
        $dir['url'] = $url;

        //Set the size(default for dirs)
        $dir['size'] = '-';

        //Fullpath
        $filepath = $fModel->GetFileBrowserRootDir(). $path. '/'. $dirname;
        $dir['fullpath'] = $filepath;

        //Get $date
        $dir['date'] = @filemtime($filepath);

        //Set the curr dir name
        $dir['dirname'] = $path;
        //Is shared?
        $dir['is_shared'] = file_exists($dir['fullpath'].'/.jaws_virtual');
        //hold.. is it shared?
        if ($dir['is_shared']) {
            $dir['icon'] = 'gadgets/FileBrowser/Resources/images/folder-remote.png';
            $dir['mini_icon'] =  'gadgets/FileBrowser/Resources/images/folder-remote.png';
        } else {
            $dir['icon'] = 'gadgets/FileBrowser/Resources/images/folder.png';
            $dir['mini_icon'] = 'gadgets/FileBrowser/Resources/images/mini_folder.png';
        }

        $fModel = $this->gadget->model->load('Files');
        $dbDir = $fModel->DBFileInfo($path, $dirname);
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
     * @param   string  $path   Where to read
     * @param   int     $limit  limit data
     * @param   int     $offset start offset
     * @param   string  $order
     * @return  mixed   A list of properties of files and directories of a certain path and Jaws_Error on failure
     */
    function ReadDir($path = '', $limit = 0, $offset = 0, $order = '')
    {
        $path = trim($path, '/');
        $path = str_replace('..', '', $path);

        $fModel = $this->gadget->model->load('Files');
        $folder = $fModel->GetFileBrowserRootDir() . $path;
        if (!file_exists($folder) || !$adr = scandir($folder)) {
            if (isset($GLOBALS['app']->Session)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FILEBROWSER_ERROR_CANT_OPEN_DIRECTORY', $path),
                    RESPONSE_ERROR);
            }
            return new Jaws_Error(_t('FILEBROWSER_ERROR_CANT_OPEN_DIRECTORY', $path));
        }

        $files = array();
        $file_counter = -1;
        $date_obj = Jaws_Date::getInstance();
        foreach ($adr as $file) {
            //we should return only 'visible' files, not hidden files
            if ($file{0} != '.') {
                $file_counter++;
                $filepath = $fModel->GetFileBrowserRootDir(). $path. '/'. $file;
                if (is_dir($filepath)) {
                    $files[$file_counter] = $this->GetDirProperties($path, $file);
                    // check directory access permission
                    if (empty($path) && !empty($files[$file_counter]['id']) &&
                        !$this->gadget->GetPermission('OutputAccess', $files[$file_counter]['id']))
                    {
                        unset($files[$file_counter]);
                        $file_counter--;
                        continue;
                    }
                } else {
                    $files[$file_counter] = $fModel->GetFileProperties($path, $file);
                }
            }
        }

        $fModel = $this->gadget->model->load('Files');
        $files  = $fModel->SortFiles($files, $order);
        if (empty($limit)) {
            return $files;
        }

        return array_slice($files, $offset, $limit);
    }

    /**
     * Gets Count of items in directory
     *
     * @access  public
     * @param   string  $path   Where to check
     * @return  int     Count of items in directory
     */
    function GetDirContentsCount($path)
    {
        $path = trim($path, '/');
        $path = str_replace('..', '', $path);

        $fModel = $this->gadget->model->load('Files');
        $folder = $fModel->GetFileBrowserRootDir() . $path;
        if (file_exists($folder) && $adr = scandir($folder)) {
            return count($adr) - 2;
        }

        return 0;
    }

}