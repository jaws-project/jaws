<?php
/**
 * Directory Gadget
 *
 * @category    Gadget
 * @package     Directory
 */
class Directory_Actions_File extends Jaws_Gadget_Action
{
    /**
     * Displays file properties
     *
     * @access  public
     * @return  string  HTML content
     */
    function file()
    {
        $get = $this->gadget->request->fetch(array('id', 'user', 'key'),  'get');
        $file = $this->gadget->model->load('Files')->GetFile((int)$get['id']);
        if (Jaws_Error::IsError($file) || empty($file) || $file['is_dir']) {
            return Jaws_HTTPError::Get(404);
        }

        // check private file
        if (!$file['public']) {
            $loggedUser = (int)$this->app->session->user->id;
            if ($file['user'] != $loggedUser && $get['key'] != $file['key']) {
                return Jaws_HTTPError::Get(403);
            }
        }

        $tpl = $this->gadget->template->load('File.html');
        $tpl->SetBlock('file');

        $tpl->SetVariable('lbl_title', $this::t('FILE_TITLE'));
        $tpl->SetVariable('lbl_desc', $this::t('FILE_DESC'));
        $tpl->SetVariable('lbl_filename', $this::t('FILE_FILENAME'));
        $tpl->SetVariable('lbl_type', $this::t('FILE_TYPE'));
        $tpl->SetVariable('lbl_size', $this::t('FILE_SIZE'));
        $tpl->SetVariable('lbl_bytes', $this::t('BYTES'));
        $tpl->SetVariable('lbl_created', $this::t('FILE_CREATED'));
        $tpl->SetVariable('lbl_modified', $this::t('FILE_MODIFIED'));
        $tpl->SetVariable('lbl_download', $this::t('DOWNLOAD'));

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
        $tpl->SetVariable(
            'preview',
            $this->gadget->action->loadAdmin('Files')->PlayMedia($file)
        );

        // display thumbnail
        $model = $this->gadget->model->load('Files');
        $tpl->SetVariable('thumbnail', $model->GetThumbnailURL($file['host_filename']));

        // display comments/comment-form
        if (Jaws_Gadget::IsGadgetInstalled('Comments')) {
            $allow_comments = $this->gadget->registry->fetch('allow_comments', 'Comments');

            $cHTML = Jaws_Gadget::getInstance('Comments')->action->load('Comments');
            $tpl->SetVariable('comments', $cHTML->ShowComments('Directory', 'File', $file['id'],
                array('action' => 'Directory', 'params' => array('id' => $file['id']))));

            if ($allow_comments == 'true') {
                $tpl->SetVariable('comment-form', $cHTML->ShowCommentsForm(
                    'Directory',
                    'File',
                    $file['id']
                ));
            } elseif ($allow_comments == 'restricted') {
                $login_url = $this->app->map->GetMappedURL('Users', 'Login');
                $register_url = $this->app->map->GetMappedURL('Users', 'Registration');
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
     * Displays file
     *
     * @access  public
     * @return  array   Response array
     */
    function PlayMedia($file = null)
    {
        $result = $this->gadget->action->loadAdmin('Files')->PlayMedia($file);
        if ($result) {
            return $this->gadget->session->response(
                '',
                RESPONSE_NOTICE,
                $result
            );
        }

        return $this->gadget->session->response(
            Jaws::t('ERROR_FILE_DOES_NOT_EXIST'),
            RESPONSE_ERROR
        );
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
     * Get a file info
     *
     * @access  public
     * @return  array   Directory hierarchy
     */
    function GetFile()
    {
        $id = (int)$this->gadget->request->fetch('id');
        return $this->gadget->model->load('Files')->GetFile($id);
    }

    /**
     * Creates a new file
     *
     * @access  public
     * @return  array   Response array
     */
    function SaveFile()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $data = $this->gadget->request->fetch(
            array('id', 'parent', 'title', 'description', 'public', 'published', 'tags')
        );

        if (!empty($data['title'])) {
            $result = $this->gadget->model->loadAdmin('Files')->SaveFile($data);
        } else {
            $result = Jaws_Error::raiseError($this::t('ERROR_INCOMPLETE_DATA'), __FUNCTION__, JAWS_ERROR_NOTICE);
        }
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push(
                $result->getMessage(),
                RESPONSE_ERROR,
                'SaveFile'
            );
        } else {
            $this->gadget->session->push(
                $result,
                RESPONSE_NOTICE,
                'SaveFile'
            );
        }

        $referrer = $this->gadget->request->fetch('referrer');
        $referrer = parse_url(hex2bin($referrer));
        $redirectURL =
            (array_key_exists('path', $referrer)? $referrer['path'] : '') . 
            (array_key_exists('query', $referrer)? "?{$referrer['query']}" : '') . 
            (array_key_exists('fragment', $referrer)? "#{$referrer['fragment']}" : '');
        return Jaws_Header::Location($redirectURL);
    }

    /**
     * Delete a file
     *
     * @access  public
     * @return  mixed   Number of forms or Jaws_Error
     */
    function DeleteFile()
    {
        if (!$this->app->session->user->logged) {
            return Jaws_HTTPError::Get(403);
        }

        $id = (int)$this->gadget->request->fetch('fileId', 'post');

        $model = $this->gadget->model->load('Files');
        $fileInfo = $this->gadget->model->load('Files')->GetFile($id);
        if (Jaws_Error::IsError($fileInfo)) {
            return Jaws_HTTPError::Get(500);
        }
        if (empty($fileInfo)) {
            return Jaws_HTTPError::Get(404);
        }
        $currentUser = $this->app->session->user->id;
        if ($fileInfo['public'] || $fileInfo['user'] != $currentUser) {
            return Jaws_HTTPError::Get(403);
        }

        $res = $this->gadget->model->loadAdmin('Files')->DeleteFile($fileInfo);
        if (Jaws_Error::isError($res)) {
            return $this->gadget->session->response($res->getMessage(), RESPONSE_ERROR);
        } else {
            return $this->gadget->session->response($this::t('NOTICE_ITEMS_DELETED'), RESPONSE_NOTICE);
        }
    }

}