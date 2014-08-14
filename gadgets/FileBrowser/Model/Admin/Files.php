<?php
/**
 * Filebrowser Admin Gadget
 *
 * @category   GadgetModel
 * @package    FileBrowser
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_Model_Admin_Files extends Jaws_Gadget_Model
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
        $path = trim($path, '/');
        $path = str_replace('..', '', $path);

        $title = empty($title)? $file : $title;
        $fast_url = empty($fast_url) ? $title : $fast_url;

        $params['path']        = $path;
        $params['filename']    = $file;
        $params['title']       = $title;
        $params['description'] = $description;
        $params['updatetime']  = Jaws_DB::getInstance()->date();

        $oldname = empty($oldname)? $params['filename'] : $oldname;
        $fModel = $this->gadget->model->load('Files');
        $dbFile = $fModel->DBFileInfo($path, $oldname);
        if (Jaws_Error::IsError($dbFile)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if (!empty($dbFile) && array_key_exists('id', $dbFile)) {
            // Update
            $fast_url = $this->GetRealFastUrl($fast_url, 'filebrowser', false);
            $params['fast_url'] = $fast_url;

            $table = Jaws_ORM::getInstance()->table('filebrowser');
            $res = $table->update($params)->where('path', $path)->and()->where('filename', $oldname)->exec();
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }

            $GLOBALS['app']->Session->PushLastResponse(_t('FILEBROWSER_FILE_UPDATED', $file), RESPONSE_NOTICE);
        } else {
            //Insert
            $fast_url = $this->GetRealFastUrl($fast_url, 'filebrowser');
            $params['fast_url'] = $fast_url;
            unset($params['oldname']);

            $fileTable = Jaws_ORM::getInstance()->table('filebrowser');
            $res = $fileTable->insert($params)->exec();
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }

            if (empty($path) && is_dir($fModel->GetFileBrowserRootDir(). '/'. $file)) {
                $this->gadget->acl->insert('OutputAccess', $res, true);
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
        $path = trim($path, '/');
        $path = str_replace('..', '', $path);

        $fModel = $this->gadget->model->load('Files');
        $dbRow = $fModel->DBFileInfo($path, $file);
        if (!Jaws_Error::isError($dbRow) && !empty($dbRow)) {
            $table = Jaws_ORM::getInstance()->table('filebrowser');
            $res = $table->delete()->where('id', $dbRow['id'])->exec();
            if (!Jaws_Error::IsError($res)) {
                $this->gadget->acl->delete('OutputAccess', $dbRow['id']);
                $GLOBALS['app']->Session->PushLastResponse(_t('FILEBROWSER_FILE_DELETED', $file), RESPONSE_NOTICE);
                return true;
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
        return false;
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
        $path = trim($path, '/');
        $path = str_replace('..', '', $path);

        $file = $path. '/'. $filename;
        $fModel = $this->gadget->model->load('Files');
        $filename = $fModel->GetFileBrowserRootDir(). $file;
        $blackList = explode(',', $this->gadget->registry->fetch('black_list'));
        $blackList = array_map('strtolower', $blackList);

        require_once PEAR_PATH. 'File/Util.php';
        $realpath = File_Util::realpath($filename);
        if (!File_Util::pathInRoot($realpath, $fModel->GetFileBrowserRootDir()) ||
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
        $path = trim($path, '/');
        $path = str_replace('..', '', $path);

        $fModel = $this->gadget->model->load('Files');
        $oldfile = $fModel->GetFileBrowserRootDir(). $path. '/'. $old;
        $newfile = $fModel->GetFileBrowserRootDir(). $path. '/'. $new;

        require_once PEAR_PATH. 'File/Util.php';
        $oldfile = File_Util::realpath($oldfile);
        $newfile = File_Util::realpath($newfile);
        $blackList = explode(',', $this->gadget->registry->fetch('black_list'));
        $blackList = array_map('strtolower', $blackList);

        if (!File_Util::pathInRoot($oldfile, $fModel->GetFileBrowserRootDir()) ||
            !File_Util::pathInRoot($newfile, $fModel->GetFileBrowserRootDir()) ||
            in_array(strtolower(basename($oldfile)), $blackList) ||
            in_array(strtolower(basename($newfile)), $blackList))
        {
            $GLOBALS['app']->Session->PushLastResponse(
                _t('FILEBROWSER_ERROR_CANT_RENAME', $old, $new),
                RESPONSE_ERROR
            );
            return false;
        }

        $return = @rename($oldfile, $newfile);
        if ($return) {
            $GLOBALS['app']->Session->PushLastResponse(
                _t('FILEBROWSER_RENAMED', $old, $new),
                RESPONSE_NOTICE
            );
            return true;
        }

        $msgError = _t('FILEBROWSER_ERROR_CANT_RENAME', $old, $new);
        $GLOBALS['app']->Session->PushLastResponse($msgError, RESPONSE_ERROR);
        return false;
    }

}