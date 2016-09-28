<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
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
                -1 => _t('DIRECTORY_FILE_TYPE_ALL'),
                Directory_Info::FILE_TYPE_TEXT    => _t('DIRECTORY_FILE_TYPE_TEXT'),
                Directory_Info::FILE_TYPE_IMAGE   => _t('DIRECTORY_FILE_TYPE_IMAGE'),
                Directory_Info::FILE_TYPE_AUDIO   => _t('DIRECTORY_FILE_TYPE_AUDIO'),
                Directory_Info::FILE_TYPE_VIDEO   => _t('DIRECTORY_FILE_TYPE_VIDEO'),
                Directory_Info::FILE_TYPE_ARCHIVE => _t('DIRECTORY_FILE_TYPE_ARCHIVE'),
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
        $tpl = $this->gadget->template->load('Directory.html');
        $tpl->SetBlock('directory');

        $id = ($type == null)? (int)jaws()->request->fetch('id') : 0;
        if ($id == 0) {
            $tpl->SetVariable('content', $this->ListFiles(0, $type, $orderBy, $limit));
        } else {
            $model = $this->gadget->model->loadAdmin('Files');
            $file = $model->GetFile($id);
            if (Jaws_Error::IsError($file) || empty($file)) {
                require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
                return Jaws_HTTPError::Get(404);
            }
            if ($file['is_dir']) {
                $tpl->SetVariable('content', $this->ListFiles($id, $type));
            } else {
                $tpl->SetVariable('content', $this->ViewFile($file));
            }
            $tpl->SetVariable('root', _t('DIRECTORY_HOME'));
            $tpl->SetVariable('root_url', $this->gadget->urlMap('Directory'));
            $tpl->SetVariable('path', $this->GetPath($id));
        }

        $tpl->ParseBlock('directory');
        return $tpl->Get();
    }

    /**
     * Fetches and displays list of dirs/files
     *
     * @access  public
     * @param   int     $parent
     * @param   null    $type
     * @param   int     $orderBy    Order by
     * @param   int     $limit      Forms limit
     * @return string HTML content
     */
    function ListFiles($parent = 0, $type = null, $orderBy = 0, $limit = 0)
    {
        $isLayoutAction = false;
        if ($type == null) { // normal action
            $params = jaws()->request->fetch(array('type', 'page'), 'get');
            $page = (int)$params['page'];
            if ($params['type'] !== '') {
                $params['file_type'] = $params['type'];
            }
            unset($params['type']);
        } else {
            $isLayoutAction = true;
            $page = 0;
            $params = array();
            $params['file_type'] = ($type == -1)? null : $type;
        }
        $params['limit'] = ($limit > 0) ? $limit : (int)$this->gadget->registry->fetch('items_per_page');
        $params['offset'] = ($page == 0)? 0 : $params['limit'] * ($page - 1);
        $params['parent'] = $parent;
        $params['hidden'] = false;
        $params['published'] = true;

        $user = jaws()->request->fetch('user', 'get');
        if (!empty($user)) {
            $params['user'] = $user;
        }

        $model = $this->gadget->model->load('Files');
        $files = $model->GetFiles($params, false, $orderBy);
        if (Jaws_Error::IsError($files)) {
            return '';
        }
        if (empty($files)) {
            return _t('DIRECTORY_INFO_NO_FILES');
        }
        $count = $model->GetFiles($params, true, $orderBy);

        $tpl = $this->gadget->template->load('Directory.html');
        $tpl->SetBlock('files');

        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_created', _t('DIRECTORY_FILE_CREATED'));
        $tpl->SetVariable('lbl_modified', _t('DIRECTORY_FILE_MODIFIED'));
        $tpl->SetVariable('lbl_type', _t('DIRECTORY_FILE_TYPE'));
        $tpl->SetVariable('lbl_size', _t('DIRECTORY_FILE_SIZE'));
        $tpl->SetVariable('lbl_start_date', _t('DIRECTORY_FILE_START_DATE'));
        $tpl->SetVariable('lbl_end_date', _t('DIRECTORY_FILE_END_DATE'));
        $tpl->SetVariable('lbl_search', _t('GLOBAL_SEARCH'));

        $tpl->SetVariable('lbl_folder', _t('DIRECTORY_FILE_TYPE_FOLDER'));
        $tpl->SetVariable('lbl_text', _t('DIRECTORY_FILE_TYPE_TEXT'));
        $tpl->SetVariable('lbl_image', _t('DIRECTORY_FILE_TYPE_IMAGE'));
        $tpl->SetVariable('lbl_audio', _t('DIRECTORY_FILE_TYPE_AUDIO'));
        $tpl->SetVariable('lbl_video', _t('DIRECTORY_FILE_TYPE_VIDEO'));
        $tpl->SetVariable('lbl_archive', _t('DIRECTORY_FILE_TYPE_ARCHIVE'));
        $tpl->SetVariable('lbl_other', _t('DIRECTORY_FILE_TYPE_OTHER'));

        $tpl->SetVariable('type_folder', -1);
        $tpl->SetVariable('type_text', Directory_Info::FILE_TYPE_TEXT);
        $tpl->SetVariable('type_image', Directory_Info::FILE_TYPE_IMAGE);
        $tpl->SetVariable('type_audio', Directory_Info::FILE_TYPE_AUDIO);
        $tpl->SetVariable('type_video', Directory_Info::FILE_TYPE_VIDEO);
        $tpl->SetVariable('type_archive', Directory_Info::FILE_TYPE_ARCHIVE);
        $tpl->SetVariable('type_other', Directory_Info::FILE_TYPE_UNKNOWN);

        // Start date
        $cal_type = $this->gadget->registry->fetch('calendar', 'Settings');
        $cal_lang = $this->gadget->registry->fetch('site_language', 'Settings');
        $datePicker =& Piwi::CreateWidget('DatePicker', 'start_date');
        $datePicker->showTimePicker(true);
        $datePicker->setCalType($cal_type);
        $datePicker->setLanguageCode($cal_lang);
        $datePicker->setDateFormat('%Y-%m-%d');
        $datePicker->setStyle('width:80px');
        $tpl->SetVariable('start_date', $datePicker->Get());

        // End date
        $datePicker =& Piwi::CreateWidget('DatePicker', 'end_date');
        $datePicker->showTimePicker(true);
        $datePicker->setDateFormat('%Y-%m-%d');
        $datePicker->SetIncludeCSS(false);
        $datePicker->SetIncludeJS(false);
        $datePicker->setCalType($cal_type);
        $datePicker->setLanguageCode($cal_lang);
        $datePicker->setStyle('width:80px');
        $tpl->SetVariable('end_date', $datePicker->Get());

        $tpl->SetVariable('site_url', $GLOBALS['app']->getSiteURL('/'));
        $theme = $GLOBALS['app']->GetTheme();
        $iconUrl = is_dir($theme['url'] . 'mimetypes')? $theme['url'] . 'mimetypes/' : 'images/mimetypes/';
        $icons = array(
            null => 'folder',
            0 => 'file-generic',
            1 => 'text-generic',
            2 => 'image-generic',
            3 => 'audio-generic',
            4 => 'video-generic',
            5 => 'package-generic'
        );
        $objDate = Jaws_Date::getInstance();
        foreach ($files as $file) {
            $url = $this->gadget->urlMap('Directory', array('id' => $file['id']));
            $tpl->SetBlock('files/file');
            $tpl->SetVariable('url', $url);
            $tpl->SetVariable('title', $file['title']);
            $tpl->SetVariable('type', empty($file['mime_type'])? '-' : $file['mime_type']);
            $tpl->SetVariable('size', Jaws_Utils::FormatSize($file['file_size']));
            $tpl->SetVariable('created', $objDate->Format($file['create_time'], 'n/j/Y g:i a'));
            $tpl->SetVariable('modified', $objDate->Format($file['update_time'], 'n/j/Y g:i a'));
            $tpl->SetVariable('icon', $iconUrl . $icons[$file['file_type']] . '.png');
            $tpl->ParseBlock('files/file');
        }

        // Pagination
        if (!$isLayoutAction && $tpl->VariableExists('pagination') && $params['limit'] > 0) {
            $action = $this->gadget->action->load('Pagination');
            $args = array();
            if ($parent > 0) {
                $args['id'] = $parent;
            }
            $tpl->setVariable('pagination', $action->Pagination($page, $params['limit'], $count, 'Directory', $args));
        }

        $tpl->ParseBlock('files');
        return $tpl->Get();
    }

    /**
     * Displays file properties
     *
     * @access  public
     * @return  string  HTML content
     */
    function ViewFile($file)
    {
        $tpl = $this->gadget->template->load('Directory.html');
        $tpl->SetBlock('file');

        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_desc', _t('DIRECTORY_FILE_DESC'));
        $tpl->SetVariable('lbl_filename', _t('DIRECTORY_FILE_FILENAME'));
        $tpl->SetVariable('lbl_type', _t('DIRECTORY_FILE_TYPE'));
        $tpl->SetVariable('lbl_size', _t('DIRECTORY_FILE_SIZE'));
        $tpl->SetVariable('lbl_bytes', _t('DIRECTORY_BYTES'));
        $tpl->SetVariable('lbl_created', _t('DIRECTORY_FILE_CREATED'));
        $tpl->SetVariable('lbl_modified', _t('DIRECTORY_FILE_MODIFIED'));
        $tpl->SetVariable('lbl_download', _t('DIRECTORY_DOWNLOAD'));

        $objDate = Jaws_Date::getInstance();
        $file['created'] = $objDate->Format($file['create_time'], 'n/j/Y g:i a');
        $file['modified'] = $objDate->Format($file['update_time'], 'n/j/Y g:i a');
        $file['type'] = empty($file['mime_type'])? '-' : $file['mime_type'];
        $file['size'] = Jaws_Utils::FormatSize($file['file_size']);
        $file['download'] = $this->gadget->urlMap('Download', array('id' => $file['id']));
        foreach ($file as $key => $value) {
            $tpl->SetVariable($key, $value);
        }

        // display tags
        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $tagsHTML = Jaws_Gadget::getInstance('Tags')->action->load('Tags');
            $tagsHTML->loadReferenceTags('Directory', 'file', $file['id'], $tpl, 'file');
        }

        // display file
        $fileInfo = pathinfo($file['host_filename']);
        if (isset($fileInfo['extension'])) {
            $ext = $fileInfo['extension'];
            $type = '';
            if ($ext === 'txt') {
                $type = 'text';
            } else if (in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'svg'))) {
                $type = 'image';
            } else if (in_array($ext, array('wav', 'mp3'))) {
                $type = 'audio';
            } else if (in_array($ext, array('webm', 'mp4', 'ogg'))) {
                $type = 'video';
            }
            if ($type != '') {
                $tpl->SetVariable('preview', $this->PlayMedia($file, $type));
            }
        }

        // display comments/comment-form
        if (Jaws_Gadget::IsGadgetInstalled('Comments')) {
            $allow_comments = $this->gadget->registry->fetch('allow_comments', 'Comments');

            $cHTML = Jaws_Gadget::getInstance('Comments')->action->load('Comments');
            $tpl->SetVariable('comments', $cHTML->ShowComments('Directory', 'File', $file['id'],
                array('action' => 'Directory', 'params' => array('id' => $file['id']))));

            if ($allow_comments == 'true') {
                $redirect_to = $this->gadget->urlMap('Directory', array('id' => $file['id']));
                $tpl->SetVariable('comment-form', $cHTML->ShowCommentsForm('Directory', 'File', $file['id'], $redirect_to));
            } elseif ($allow_comments == 'restricted') {
                $login_url = $GLOBALS['app']->Map->GetURLFor('Users', 'LoginBox');
                $register_url = $GLOBALS['app']->Map->GetURLFor('Users', 'Registration');
                $tpl->SetVariable('comment-form', _t('COMMENTS_COMMENTS_RESTRICTED', $login_url, $register_url));
            }
        }

        // Show like rating
        if (Jaws_Gadget::IsGadgetInstalled('Rating')) {
            $ratingHTML = Jaws_Gadget::getInstance('Rating')->action->load('RatingTypes');
            $ratingHTML->loadReferenceLike('Directory', 'File', $file['id'], 0, $tpl, 'file');
        }

        // display subscription if installed
//        if (Jaws_Gadget::IsGadgetInstalled('Subscription')) {
//            $sHTML = Jaws_Gadget::getInstance('Subscription')->action->load('Subscription');
//            $tpl->SetVariable('subscription', $sHTML->ShowSubscription('Directory', 'Folder', $e['id']));
//        }

        $tpl->ParseBlock('file');
        return $tpl->Get();
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
        $model = $this->gadget->model->loadAdmin('Files');
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
     * Displays file
     *
     * @access  public
     * @return  array   Response array
     */
    function PlayMedia($file, $type)
    {
        $tpl = $this->gadget->template->loadAdmin('Media.html');
        $tpl->SetBlock($type);
        if ($type === 'text') {
            $filename = JAWS_DATA . 'directory/' . $file['host_filename'];
            if (file_exists($filename)) {
                $tpl->SetVariable('text', file_get_contents($filename));
            }
        } else {
            $tpl->SetVariable('url', $this->gadget->urlMap('Download', array('id' => $file['id'])));
        }
        $tpl->ParseBlock($type);

        return $this->gadget->ParseText($tpl->get(), 'Directory', 'index');
    }

    /**
     * Downloads(streams) file
     *
     * @access  public
     * @return  mixed   File data or Jaws_Error
     */
    function Download()
    {
        $id = jaws()->request->fetch('id');
        if (is_null($id)) {
            return Jaws_HTTPError::Get(500);
        }
        $id = (int)$id;
        $model = $this->gadget->model->loadAdmin('Files');

        // Validate file
        $file = $model->GetFile($id);
        if (Jaws_Error::IsError($file)) {
            return Jaws_HTTPError::Get(500);
        }
        if (empty($file) || empty($file['user_filename'])) {
            return Jaws_HTTPError::Get(404);
        }

        // Check for file existence
        $filename = JAWS_DATA . 'directory/' . $file['host_filename'];
        if (!file_exists($filename)) {
            return Jaws_HTTPError::Get(404);
        }

        // Stream file
        if (!Jaws_Utils::Download($filename, $file['user_filename'], $file['mime_type'])) {
            return Jaws_HTTPError::Get(500);
        }

        return true;
    }
}