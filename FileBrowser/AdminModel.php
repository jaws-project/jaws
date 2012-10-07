<?php
/**
 * Filebrowser Admin Gadget
 *
 * @category   GadgetModel
 * @package    FileBrowser
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'gadgets/FileBrowser/Model.php';

class FileBrowserAdminModel extends FileBrowserModel
{
    /**
     * Install the gadget
     *
     * @access  public
     * @return  boolean True on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        if (!Jaws_Utils::is_writable(JAWS_DATA)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_DIRECTORY_UNWRITABLE', JAWS_DATA));
        }

        $new_dir = JAWS_DATA . 'files' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('FILEBROWSER_NAME'));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        //registry keys.
        $GLOBALS['app']->Registry->NewKey('/gadgets/FileBrowser/black_list', 'htaccess');
        $GLOBALS['app']->Registry->NewKey('/gadgets/FileBrowser/root_dir', 'files');
        $GLOBALS['app']->Registry->NewKey('/gadgets/FileBrowser/frontend_avail', 'true');
        $GLOBALS['app']->Registry->NewKey('/gadgets/FileBrowser/virtual_links', 'false');
        $GLOBALS['app']->Registry->NewKey('/gadgets/FileBrowser/order_type', 'filename, false');
        $GLOBALS['app']->Registry->NewKey('/gadgets/FileBrowser/views_limit', '0');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  boolean  Success/Failure (Jaws_Error)
     */
    function UninstallGadget()
    {
        $result = $GLOBALS['db']->dropTable('filebrowser');
        if (Jaws_Error::IsError($result)) {
            $gName  = _t('FILEBROWSER_NAME');
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
            $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
            return new Jaws_Error($errMsg, $gName);
        }

        // Registry keys
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/FileBrowser/black_list');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/FileBrowser/root_dir');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/FileBrowser/frontend_avail');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/FileBrowser/virtual_links');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/FileBrowser/order_type');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/FileBrowser/views_limit');

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  boolean  Success/Failure (Jaws_Error)
     */
    function UpdateGadget($old, $new)
    {
        if (version_compare($old, '0.7.0', '<')) {
            $result = $GLOBALS['db']->dropTable('filebrowser_communities');
            if (Jaws_Error::IsError($result)) {
                // do nothing
            }

            // Registry keys.
            $GLOBALS['app']->Registry->NewKey('/gadgets/FileBrowser/black_list', '.htaccess');
            $GLOBALS['app']->Registry->NewKey('/gadgets/FileBrowser/frontend_avail', 'true');
        }

        if (version_compare($old, '0.7.1', '<')) {
            $GLOBALS['app']->Registry->NewKey('/gadgets/FileBrowser/root_dir', 'files');
        }

        if (version_compare($old, '0.8.0', '<')) {
            $result = $this->installSchema('0.8.0.xml');
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/FileBrowser/ManageFiles',       'false');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/FileBrowser/UploadFiles',       'false');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/FileBrowser/ManageDirectories', 'false');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/FileBrowser/OutputAccess',      'true');
            
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/FileBrowser/AddFile');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/FileBrowser/RenameFile');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/FileBrowser/DeleteFile');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/FileBrowser/AddDir');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/FileBrowser/RenameDir');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/FileBrowser/DeleteDir');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/FileBrowser/ShareDir');

            //Registry key
            $GLOBALS['app']->Registry->NewKey('/gadgets/FileBrowser/virtual_links', 'false');
            $GLOBALS['app']->Registry->NewKey('/gadgets/FileBrowser/order_type', 'filename, false');
        }

        if (version_compare($old, '0.8.1', '<')) {
            //Registry key
            $GLOBALS['app']->Registry->NewKey('/gadgets/FileBrowser/views_limit', '0');
        }

        if (version_compare($old, '0.8.2', '<')) {
            $result = $this->installSchema('schema.xml', '', "0.8.0.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '0.8.3', '<')) {
            $GLOBALS['app']->Registry->Set('/gadgets/FileBrowser/black_list', 'htaccess');
        }

        return true;
    }

    /**
     * Add/Update file or directory information
     *
     * @access  public
     * @param   string  $path File|Directory path
     * @param   string  $file File|Directory name
     * @return  array   A list of properties of files and directories of a certain path and Jaws_Error on failure
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
        $xss   = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $title = empty($title)? $file : $title;
        $fast_url = empty($fast_url) ? $title : $fast_url;

        $params = array();
        $params['path']        = $xss->parse($path);
        $params['file']        = $xss->parse($file);
        $params['oldname']     = empty($oldname)? $params['file'] : $xss->parse($oldname);
        $params['title']       = $xss->parse($title);
        $params['description'] = $xss->parse($description);
        $params['now']         = $GLOBALS['db']->Date();

        $dbFile = $this->DBFileInfo($path, $params['oldname']);
        if (Jaws_Error::IsError($dbFile)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if (array_key_exists('id', $dbFile)) {
            // Update
            $fast_url = $this->GetRealFastUrl($fast_url, 'filebrowser', false);
            $params['fast_url'] = $xss->parse($fast_url);

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
            $params['fast_url'] = $xss->parse($fast_url);

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
     * @param   string  $path File|Directory path
     * @param   string  $file File|Directory name
     * @return  boolean True/False
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

        $xss  = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params = array();
        $params['path'] = $xss->parse($path);
        $params['file'] = $xss->parse($file);

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
     * @param   string  $path     Where to create it
     * @param   string  $dir_name Which name
     * @return  boolean Returns true if the directory was created, if not, returns Jaws_Error
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

        require_once 'File/Util.php';
        $realpath = File_Util::realpath($dir);
        $blackList = explode(',', $GLOBALS['app']->Registry->Get('/gadgets/FileBrowser/black_list'));
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
     * @param   string  $path     Where is it
     * @param   string  $filename The name of the file
     * @return  boolean Returns true if file/directory was deleted without problems, if not, returns Jaws_Error
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
        $blackList = explode(',', $GLOBALS['app']->Registry->Get('/gadgets/FileBrowser/black_list'));
        $blackList = array_map('strtolower', $blackList);

        require_once 'File/Util.php';
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
     * @return  boolean Returns file if file/directory was renamed without problems, if not, returns Jaws_Error
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

        require_once 'File/Util.php';
        $oldfile = File_Util::realpath($oldfile);
        $newfile = File_Util::realpath($newfile);
        $blackList = explode(',', $GLOBALS['app']->Registry->Get('/gadgets/FileBrowser/black_list'));
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