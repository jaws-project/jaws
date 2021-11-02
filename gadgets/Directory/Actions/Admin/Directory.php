<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 */
class Directory_Actions_Admin_Directory extends Directory_Actions_Admin_Common
{
    /**
     * Builds file management UI
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function Directory()
    {
        $standalone = (bool)$this->gadget->request->fetch('standalone', 'get');
        $calType = strtolower($this->gadget->registry->fetch('calendar', 'Settings'));
        $calLang = strtolower($this->gadget->registry->fetch('admin_language', 'Settings'));
        if ($calType != 'gregorian') {
            $this->app->layout->addScript("libraries/piwi/piwidata/js/jscalendar/$calType.js");
        }
        $this->app->layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar.js');
        $this->app->layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar-setup.js');
        $this->app->layout->addScript("libraries/piwi/piwidata/js/jscalendar/lang/calendar-$calLang.js");
        $this->app->layout->addLink('libraries/piwi/piwidata/js/jscalendar/calendar-blue.css');

        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('Workspace.html');
        $tpl->SetBlock('workspace');

        if ($standalone) {
            $tpl->SetVariable('standalone', 'standalone');
            $this->gadget->define('currentAction', 'Browse');
            $tpl->SetVariable('home_url', $this->gadget->url('Directory', array('standalone' => '1')));

            $tpl->SetBlock('workspace/standalone');
            $tpl->ParseBlock('workspace/standalone');

            $editor = $this->app->getEditor();
            if ($editor === 'TinyMCE') {
            } elseif ($editor === 'CKEditor') {
                $getParams = $this->gadget->request->fetch(array('CKEditor', 'CKEditorFuncNum', 'langCode'), 'get');
                $extraParams = '&amp;CKEditor=' . $getParams['CKEditor'] .
                               '&amp;CKEditorFuncNum=' . $getParams['CKEditorFuncNum'] .
                               '&amp;langCode=' . $getParams['langCode'];

                $ckFuncIndex = $this->gadget->request->fetch('CKEditorFuncNum', 'get');
                $this->gadget->define('ckFuncIndex', $ckFuncIndex);
            }
        } else {
            $tpl->SetVariable('menubar', $this->MenuBar('Directory'));
            $this->gadget->define('currentAction', 'Directory');
            $tpl->SetVariable('home_url', BASE_SCRIPT . '?reqGadget=Directory');
        }

        $tpl->SetVariable('lbl_search', Jaws::t('SEARCH'));
        $tpl->SetVariable('lbl_adv_search', _t('DIRECTORY_ADVANCED_SEARCH'));
        $tpl->SetVariable('lbl_new_dir', _t('DIRECTORY_NEW_DIR'));
        $tpl->SetVariable('lbl_new_file', _t('DIRECTORY_NEW_FILE'));
        $tpl->SetVariable('lbl_props', _t('DIRECTORY_PROPERTIES'));
        $tpl->SetVariable('lbl_edit', Jaws::t('EDIT'));
        $tpl->SetVariable('lbl_delete', Jaws::t('DELETE'));
        $tpl->SetVariable('lbl_move', _t('DIRECTORY_MOVE'));
        $tpl->SetVariable('lbl_dl', _t('DIRECTORY_DOWNLOAD'));
        $tpl->SetVariable('lbl_folder', _t('DIRECTORY_FILE_TYPE_FOLDER'));
        $tpl->SetVariable('lbl_text', _t('DIRECTORY_FILE_TYPE_TEXT'));
        $tpl->SetVariable('lbl_image', _t('DIRECTORY_FILE_TYPE_IMAGE'));
        $tpl->SetVariable('lbl_audio', _t('DIRECTORY_FILE_TYPE_AUDIO'));
        $tpl->SetVariable('lbl_video', _t('DIRECTORY_FILE_TYPE_VIDEO'));
        $tpl->SetVariable('lbl_archive', _t('DIRECTORY_FILE_TYPE_ARCHIVE'));
        $tpl->SetVariable('lbl_other', _t('DIRECTORY_FILE_TYPE_OTHER'));

        $tpl->SetVariable('type_folder', Directory_Info::FILE_TYPE_FOLDER);
        $tpl->SetVariable('type_text', Directory_Info::FILE_TYPE_TEXT);
        $tpl->SetVariable('type_image', Directory_Info::FILE_TYPE_IMAGE);
        $tpl->SetVariable('type_audio', Directory_Info::FILE_TYPE_AUDIO);
        $tpl->SetVariable('type_video', Directory_Info::FILE_TYPE_VIDEO);
        $tpl->SetVariable('type_archive', Directory_Info::FILE_TYPE_ARCHIVE);
        $tpl->SetVariable('type_other', Directory_Info::FILE_TYPE_UNKNOWN);

        $tpl->SetVariable('img_new_dir', STOCK_DIRECTORY_NEW);
        $tpl->SetVariable('img_new_file', STOCK_NEW);
        $tpl->SetVariable('img_props', 'images/stock/properties.png');
        $tpl->SetVariable('img_edit', STOCK_EDIT);
        $tpl->SetVariable('img_delete', STOCK_DELETE);
        $tpl->SetVariable('img_move', STOCK_RIGHT);
        $tpl->SetVariable('img_dl', STOCK_SAVE);
        $tpl->SetVariable('img_search', STOCK_SEARCH);

        $dir_id = (int)$this->gadget->request->fetch('id');
        $this->gadget->define('currentDir', $dir_id);
        $tpl->SetVariable('home_title', _t('DIRECTORY_HOME'));
        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_created', _t('DIRECTORY_FILE_CREATED'));
        $tpl->SetVariable('lbl_modified', _t('DIRECTORY_FILE_MODIFIED'));
        $tpl->SetVariable('lbl_term', _t('DIRECTORY_FILE_TERM'));
        $tpl->SetVariable('lbl_tags', _t('DIRECTORY_FILE_TAGS'));
        $tpl->SetVariable('lbl_type', _t('DIRECTORY_FILE_TYPE'));
        $tpl->SetVariable('lbl_owner', _t('DIRECTORY_FILE_OWNER'));
        $tpl->SetVariable('lbl_published', Jaws::t('PUBLISHED'));
        $tpl->SetVariable('lbl_yes', Jaws::t('YES'));
        $tpl->SetVariable('lbl_no', Jaws::t('NO'));
        $tpl->SetVariable('lbl_size', _t('DIRECTORY_FILE_SIZE'));
        $tpl->SetVariable('lbl_start_date', _t('DIRECTORY_FILE_FROM_DATE'));
        $tpl->SetVariable('lbl_end_date', _t('DIRECTORY_FILE_TO_DATE'));
        $this->gadget->define('confirmDelete', _t('DIRECTORY_CONFIRM_DELETE'));
        $tpl->SetVariable('confirmFileDelete', _t('DIRECTORY_CONFIRM_FILE_DELETE'));
        $this->gadget->define('imgDeleteFile', STOCK_DELETE);
        $theme = $this->app->GetTheme();
        $icon_url = is_dir($theme['url'] . 'mimetypes')?
            $theme['url'] . 'mimetypes/' : 'images/mimetypes/';
        $this->gadget->define('icon_url', $icon_url);

        // Start date
        $cal_type = $this->gadget->registry->fetch('calendar', 'Settings');
        $cal_lang = $this->gadget->registry->fetch('site_language', 'Settings');
        $datePicker =& Piwi::CreateWidget('DatePicker', 'start_date');
        $datePicker->setCalType($cal_type);
        $datePicker->setLanguageCode($cal_lang);
        $datePicker->setDateFormat('%Y-%m-%d');
        $datePicker->setStyle('width:80px');
        $tpl->SetVariable('start_date', $datePicker->Get());

        // End date
        $datePicker =& Piwi::CreateWidget('DatePicker', 'end_date');
        $datePicker->setDateFormat('%Y-%m-%d');
        $datePicker->setCalType($cal_type);
        $datePicker->setLanguageCode($cal_lang);
        $datePicker->setStyle('width:80px');
        $tpl->SetVariable('end_date', $datePicker->Get());

        // File template
        $tpl->SetBlock('workspace/fileTemplate');
        $tpl->SetVariable('id', '{id}');
        $tpl->SetVariable('url', '{url}');
        $tpl->SetVariable('title', '{title}');
        $tpl->SetVariable('description', '{description}');
        $tpl->SetVariable('icon', '{icon}');
        $tpl->SetVariable('thumbnail', '{thumbnail}');
        $tpl->SetVariable('type', '{type}');
        $tpl->SetVariable('size', '{size}');
        $tpl->SetVariable('nickname', '{nickname}');
        $tpl->SetVariable('userlink', '{userlink}');
        $tpl->SetVariable('published_str', '{published_str}');
        $tpl->SetVariable('created', '{created}');
        $tpl->SetVariable('modified', '{modified}');
        $tpl->ParseBlock('workspace/fileTemplate');

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
        $data = $this->gadget->request->fetch(array('parent'));
        $modelFiles = $this->gadget->model->load('Files');
        $data['public'] = true;
        $files = $modelFiles->GetFiles($data);
        if (Jaws_Error::IsError($files)){
            return array();
        }
        $objDate = Jaws_Date::getInstance();
        $action = $this->gadget->request->fetch('curr_action');
        foreach ($files as &$file) {
            if ($file['is_dir']) {
                $file['url'] = BASE_SCRIPT . "?reqGadget=Directory&reqAction=$action&id=" . $file['id'];
            } else {
                $file['link'] = $this->gadget->urlMap(
                    'Directory',
                    array('id' => $file['id']),
                    array('absolute' => true)
                );
            }
            $file['userlink'] = $this->app->map->GetMappedURL(
                'Users',
                'Profile',
                array('user' => $file['username'])
            );
            $file['published_str'] = $file['published'] ? Jaws::t('YES'): Jaws::t('NO');
            $file['created'] = $objDate->Format($file['create_time'], 'n/j/Y g:i a');
            $file['modified'] = $objDate->Format($file['update_time'], 'n/j/Y g:i a');
            $file['thumbnail'] = $modelFiles->GetThumbnailURL($file['host_filename']);

            // Fetch tags
            $file['tags'] = array();
            if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
                $tModel = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
                $tags = $tModel->GetReferenceTags('Directory', 'file', $file['id']);
                $file['tags'] = implode(', ', array_filter($tags));
            }
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
        $id = $this->gadget->request->fetch('id', 'post');
        $file = $this->gadget->model->load('Files')->GetFile($id);
        if (Jaws_Error::IsError($file)) {
            return array();
        }
        $objDate = Jaws_Date::getInstance();
        $file['created'] = $objDate->Format($file['create_time'], 'n/j/Y g:i a');
        $file['modified'] = $objDate->Format($file['update_time'], 'n/j/Y g:i a');

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
        $action = $this->gadget->request->fetch('curr_action');
        $id = $this->gadget->request->fetch('id');
        $path = array();
        $this->gadget->model->load('Files')->GetPath($id, $path);
        foreach($path as &$p) {
            $p['url'] = BASE_SCRIPT . "?reqGadget=Directory&reqAction=$action&id=" . $p['id'];
        }
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
        $exclude = $this->gadget->request->fetch('id_set');
        $exclude = empty($exclude)? array() : explode(',', $exclude);
        $this->BuildTree(0, $exclude, $tree);

        $tpl = $this->gadget->template->loadAdmin('Move.html');
        $tpl->SetBlock('tree');
        $tpl->SetVariable('lbl_ok', Jaws::t('OK'));
        $tpl->SetVariable('lbl_cancel', Jaws::t('CANCEL'));
        $tpl->SetVariable('tree', $tree);
        $tpl->ParseBlock('tree');
        return $tpl->Get();
    }

    /**
     * Builds a (sub)tree of directories
     *
     * @access  public
     * @param   int     $root       File ID as tree root
     * @param   array   $exclude    Set of IDs to be excluded in tree
     * @param   string  $tree       XHTML tree
     * @return  void
     */
    function BuildTree($root = 0, $exclude = array(), &$tree)
    {
        $user = (int)$this->app->session->user->id;
        $dirs = $this->gadget->model->load('Files')->GetFiles(array('parent' => $root, 'is_dir' => true));
        if (Jaws_Error::IsError($dirs)) {
            return;
        }
        if (!empty($dirs)) {
            $tree .= '<ul>';
            foreach ($dirs as $dir) {
                if (in_array($dir['id'], $exclude)) {
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
     * Deletes passed file(s)/directorie(s)
     *
     * @access  public
     * @return  mixed   Response array
     */
    function Delete()
    {
        $id_set = $this->gadget->request->fetch('id_set');
        $id_set = explode(',', $id_set);
        if (empty($id_set)) {
            return $this->gadget->session->response(
                _t('DIRECTORY_ERROR_DELETE'),
                RESPONSE_ERROR
            );
        }

        $user = (int)$this->app->session->user->id;
        $fault = false;
        foreach ($id_set as $id) {
            // Validate file
            $file = $this->gadget->model->load('Files')->GetFile($id);
            if (Jaws_Error::IsError($file)) {
                $fault = true;
                continue;
            }

            // Delete file/directory
            $res = $this->gadget->model->loadAdmin('Files')->DeleteFile($file);
            if (Jaws_Error::IsError($res)) {
                $fault = true;
            }

            // Delete tags
            if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
                $tModel = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
                $tModel->DeleteReferenceTags('Directory', 'file', $id);
            }
        }

        if ($fault === true) {
            return $this->gadget->session->response(
                _t('DIRECTORY_WARNING_DELETE'),
                RESPONSE_WARNING
            );
        } else {
            return $this->gadget->session->response(
                _t('DIRECTORY_NOTICE_ITEMS_DELETED'),
                RESPONSE_NOTICE
            );
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
        $data = $this->gadget->request->fetch(array('id_set', 'target'));
        if (empty($data['id_set']) || is_null($data['target'])) {
            return $this->gadget->session->response(
                _t('DIRECTORY_ERROR_MOVE'),
                RESPONSE_ERROR
            );
        }

        $id_set = explode(',', $data['id_set']);
        $target = (int)$data['target'];
        $modelFiles = $this->gadget->model->load('Files');

        // Validate target
        if ($target !== 0) {
            $dir = $modelFiles->GetFile($target);
            if (Jaws_Error::IsError($dir) || !$dir['is_dir']) {
                return $this->gadget->session->response(
                    _t('DIRECTORY_ERROR_MOVE'),
                    RESPONSE_ERROR
                );
            }
        }

        $fault = false;
        foreach ($id_set as $id) {
            // Prevent moving to itself
            if ($target == $id) {
                $fault = true;
                continue;
            }

            // Validate file
            $file = $modelFiles->GetFile($id);
            if (Jaws_Error::IsError($file)) {
                $fault = true;
                continue;
            }

            // Prevent moving to it's parent
            if ($target == $file['parent']) {
                $fault = true;
                continue;
            }

            // Prevent moving to it's children
            $path = array();
            $pathArr = array();
            $modelFiles->GetPath($target, $path);
            foreach ($path as $dir) {
                $pathArr[] = $dir['id'];
            }
            if (in_array($id, $pathArr)) {
                $fault = true;
                continue;
            }

            // Let's perform move
            // FIXME: we can move all files at once
            $res = $this->gadget->model->loadAdmin('Files')->Move($id, $target);
            if (Jaws_Error::IsError($res)) {
                $fault = true;
                continue;
            }
        }

        if ($fault === true) {
            return $this->gadget->session->response(
                _t('DIRECTORY_WARNING_MOVE'),
                RESPONSE_WARNING
            );
        } else {
            return $this->gadget->session->response(
                _t('DIRECTORY_NOTICE_ITEMS_MOVED'),
                RESPONSE_NOTICE
            );
        }
    }

    /**
     * Searches among files and directories by passed query
     *
     * @access  public
     * @return  array   Response array
     */
    function Search()
    {
        $data = $this->gadget->request->fetch(array('id', 'file_filter', 'file_search', 'file_published',
            'file_type', 'file_size', 'start_date', 'end_date'));

        $jdate = Jaws_Date::getInstance();
        $start_date = $end_date = '';
        if (!empty($data['start_date'])) {
            $start_date = $jdate->ToBaseDate(preg_split('/[- :]/', $data['start_date']));
            $start_date = $this->app->UserTime2UTC($start_date['timestamp']);
        }
        if (!empty($data['end_date'])) {
            $end_date = $jdate->ToBaseDate(preg_split('/[- :]/', $data['end_date'].' 23:59:59'));
            $end_date = $this->app->UserTime2UTC($end_date['timestamp']);
        }
        $date = array($start_date, $end_date);

        $model = $this->gadget->model->load('Files');
        $params = array();
        $params['parent'] = $data['id'];
        $params['query'] = $data['file_search'];
        if ($data['file_type'] !== '') {
            $params['file_type'] = $data['file_type'];
            if ($data['file_type'] == 1) {
                $params['is_dir'] = true;
            }
        }
        $params['file_size'] = ($data['file_size'] == '0') ? null : explode(',', $data['file_size']);
        $params['date'] = $date;
        if ($data['file_published'] != '') {
            $params['published'] = ($data['file_published'] == '1') ? true : false;
        }

        $files = $model->GetFiles($params);
        if (Jaws_Error::IsError($files)){
            return $this->gadget->session->response($files->getMessage(), RESPONSE_ERROR);
        }

        $objDate = Jaws_Date::getInstance();
        foreach ($files as &$file) {
            if ($file['is_dir']) {
                $file['url'] = BASE_SCRIPT . '?reqGadget=Directory&reqAction=Directory&id=' . $file['id'];
            }
            $file['published_str'] = $file['published'] ? Jaws::t('YES'): Jaws::t('NO');
            $file['created'] = $objDate->Format($file['create_time'], 'n/j/Y g:i a');
            $file['modified'] = $objDate->Format($file['update_time'], 'n/j/Y g:i a');
            $file['thumbnail'] = $model->GetThumbnailURL($file['host_filename']);

        }

        return $this->gadget->session->response(
            _t('DIRECTORY_NOTICE_SEARCH_RESULT', count($files)),
            RESPONSE_NOTICE,
            $files);
    }
}