<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Actions_Directory extends Jaws_Gadget_HTML
{
    /**
     * Builds file management UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Directory()
    {
        $GLOBALS['app']->Layout->AddHeadLink('gadgets/Directory/resources/site_style.css');
        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->loadTemplate('Workspace.html');
        $tpl->SetBlock('workspace');

        $tpl->SetVariable('title', _t('DIRECTORY_NAME'));
        $tpl->SetVariable('lbl_new_dir', _t('DIRECTORY_NEW_DIR'));
        $tpl->SetVariable('lbl_new_file', _t('DIRECTORY_NEW_FILE'));
        $tpl->SetVariable('lbl_search', _t('GLOBAL_SEARCH'));
        $tpl->SetVariable('lbl_all_files', _t('DIRECTORY_FILTER_ALL_FILES'));
        $tpl->SetVariable('lbl_shared_files', _t('DIRECTORY_FILTER_SHARED_FILES'));
        $tpl->SetVariable('lbl_foreign_files', _t('DIRECTORY_FILTER_FOREIGN_FILES'));
        $tpl->SetVariable('new_dir', 'gadgets/Directory/images/new-dir.png');
        $tpl->SetVariable('new_file', 'gadgets/Directory/images/new-file.png');
        $tpl->SetVariable('search', 'gadgets/Directory/images/search.png');

        if ($this->gadget->GetPermission('ShareFile')) {
            $tpl->SetBlock('workspace/share');
            $tpl->SetVariable('lbl_share', _t('DIRECTORY_SHARE'));
            $tpl->ParseBlock('workspace/share');
        }

        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $tpl->SetVariable('UID', $user);
        $tpl->SetVariable('lbl_props', _t('DIRECTORY_PROPERTIES'));
        $tpl->SetVariable('lbl_edit', _t('GLOBAL_EDIT'));
        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('lbl_move', _t('DIRECTORY_MOVE'));
        $tpl->SetVariable('lbl_download', _t('DIRECTORY_DOWNLOAD'));
        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_created', _t('DIRECTORY_FILE_CREATED'));
        $tpl->SetVariable('lbl_modified', _t('DIRECTORY_FILE_MODIFIED'));
        $tpl->SetVariable('lbl_username', _t('DIRECTORY_FILE_OWNER'));
        $tpl->SetVariable('lbl_size', _t('DIRECTORY_FILE_SIZE'));
        $tpl->SetVariable('alertShortQuery', _t('DIRECTORY_ERROR_SHORT_QUERY'));
        $tpl->SetVariable('confirmDirDelete', _t('DIRECTORY_CONFIRM_DIR_DELETE'));
        $tpl->SetVariable('confirmFileDelete', _t('DIRECTORY_CONFIRM_FILE_DELETE'));
        $tpl->SetVariable('imgDeleteFile', STOCK_DELETE);
        $tpl->SetVariable('site_url', $GLOBALS['app']->getSiteURL('/'));
        $theme = $GLOBALS['app']->GetTheme();
        $icon_url = is_dir($theme['url'] . 'mimetypes')?
            $theme['url'] . 'mimetypes/' : 'images/mimetypes/';
        $tpl->SetVariable('icon_url', $icon_url);

        // File template
        $tpl->SetBlock('workspace/fileTemplate');
        $tpl->SetVariable('id', '{id}');
        $tpl->SetVariable('title', '{title}');
        $tpl->SetVariable('description', '{description}');
        $tpl->SetVariable('icon', '{icon}');
        $tpl->SetVariable('type', '{type}');
        $tpl->SetVariable('size', '{size}');
        $tpl->SetVariable('username', '{username}');
        $tpl->SetVariable('created', '{created}');
        $tpl->SetVariable('modified', '{modified}');
        $tpl->SetVariable('shared', '{shared}');
        $tpl->SetVariable('foreign', '{foreign}');
        $tpl->SetVariable('public', '{public}');
        $tpl->ParseBlock('workspace/fileTemplate');

        // Status bar
        $tpl->SetBlock('workspace/statusbar');
        $tpl->SetVariable('title', '{title}');
        $tpl->SetVariable('size', '{size}');
        $tpl->SetVariable('created', '{created}');
        $tpl->SetVariable('modified', '{modified}');
        $tpl->ParseBlock('workspace/statusbar');

        // Display probabley responses
        $response = $GLOBALS['app']->Session->PopResponse('Directory');
        if ($response) {
            $tpl->SetVariable('response', $response['text']);
            $tpl->SetVariable('response_type', $response['type']);
        }

        $tpl->ParseBlock('workspace');
        return $tpl->Get();
    }

    /**
     * Fetches list of files
     *
     * @access  public
     * @return  array   File data or an empty array
     */
    function GetFiles()
    {
        $flags = jaws()->request->fetch(array('id', 'shared', 'foreign'));
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $files = $model->GetFiles($flags['id'], $user, $flags['shared'], $flags['foreign']);
        if (Jaws_Error::IsError($files)){
            return array();
        }
        $objDate = $GLOBALS['app']->loadDate();
        foreach ($files as &$file) {
            $file['created'] = $objDate->Format($file['createtime'], 'n/j/Y g:i a');
            $file['modified'] = $objDate->Format($file['updatetime'], 'n/j/Y g:i a');
        }
        return $files;
    }

    /**
     * Fetches data of a file/directory
     *
     * @access  public
     * @return  array   File data or an empty array
     */
    function GetFile()
    {
        $id = jaws()->request->fetch('id');
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $access = $model->CheckAccess($id, $user);
        if ($access !== true) {
            return array();
        }
        $file = $model->GetFile($id);
        if (Jaws_Error::IsError($file)) {
            return array();
        }
        $objDate = $GLOBALS['app']->loadDate();
        $file['created'] = $objDate->Format($file['createtime'], 'n/j/Y g:i a');
        $file['modified'] = $objDate->Format($file['updatetime'], 'n/j/Y g:i a');

        // Shared for
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Share');
        $users = $model->GetFileUsers($id);
        if (!Jaws_Error::IsError($users)) {
            $uid_set = array();
            foreach ($users as $user) {
                $uid_set[] = $user['username'];
            }
            $file['users'] = implode(', ', $uid_set);
        }

        return $file;
    }

    /**
     * Fetches path of a file/directory
     *
     * @access  public
     * @return  array   Directory hierarchy
     */
    function GetPath()
    {
        $id = jaws()->request->fetch('id');
        $path = array();
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $model->GetPath($id, $path);
        return $path;
    }

    /**
     * Builds a (sub)tree of directories
     *
     * @access  public
     * @return  string   XHTML tree
     */
    function GetTree()
    {
        $tree = '';
        $exclude = (int)jaws()->request->fetch('id');
        $this->BuildTree(0, $exclude, $tree);

        $tpl = $this->gadget->loadTemplate('Move.html');
        $tpl->SetBlock('tree');
        $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('tree', $tree);
        $tpl->ParseBlock('tree');
        return $tpl->Get();
    }

    /**
     * Builds a (sub)tree of directories
     *
     * @access  public
     * @param   int     $root       File ID as tree root
     * @param   int     $exclude    File ID to be excluded in tree
     * @param   string  $tree       XHTML tree
     * @return  void
     */
    function BuildTree($root = 0, $exclude = null, &$tree)
    {
        $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $dirs = $model->GetFiles($root, $user, null, null, true);
        if (Jaws_Error::IsError($dirs)) {
            return;
        }
        if (!empty($dirs)) {
            $tree .= '<ul>';
            foreach ($dirs as $dir) {
                if ($dir['id'] == $exclude || $dir['user'] !== $dir['owner']) {
                    continue;
                }
                $tree .= "<li><a id='node_{$dir['id']}'>{$dir['title']}</a>";
                $this->BuildTree($dir['id'], $exclude, $tree);
                $tree .= "</li>";
            }
            $tree .= '</ul>';
        }
    }

    /**
     * Moves file/directory to the given target directory
     *
     * @access  public
     * @return  array   Response array
     */
    function Move()
    {
        try {
            $data = jaws()->request->fetch(array('id', 'target'));
            if ($data['id'] === null || $data['target'] === null) {
                throw new Exception(_t('DIRECTORY_ERROR_MOVE'));
            }

            $id = (int)$data['id'];
            $target = (int)$data['target'];
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');

            // Validate source/target
            $file = $model->GetFile($id);
            if (Jaws_Error::IsError($file)) {
                throw new Exception($file->getMessage());
            }
            if ($target !== 0) {
                $dir = $model->GetFile($target);
                if (Jaws_Error::IsError($dir)) {
                    throw new Exception($dir->getMessage());
                }
                if (!$dir['is_dir']) {
                    throw new Exception(_t('DIRECTORY_ERROR_MOVE'));
                }
            }

            // Stop moving to itself, it's parent or it's children
            if ($target == $id || $target == $file['parent']) {
                throw new Exception(_t('DIRECTORY_ERROR_MOVE'));
            }
            $path = array();
            $id_set = array();
            $model->GetPath($target, $path);
            foreach ($path as $d) {
                $id_set[] = $d['id'];
            }
            if (in_array($id, $id_set)) {
                throw new Exception(_t('DIRECTORY_ERROR_MOVE'));
            }

            // Validate user
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
            // FIXME: we should be able to move into a shared directory
            // if ($file['user'] != $user || $dir['user'] != $user) {
            if ($file['user'] != $user) {
                throw new Exception(_t('DIRECTORY_ERROR_MOVE'));
            }

            // Let's perform move
            $res = $model->Move($id, $target);
            if (Jaws_Error::IsError($res)) {
                throw new Exception($res->getMessage());
            }
        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_MOVE'), RESPONSE_NOTICE);
    }

    /**
     * Searches among files and directories for passed query
     *
     * @access  public
     * @return  array   Response array
     */
    function Search()
    {
        try {
            $query = jaws()->request->fetch('query', 'post');
            if ($query === null || strlen($query) < 2) {
                throw new Exception(_t('DIRECTORY_ERROR_SEARCH'));
            }
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
            $files = $model->GetFiles(null, $user, null, null, null, $query);
            if (Jaws_Error::IsError($files)){
                return array();
            }
            $objDate = $GLOBALS['app']->loadDate();
            foreach ($files as &$file) {
                $file['created'] = $objDate->Format($file['createtime'], 'n/j/Y g:i a');
                $file['modified'] = $objDate->Format($file['updatetime'], 'n/j/Y g:i a');
            }
        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('DIRECTORY_NOTICE_SEARCH_RESULT', count($files)),
            RESPONSE_NOTICE,
            $files);
    }
}