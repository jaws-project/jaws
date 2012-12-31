<?php
require_once JAWS_PATH . 'gadgets/FileBrowser/Model.php';
/**
 * Filebrowser Admin Gadget
 *
 * @category   GadgetModel
 * @package    FileBrowser
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_AdminModel extends FileBrowser_Model
{
    /**
     * Add/Update file or directory information
     *
     * @access  public
     * @param   string  $path           File|Directory path
     * @param   string  $file           File|Directory name
     * @param   string  $title          
     * @param   string  $description
     * @param   string  $fast_url
     * @param   string  $oldname        
     * @return  mixed   A list of properties of files and directories of a certain path and Jaws_Error on failure
     */
    function UpdateDBFileInfo($path, $file, $title, $description, $fast_url, $oldname = '')
    {
        if (!empty($path) && $path != '/') {
            if (substr($path, -1) != '/') {
                $path .= '/';
            }
        } else {
            $path = '';
        }
        $path = str_replace('..', '', $path);

        $date  = $GLOBALS['app']->loadDate();
        $title = empty($title)? $file : $title;
        $fast_url = empty($fast_url) ? $title : $fast_url;

        $params = array();
        $params['path']        = $path;
        $params['file']        = $file;
        $params['oldname']     = empty($oldname)? $params['file'] : $oldname;
        $params['title']       = $title;
        $params['description'] = $description;
        $params['now']         = $GLOBALS['db']->Date();

        $dbFile = $this->DBFileInfo($path, $params['oldname']);
        if (Jaws_Error::IsError($dbFile)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if (array_key_exists('id', $dbFile)) {
            // Update
            $fast_url = $this->GetRealFastUrl($fast_url, 'filebrowser', false);
            $params['fast_url'] = $fast_url;

            $sql = '
                UPDATE [[filebrowser]] SET
                    [path]         = {path},
                    [filename]     = {file},
                    [title]        = {title},
                    [description]  = {description},
                    [fast_url]     = {fast_url},
                    [updatetime]   = {now}
                WHERE
                    [path]     = {path}
                  AND
                    [filename] = {oldname}';

            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }

            $GLOBALS['app']->Session->PushLastResponse(_t('FILEBROWSER_FILE_UPDATED', $file), RESPONSE_NOTICE);
        } else {
            //Insert
            $fast_url = $this->GetRealFastUrl($fast_url, 'filebrowser');
            $params['fast_url'] = $fast_url;

            $sql = '
                INSERT INTO [[filebrowser]]
                    ([path], [filename], [title], [description], [fast_url], [createtime], [updatetime])
                VALUES
                    ({path}, {file}, {title}, {description}, {fast_url}, {now}, {now})';

            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }

            $GLOBALS['app']->Session->PushLastResponse(_t('FILEBROWSER_FILE_ADDED', $file), RESPONSE_NOTICE);
        }

        return true;
    }

    /**
     * Delete file or directory information
     *
     * @access  public
     * @param   string  $path   File|Directory path
     * @param   string  $file   File|Directory name
     * @return  bool    True/False
     */
    function DeleteDBFileInfo($path, $file)
    {
        if (!empty($path) && $path != '/') {
            if (substr($path, -1) != '/') {
                $path .= '/';
            }
        } else {
            $path = '';
        }
        $path = str_replace('..', '', $path);

        $params = array();
        $params['path'] = $path;
        $params['file'] = $file;

        $sql = '
            DELETE FROM [[filebrowser]]
            WHERE [path] = {path} AND [filename] = {file}';

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FILEBROWSER_FILE_DELETED', $file), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Creates a directory
     *
     * @access  public
     * @param   string  $path       Where to create it
     * @param   string  $dir_name   Which name
     * @return  bool    Returns true if the directory was created, if not, returns false
     */
    function MakeDir($path, $dir_name)
    {
        if (!empty($path) && $path != '/') {
            if (substr($path, -1) != '/') {
                $path .= '/';
            }
        } else {
            $path = '';
        }
        $path = str_replace('..', '', $path);

        $dir = $this->GetFileBrowserRootDir() . $path . '/' . $dir_name;

        require_once PEAR_PATH. 'File/Util.php';
        $realpath = File_Util::realpath($dir);
        $blackList = explode(',', $this->gadget->GetRegistry('black_list'));
        $blackList = array_map('strtolower', $blackList);

        if (!File_Util::pathInRoot($realpath, $this->GetFileBrowserRootDir()) ||
            in_array(strtolower(basename($realpath)), $blackList) ||
            !Jaws_Utils::mkdir($realpath))
        {
            $GLOBALS['app']->Session->PushLastResponse(_t('FILEBROWSER_ERROR_CANT_CREATE_DIRECTORY', $realpath), RESPONSE_ERROR);
            return false;
        }

        return true;
    }

    /**
     * Deletes a file or directory
     *
     * @access  public
     * @param   string  $path       Where is it
     * @param   string  $filename   The name of the file
     * @return  bool    Returns true if file/directory was deleted without problems, if not, returns false
     */
    function Delete($path, $filename)
    {
        if (!empty($path) && $path != '/') {
            if (substr($path, -1) != '/') {
                $path .= '/';
            }
        } else {
            $path = '';
        }
        $path = str_replace('..', '', $path);

        $file = $path . ((empty($path)? '': DIRECTORY_SEPARATOR)) . $filename;
        $filename = $this->GetFileBrowserRootDir() . DIRECTORY_SEPARATOR . $file;
        $blackList = explode(',', $this->gadget->GetRegistry('black_list'));
        $blackList = array_map('strtolower', $blackList);

        require_once PEAR_PATH. 'File/Util.php';
        $realpath = File_Util::realpath($filename);
        if (!File_Util::pathInRoot($realpath, $this->GetFileBrowserRootDir()) ||
            in_array(strtolower(basename($filename)), $blackList))
        {
            $msgError = is_dir($filename)? _t('FILEBROWSER_ERROR_CANT_DELETE_DIR', $file):
                                           _t('FILEBROWSER_ERROR_CANT_DELETE_FILE', $file);
            $GLOBALS['app']->Session->PushLastResponse($msgError, RESPONSE_ERROR);
            return false;
        }

        if (is_file($filename)) {
            $return = @unlink($filename);
            if (!$return) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FILEBROWSER_ERROR_CANT_DELETE_FILE', $file), RESPONSE_ERROR);
                return false;
            }
        } elseif (is_dir($filename)) {
            require_once JAWS_PATH . 'include/Jaws/FileManagement.php';
            $return = Jaws_FileManagement::FullRemoval($filename);
            if (!$return) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FILEBROWSER_ERROR_CANT_DELETE_DIR', $file), RESPONSE_ERROR);
                return false;
            }
        }

        return true;
    }

    /**
     * Rename a given file or directory
     *
     * @access  public
     * @param   string  $type             file or dir
     * @param   string  $old_filename     Filename to rename
     * @param   string  $new_filename     New Filename
     * @return  bool    Returns file if file/directory was renamed without problems, if not, returns false
     */
    function Rename($path, $old, $new)
    {
        if (!empty($path) && $path != '/') {
            if (substr($path, -1) != '/') {
                $path .= '/';
            }
        } else {
            $path = '';
        }
        $path = str_replace('..', '', $path);
        $oldfile = $this->GetFileBrowserRootDir() . $path . '/' . $old;
        $newfile = $this->GetFileBrowserRootDir() . $path . '/' . $new;

        require_once PEAR_PATH. 'File/Util.php';
        $oldfile = File_Util::realpath($oldfile);
        $newfile = File_Util::realpath($newfile);
        $blackList = explode(',', $this->gadget->GetRegistry('black_list'));
        $blackList = array_map('strtolower', $blackList);

        if (!File_Util::pathInRoot($oldfile, $this->GetFileBrowserRootDir()) ||
            !File_Util::pathInRoot($newfile, $this->GetFileBrowserRootDir()) ||
            in_array(strtolower(basename($oldfile)), $blackList) ||
            in_array(strtolower(basename($newfile)), $blackList))
        {
            $GLOBALS['app']->Session->PushLastResponse(_t('FILEBROWSER_ERROR_CANT_RENAME', $old, $new), RESPONSE_ERROR);
            return false;
        }

        $return = @rename($oldfile, $newfile);
        if ($return) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FILEBROWSER_RENAMED', $old, $new), RESPONSE_NOTICE);
            return true;
        }

        $msgError = _t('FILEBROWSER_ERROR_CANT_RENAME', $old, $new);
        $GLOBALS['app']->Session->PushLastResponse($msgError, RESPONSE_ERROR);
        return false;
    }

}