<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 */
class Directory_Actions_Directory extends Jaws_Gadget_Action
{
    /**
     * Get Display action params
     *
     * @access  public
     * @return  array list of Display action params
     */
    function DirectoryLayoutParams()
    {
        $result[] = array('title' => _t('DIRECTORY_FILE_TYPE'), 'value' =>
            array(
                0 => _t('GLOBAL_ALL'),
                Directory_Info::FILE_TYPE_FOLDER  => _t('DIRECTORY_FILE_TYPE_FOLDER'),
                Directory_Info::FILE_TYPE_TEXT    => _t('DIRECTORY_FILE_TYPE_TEXT'),
                Directory_Info::FILE_TYPE_IMAGE   => _t('DIRECTORY_FILE_TYPE_IMAGE'),
                Directory_Info::FILE_TYPE_AUDIO   => _t('DIRECTORY_FILE_TYPE_AUDIO'),
                Directory_Info::FILE_TYPE_VIDEO   => _t('DIRECTORY_FILE_TYPE_VIDEO'),
                Directory_Info::FILE_TYPE_ARCHIVE => _t('DIRECTORY_FILE_TYPE_ARCHIVE'),
                Directory_Info::FILE_TYPE_UNKNOWN => _t('DIRECTORY_FILE_TYPE_OTHER'),
            ));

        $result[] = array(
            'title' => _t('GLOBAL_ORDERBY'),
            'value' => array(
                1 => _t('GLOBAL_CREATETIME') . ' &uarr;',
                2 => _t('GLOBAL_CREATETIME') . ' &darr;',
            )
        );

        $result[] = array(
            'title' => _t('GLOBAL_COUNT'),
            'value' =>  $this->gadget->registry->fetch('files_limit')
        );

        return $result;
    }

    /**
     * Builds directory and file navigation UI
     *
     * @param   int     $type       File type (for normal action = null)
     * @param   int     $orderBy    Order by
     * @param   int     $limit      Forms limit
     * @access  public
     * @return  string  XHTML UI
     */
    function Directory($type = null, $orderBy = 0, $limit = 0)
    {
        $id = ($type == null)? (int)$this->gadget->request->fetch('id') : 0;
        if (!empty($id)) {
            $file = $this->gadget->model->load('Files')->GetFile($id);
            if (Jaws_Error::IsError($file) || empty($file) || !$file['is_dir']) {
                return Jaws_HTTPError::Get(404);
            }
        }

        $this->AjaxMe('index.js');
        $this->gadget->define('confirmDelete', _t('DIRECTORY_CONFIRM_DELETE'));

        $standalone = (bool)$this->gadget->request->fetch('standalone');
        if ($standalone) {
            $this->SetActionMode('Directory', 'standalone', 'normal');
        }

        if ($GLOBALS['app']->requestedActionMode == ACTION_MODE_NORMAL) {
            $showPreview = false;
            $tpl = $this->gadget->template->load('Directory.html');
        } else {
            $showPreview = true;
            $tpl = $this->gadget->template->load('DirBox.html');
        }
        $tpl->SetBlock('directory');

        $response = $GLOBALS['app']->Session->PopResponse('Directory.SaveFile');
        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $this->SetTitle(_t('DIRECTORY_ACTIONS_DIRECTORY'));
        $tpl->SetVariable('gadget_title', _t('DIRECTORY_ACTIONS_DIRECTORY'));

        // parse files, filters, pagination
        $this->ListFiles($tpl, $id, $type, $showPreview, $orderBy, $limit);

        $tpl->SetVariable('root', _t('DIRECTORY_HOME'));
        $tpl->SetVariable('root_url', $this->gadget->urlMap('Directory'));
        $tpl->SetVariable('path', $this->GetPath($id));

        if ($this->gadget->GetPermission('UploadFiles')) {
            $tpl->SetBlock('directory/upload_files');
            $tpl->SetVariable('upload', _t('DIRECTORY_UPLOAD_FILE'));
            $tpl->ParseBlock('directory/upload_files');
        }

        // if user has permission, display upload area
        if ($GLOBALS['app']->Session->Logged()) {
            $tpl->SetBlock('directory/upload_form');
            $tpl->SetVariable('lbl_file', _t('DIRECTORY_FILE'));
            $tpl->SetVariable('lbl_thumbnail', _t('DIRECTORY_THUMBNAIL'));
            $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
            $tpl->SetVariable('lbl_desc', _t('DIRECTORY_FILE_DESC'));
            $tpl->SetVariable('lbl_tags', _t('DIRECTORY_FILE_TAGS'));
            $tpl->SetVariable('lbl_public', _t('DIRECTORY_FILE_PUBLIC'));
            $tpl->SetVariable('lbl_url', _t('DIRECTORY_FILE_URL'));
            $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
            $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));

            $description = '';
            $descriptionEditor =& $GLOBALS['app']->LoadEditor(
                'Directory', 'description', Jaws_XSS::defilter($description), false
            );
            $descriptionEditor->setId('description');
            $descriptionEditor->TextArea->SetRows(8);
            $tpl->SetVariable('description', $descriptionEditor->Get());

            $tpl->SetVariable('parent', $id);
            $tpl->SetVariable('referrer', bin2hex(Jaws_Utils::getRequestURL(true)));
            if ($this->gadget->GetPermission('PublishFiles')) {
                $tpl->SetBlock('directory/upload_form/published');
                $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
                if (isset($fileInfo['published']) && $fileInfo['published']) {
                    $tpl->SetVariable('published_checked', 'checked');
                }
                $tpl->ParseBlock('directory/upload_form/published');
            }

            $tpl->SetVariable('root', _t('DIRECTORY_HOME'));
            $tpl->SetVariable('root_url', $this->gadget->urlMap('Directory'));

            $tpl->ParseBlock('directory/upload_form');
        }

        $tpl->ParseBlock('directory');
        return $tpl->Get();
    }

    /**
     * Fetches and displays list of directories/files
     *
     * @access  public
     * @param   int     $parent
     * @param   null    $type
     * @param   int     $orderBy    Order by
     * @param   int     $limit      Forms limit
     * @return string HTML content
     */
    function ListFiles(&$tpl, $parent = 0, $type = null, $preview = false, $orderBy = 0, $limit = 0)
    {
        $params = array();
        $filters = $this->gadget->request->fetch(
            array('filter_file_type', 'filter_file_size', 'filter_from_date', 'filter_to_date', 'filter_order'),
            'post'
        );

        // Layout action
        $isLayoutAction = $GLOBALS['app']->requestedActionMode == ACTION_MODE_LAYOUT;
        if ($isLayoutAction) {
            $page = 0;
            $params['file_type'] = (int)$type;
            if ($params['file_type'] == 1) {
                $params['is_dir'] = true;
            }
        } else {
            $get = $this->gadget->request->fetch(array('type', 'order', 'page'), 'get');
            $page = (int)$get['page'];
            $params['file_type'] = (int)$get['type'];
            if (!empty($get['order'])) {
                $orderBy = (int)$get['order'];
            }
        }

        $params['limit']  = ($limit > 0) ? $limit : (int)$this->gadget->registry->fetch('items_per_page');
        $params['offset'] = ($page == 0)? 0 : $params['limit'] * ($page - 1);
        $params['parent'] = (int)$parent;
        $params['public'] = true;
        $params['published'] = true;

        $user = $this->gadget->request->fetch('user', 'get');
        if (!empty($user)) {
            $params['user'] = (int)$user;
            if ($params['user'] == (int)$GLOBALS['app']->Session->GetAttribute('user')) {
                unset($params['public'], $params['published']);
            }
        }

        // check filters
        if (!is_null($filters['filter_file_type'])) {
            $params['file_type'] = $filters['filter_file_type'];
            if ($filters['filter_file_type'] == 1) {
                $params['is_dir'] = true;
            }
        }
        if (!empty($filters['filter_file_size'])) {
            $params['file_size'] = ($filters['filter_file_size'] == '0') ?
                null : explode(',', $filters['filter_file_size']);
        }

        $jdate = Jaws_Date::getInstance();
        $from_date = $to_date = '';
        if (!empty($filters['filter_from_date'])) {
            $from_date = $jdate->ToBaseDate(preg_split('/[- :]/', $filters['filter_from_date']));
            $from_date = $GLOBALS['app']->UserTime2UTC($from_date['timestamp']);
        }
        if (!empty($filters['filter_to_date'])) {
            $to_date = $jdate->ToBaseDate(preg_split('/[- :]/', $filters['filter_to_date'] . ' 23:59:59'));
            $to_date = $GLOBALS['app']->UserTime2UTC($to_date['timestamp']);
        }
        $params['date'] = array($from_date, $to_date);
        if (!empty($filters['filter_order'])) {
            $orderBy = (int)$filters['filter_order'];
        }

        $calType = strtolower($this->gadget->registry->fetch('calendar', 'Settings'));
        $calLang = strtolower($this->gadget->registry->fetch('admin_language', 'Settings'));
        if ($calType != 'gregorian') {
            $GLOBALS['app']->Layout->addScript("libraries/piwi/piwidata/js/jscalendar/$calType.js");
        }
        $GLOBALS['app']->Layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar.js');
        $GLOBALS['app']->Layout->addScript('libraries/piwi/piwidata/js/jscalendar/calendar-setup.js');
        $GLOBALS['app']->Layout->addScript("libraries/piwi/piwidata/js/jscalendar/lang/calendar-$calLang.js");
        $GLOBALS['app']->Layout->addLink('libraries/piwi/piwidata/js/jscalendar/calendar-blue.css');
        $this->AjaxMe('index.js');

        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/filters");

        $tpl->SetVariable('lbl_type', _t('DIRECTORY_FILE_TYPE'));
        $tpl->SetVariable('lbl_size', _t('DIRECTORY_FILE_SIZE'));
        $tpl->SetVariable('lbl_from_date', _t('DIRECTORY_FILE_FROM_DATE'));
        $tpl->SetVariable('lbl_to_date', _t('DIRECTORY_FILE_TO_DATE'));
        $tpl->SetVariable('lbl_search', _t('GLOBAL_SEARCH'));
        $tpl->SetVariable('lbl_order', _t('GLOBAL_ORDERBY'));
        $tpl->SetVariable('lbl_create_time', _t('GLOBAL_CREATETIME'));

        $tpl->SetVariable('lbl_folder', _t('DIRECTORY_FILE_TYPE_FOLDER'));
        $tpl->SetVariable('lbl_text', _t('DIRECTORY_FILE_TYPE_TEXT'));
        $tpl->SetVariable('lbl_image', _t('DIRECTORY_FILE_TYPE_IMAGE'));
        $tpl->SetVariable('lbl_audio', _t('DIRECTORY_FILE_TYPE_AUDIO'));
        $tpl->SetVariable('lbl_video', _t('DIRECTORY_FILE_TYPE_VIDEO'));
        $tpl->SetVariable('lbl_archive', _t('DIRECTORY_FILE_TYPE_ARCHIVE'));
        $tpl->SetVariable('lbl_other', _t('DIRECTORY_FILE_TYPE_OTHER'));

        // file type
        $fileTypes = array();
        $fileTypes[] = array('id' => 0, 'title' => _t('GLOBAL_ALL'));
        $fileTypes[] = array('id' => Directory_Info::FILE_TYPE_FOLDER, 'title' => _t('DIRECTORY_FILE_TYPE_FOLDER'));
        $fileTypes[] = array('id' => Directory_Info::FILE_TYPE_TEXT, 'title' => _t('DIRECTORY_FILE_TYPE_TEXT'));
        $fileTypes[] = array('id' => Directory_Info::FILE_TYPE_IMAGE, 'title' => _t('DIRECTORY_FILE_TYPE_IMAGE'));
        $fileTypes[] = array('id' => Directory_Info::FILE_TYPE_AUDIO, 'title' => _t('DIRECTORY_FILE_TYPE_AUDIO'));
        $fileTypes[] = array('id' => Directory_Info::FILE_TYPE_VIDEO, 'title' => _t('DIRECTORY_FILE_TYPE_VIDEO'));
        $fileTypes[] = array('id' => Directory_Info::FILE_TYPE_ARCHIVE, 'title' => _t('DIRECTORY_FILE_TYPE_ARCHIVE'));
        $fileTypes[] = array('id' => Directory_Info::FILE_TYPE_UNKNOWN, 'title' => _t('DIRECTORY_FILE_TYPE_OTHER'));
        foreach ($fileTypes as $fileType) {
            $tpl->SetBlock("$block/filters/file_type");
            $tpl->SetVariable('value', $fileType['id']);
            $tpl->SetVariable('title', $fileType['title']);

            $tpl->SetVariable('selected', '');
            if (isset($params['file_type']) && $params['file_type'] == $fileType['id']) {
                $tpl->SetVariable('selected', 'selected');
            }
            $tpl->ParseBlock("$block/filters/file_type");
        }

        // file size
        $fileSizes = array();
        $fileSizes[] = array('id' => '0', 'title' => _t('GLOBAL_ALL'));
        $fileSizes[] = array('id' => '0,10', 'title' => '0 - 10 KB');
        $fileSizes[] = array('id' => '10,100', 'title' => '10 - 100 KB');
        $fileSizes[] = array('id' => '100,1024', 'title' => '100 KB - 1 MB');
        $fileSizes[] = array('id' => '1024,16384', 'title' => '1 MB - 16 MB');
        $fileSizes[] = array('id' => '16384,131072', 'title' => '16 MB - 128 MB');
        $fileSizes[] = array('id' => '131072,', 'title' => '>> 128 MB');
        foreach($fileSizes as $fileSize) {
            $tpl->SetBlock("$block/filters/file_size");
            $tpl->SetVariable('value', $fileSize['id']);
            $tpl->SetVariable('title', $fileSize['title']);

            $tpl->SetVariable('selected', '');
            if ($filters['filter_file_size'] == $fileSize['id']) {
                $tpl->SetVariable('selected', 'selected');
            }
            $tpl->ParseBlock("$block/filters/file_size");
        }

        // Start date
        $cal_type = $this->gadget->registry->fetch('calendar', 'Settings');
        $cal_lang = $this->gadget->registry->fetch('site_language', 'Settings');
        $datePicker =& Piwi::CreateWidget('DatePicker', 'filter_from_date', $filters['filter_from_date']);
        $datePicker->setCalType($cal_type);
        $datePicker->setLanguageCode($cal_lang);
        $datePicker->setDateFormat('%Y-%m-%d');
        $datePicker->setStyle('width:80px');
        $tpl->SetVariable('from_date', $datePicker->Get());

        // End date
        $datePicker =& Piwi::CreateWidget('DatePicker', 'filter_to_date', $filters['filter_to_date']);
        $datePicker->setDateFormat('%Y-%m-%d');
        $datePicker->setCalType($cal_type);
        $datePicker->setLanguageCode($cal_lang);
        $datePicker->setStyle('width:80px');
        $tpl->SetVariable('to_date', $datePicker->Get());

        $tpl->SetVariable('order_selected_' . $orderBy, 'selected');
        $tpl->ParseBlock("$block/filters");

        $tpl->SetBlock("$block/files");
        $model = $this->gadget->model->load('Files');
        $files = $model->GetFiles($params, false, $orderBy);
        if (Jaws_Error::IsError($files)) {
            return;
        }
        if (empty($files)) {
            $tpl->SetBlock("$block/files/message");
            $tpl->SetVariable('msg', _t('DIRECTORY_INFO_NO_FILES'));
            $tpl->ParseBlock("$block/files/message");
            return;
        }
        $count = $model->GetFiles($params, true, $orderBy);

        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_created', _t('DIRECTORY_FILE_CREATED'));
        $tpl->SetVariable('lbl_modified', _t('DIRECTORY_FILE_MODIFIED'));
        $tpl->SetVariable('lbl_type', _t('DIRECTORY_FILE_TYPE'));
        $tpl->SetVariable('lbl_size', _t('DIRECTORY_FILE_SIZE'));
        $tpl->SetVariable('lbl_action', _t('GLOBAL_ACTIONS'));

        $tpl->SetVariable('site_url', $GLOBALS['app']->getSiteURL('/'));
        $theme = $GLOBALS['app']->GetTheme();
        $iconUrl = is_dir($theme['url'] . 'mimetypes')? $theme['url'] . 'mimetypes/' : 'images/mimetypes/';
        $icons = array(
            1 => 'folder',
            2 => 'text-generic',
            3 => 'image-generic',
            4 => 'audio-generic',
            5 => 'video-generic',
            6 => 'package-generic',
            99 => 'file-generic',
        );
        $objDate = Jaws_Date::getInstance();
        foreach ($files as $file) {
            if ($file['is_dir']) {
                $url = $this->gadget->urlMap('Directory', array('id' => $file['id']));
            } else {
                $url = $this->gadget->urlMap('File', array('id' => $file['id']));
            }
            $tpl->SetBlock("$block/files/file");
            $tpl->SetVariable('url', $url);
            $tpl->SetVariable('title', $file['title']);
            $tpl->SetVariable('description', $file['description']);
            $tpl->SetVariable('type', empty($file['mime_type'])? '-' : $file['mime_type']);
            $tpl->SetVariable('size', Jaws_Utils::FormatSize($file['file_size']));
            $tpl->SetVariable('created', $objDate->Format($file['create_time'], 'n/j/Y g:i a'));
            $tpl->SetVariable('modified', $objDate->Format($file['update_time'], 'n/j/Y g:i a'));
            if ($preview) {
                $tpl->SetBlock("$block/files/file/preview");
                $tpl->SetVariable('preview', $this->gadget->action->load('File')->play($file));
                $tpl->ParseBlock("$block/files/file/preview");
            }

            if (!$file['public']) {
                $tpl->SetBlock("$block/files/file/action");
                $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
                $tpl->SetVariable('id', $file['id']);
                $tpl->SetVariable('lbl_edit', _t('GLOBAL_EDIT'));
                $tpl->SetVariable('id',  $file['id']);
                $tpl->SetVariable('parent',  $parent);
                $tpl->ParseBlock("$block/files/file/action");
            }

            $thumbnailURL = $model->GetThumbnailURL($file['host_filename']);
            if (!empty($thumbnailURL)) {
                $tpl->SetVariable('icon', $thumbnailURL);
            } else {
                $tpl->SetVariable('icon', $iconUrl . $icons[$file['file_type']] . '.png');
            }

            $tpl->ParseBlock("$block/files/file");
        }

        // Pagination
        if (!$isLayoutAction && $params['limit'] > 0) {
            $args = array();
            if ($parent > 0) {
                $args['id'] = $parent;
            }
            if (!empty($params['user'])) {
                $args['user'] = (int)$params['user'];
            }
            if (!empty($params['public'])) {
                $args['public'] = (int)$params['public'];
            }
            if (!empty($params['file_type'])) {
                $args['type'] = $params['file_type'];
            }
            if (!empty($orderBy)) {
                $args['order'] = $orderBy;
            }

            $this->gadget->action->load('PageNavigation')->pagination(
                $tpl,
                $page,
                $params['limit'],
                $count,
                'Directory',
                $args
            );
        }

        $tpl->ParseBlock("$block/files");
        return;
    }

    /**
     * Fetches path of a file/directory
     *
     * @access  public
     * @return  array   Directory hierarchy
     */
    function GetPath($id)
    {
        $path = '';
        $pathArr = array();
        $model = $this->gadget->model->load('Files');
        $model->GetPath($id, $pathArr);
        foreach(array_reverse($pathArr) as $i => $p) {
            $url = $this->gadget->urlMap('Directory', array('id' => $p['id']));
            $path .= ($i == count($pathArr) - 1)?
                ' > ' . $p['title'] :
                " > <a href='$url'>" . $p['title'] . '</a>';
        }
        return $path;
    }

    /**
     * Downloads(streams) file
     *
     * @access  public
     * @return  mixed   File data or Jaws_Error
     */
    function Download()
    {
        $get = $this->gadget->request->fetch(array('id', 'user', 'key'), 'get');
        $id = (int)$get['id'];
        if (empty($id)) {
            return Jaws_HTTPError::Get(404);
        }
        $model = $this->gadget->model->load('Files');

        // Validate file
        $file = $model->GetFile($id);
        if (Jaws_Error::IsError($file) || empty($file) || empty($file['user_filename'])) {
            return Jaws_HTTPError::Get(404);
        }

        // check private file
        if (!$file['public']) {
            $loggedUser = (int)$GLOBALS['app']->Session->GetAttribute('user');
            if ($file['user'] != $loggedUser && $get['key'] != $file['key']) {
                return Jaws_HTTPError::Get(403);
            }
        }

        // Check for file existence
        $filename = JAWS_DATA . 'directory/' . $file['host_filename'];
        if (!file_exists($filename)) {
            return Jaws_HTTPError::Get(404);
        }

        // Stream file
        if (!Jaws_Utils::Download($filename, $file['user_filename'], $file['mime_type'])) {
            return Jaws_HTTPError::Get(404);
        }

        return true;
    }

}