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
class Directory_Actions_Files extends Jaws_Gadget_Action
{
    /**
     * Builds the file management form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function FileForm()
    {
        $mode = jaws()->request->fetch('mode');
        if ($mode === null) $mode = 'view';
        $tpl = $this->gadget->loadTemplate('File.html');
        $tpl->SetBlock($mode);
        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_desc', _t('DIRECTORY_FILE_DESC'));
        $tpl->SetVariable('lbl_url', _t('DIRECTORY_FILE_URL'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        if ($mode === 'edit') {
            $tpl->SetVariable('lbl_file', _t('DIRECTORY_FILE'));
            $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));
        } else {
            $tpl->SetVariable('lbl_filename', _t('DIRECTORY_FILE_FILENAME'));
            $tpl->SetVariable('lbl_type', _t('DIRECTORY_FILE_TYPE'));
            $tpl->SetVariable('lbl_size', _t('DIRECTORY_FILE_SIZE'));
            $tpl->SetVariable('lbl_owner', _t('DIRECTORY_FILE_OWNER'));
            $tpl->SetVariable('lbl_bytes', _t('DIRECTORY_BYTES'));
            $tpl->SetVariable('lbl_shared', _t('DIRECTORY_SHARED_FOR'));
            $tpl->SetVariable('lbl_created', _t('DIRECTORY_FILE_CREATED'));
            $tpl->SetVariable('lbl_modified', _t('DIRECTORY_FILE_MODIFIED'));
            $tpl->SetVariable('lbl_public', _t('DIRECTORY_FILE_PUBLIC_URL'));
            $tpl->SetVariable('title', '{title}');
            $tpl->SetVariable('desc', '{description}');
            $tpl->SetVariable('filename', '{filename}');
            $tpl->SetVariable('type', '{type}');
            $tpl->SetVariable('filetype', '{filetype}');
            $tpl->SetVariable('size', '{size}');
            $tpl->SetVariable('filesize', '{filesize}');
            $tpl->SetVariable('username', '{username}');
            $tpl->SetVariable('url', '{url}');
            $tpl->SetVariable('users', '{users}');
            $tpl->SetVariable('createtime', '{createtime}');
            $tpl->SetVariable('updatetime', '{updatetime}');
            $tpl->SetVariable('created', '{created}');
            $tpl->SetVariable('modified', '{modified}');
        }
        $tpl->ParseBlock($mode);
        return $tpl->Get();
    }

    /**
     * Creates a new file
     *
     * @access  public
     * @return  array   Response array
     */
    function CreateFile()
    {
        try {
            $data = jaws()->request->fetch(
                array('title', 'description', 'parent', 'url', 'filename', 'filetype', 'filesize')
            );
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }

            $model = $this->gadget->model->load('Files');
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');

            // Validate parent
            if ($data['parent'] != 0) {
                $parent = $model->GetFile($data['parent']);
                if (Jaws_Error::IsError($parent)) {
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                }
                if ($parent['user'] != $user) {
                    throw new Exception(_t('DIRECTORY_ERROR_NO_PERMISSION'));
                }
            }

            $data['user'] = $data['owner'] = $user;
            $data['is_dir'] = false;
            $data['title'] = Jaws_XSS::defilter($data['title']);
            $data['description'] = Jaws_XSS::defilter($data['description']);

            // Upload file
            $path = $GLOBALS['app']->getDataURL('directory/' . $data['user']);
            if (!is_dir($path)) {
                if (!Jaws_Utils::mkdir($path, 2)) {
                    throw new Exception('DIRECTORY_ERROR_FILE_UPLOAD');
                }
            }
            $res = Jaws_Utils::UploadFiles($_FILES, $path);
            if (Jaws_Error::IsError($res)) {
                throw new Exception($res->getMessage());
            } else if ($res !== false) {
                $data['filename'] = $res['file'][0]['host_filename'];
                $data['filetype'] = $res['file'][0]['host_filetype'];
                $data['filesize'] = $res['file'][0]['host_filesize'];
            } else {
                if (empty($data['filename'])) {
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                } else {
                    $filename = Jaws_Utils::upload_tmp_dir(). '/' . $data['filename'];
                    if (file_exists($filename)) {
                        $target = $path . '/' . $data['filename'];
                        $res = Jaws_Utils::rename($filename, $target, false);
                        if ($res === false) {
                            throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                        }
                        $data['filename'] = basename($res);
                    } else {
                        throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                    }
                }
            }

            // Insert record
            $res = $model->Insert($data);
            if (Jaws_Error::IsError($res)) {
                throw new Exception(_t('DIRECTORY_ERROR_FILE_CREATE'));
            }
        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_FILE_CREATED'), RESPONSE_NOTICE);
    }

    /**
     * Updates file
     *
     * @access  public
     * @return  array   Response array
     */
    function UpdateFile()
    {
        try {
            // Validate data
            $data = jaws()->request->fetch(
                array('id', 'title', 'description', 'parent',
                    'url', 'filename', 'filetype', 'filesize')
            );
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }
            $data['title'] = Jaws_XSS::defilter($data['title']);
            $data['description'] = Jaws_XSS::defilter($data['description']);

            $model = $this->gadget->model->load('Files');

            // Validate file
            $id = (int)$data['id'];
            $file = $model->GetFile($id);
            if (Jaws_Error::IsError($file)) {
                throw new Exception($file->getMessage());
            }

            // Validate user
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
            if ($file['user'] != $user) {
                throw new Exception(_t('DIRECTORY_ERROR_FILE_UPDATE'));
            }

            // Upload file
            if ($file['user'] != $file['owner']) { // is shortcut
                unset($data['parent'], $data['url'], $data['filename']);
                unset($data['filetype'], $data['filesize']);
            } else {
                $path = $GLOBALS['app']->getDataURL('directory/' . $user);
                if (!is_dir($path)) {
                    if (!Jaws_Utils::mkdir($path, 2)) {
                        throw new Exception('DIRECTORY_ERROR_FILE_UPLOAD');
                    }
                }
                $res = Jaws_Utils::UploadFiles($_FILES, $path);
                if (Jaws_Error::IsError($res)) {
                    throw new Exception($res->getMessage());
                } else if ($res !== false) {
                    $data['filename'] = $res['file'][0]['host_filename'];
                    $data['filetype'] = $res['file'][0]['host_filetype'];
                    $data['filesize'] = $res['file'][0]['host_filesize'];
                } else {
                    if ($data['filename'] === ':nochange:') {
                        unset($data['filename']);
                    } else if (empty($data['filename'])) {
                        throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                    } else {
                        $filename = Jaws_Utils::upload_tmp_dir(). '/'. $data['filename'];
                        if (file_exists($filename)) {
                            $target = $path . '/' . $data['filename'];
                            $res = Jaws_Utils::rename($filename, $target, false);
                            if ($res === false) {
                                throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                            }
                            $data['filename'] = basename($res);
                        } else {
                            throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                        }
                    }
                }
            }

            // Update file in database
            $data['updatetime'] = time();
            $model = $this->gadget->model->load('Files');
            $res = $model->Update($id, $data);
            if (Jaws_Error::IsError($res)) {
                throw new Exception(_t('DIRECTORY_ERROR_FILE_UPDATE'));
            }

            // Update shortcuts
            if ($file['shared']) {
                $shortcut = array();
                $shortcut['url'] = $data['url'];
                $shortcut['filename'] = $data['filename'];
                $shortcut['filetype'] = $data['filetype'];
                $shortcut['filesize'] = $data['filesize'];
                $shortcut['updatetime'] = $data['updatetime'];
                $model->UpdateShortcuts($id, $shortcut);
            }
        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_FILE_UPDATED'), RESPONSE_NOTICE);
    }

    /**
     * Makes file public/unpublic
     *
     * @access  public
     * @return  array   Response array
     */
    function PublishFile()
    {
        try {
            $id = (int)jaws()->request->fetch('id');
            $model = $this->gadget->model->load('Files');

            // Validate file
            $file = $model->GetFile($id);
            if (Jaws_Error::IsError($file) || $file['is_dir']) {
                throw new Exception(_t('DIRECTORY_ERROR_FILE_UPDATE'));
            }

            // Validate user
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
            if ($file['user'] != $user) {
                throw new Exception(_t('DIRECTORY_ERROR_FILE_UPDATE'));
            }

            $public = jaws()->request->fetch('public');
            if ($public === null) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }
            $public = (bool)$public;

            // Update record
            $model = $this->gadget->model->load('Files');
            $res = $model->Update($id, array('public' => $public));
            if (Jaws_Error::IsError($res)) {
                throw new Exception(_t('DIRECTORY_ERROR_FILE_UPDATE'));
            }
            $dl_url = $public? $this->GetDownloadURL($id, false) : '';
        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('DIRECTORY_NOTICE_FILE_UPDATED'),
            RESPONSE_NOTICE,
            $dl_url
        );
    }

    /**
     * Generates file download URL
     *
     * @access  public
     * @return  string  Related URL
     */
    function GetDownloadURL($id = null, $open = null)
    {
        $id = ($id !== null)? $id : (int)jaws()->request->fetch('id');
        $open = ($open !== null)? $open : (bool)jaws()->request->fetch('open');
        $action = $open? 'OpenFile' : 'DownloadFile';
        return $GLOBALS['app']->Map->GetURLFor(
            'Directory',
            $action,
            array('id' => $id));
    }

    /**
     * Builds HTML5 audio/video tags for the file
     *
     * @access  public
     * @return  array   Response array
     */
    function PlayMedia()
    {
        $id = (int)jaws()->request->fetch('id');
        $type = jaws()->request->fetch('type');
        $url = $this->GetDownloadURL($id, true);
        $tpl = "[$type]" . $url . "[/$type]";
        return $this->gadget->ParseText($tpl, 'Directory', 'index');
    }

    /**
     * Uploads file to system temp directory
     *
     * @access  public
     * @return  string  JavaScript snippet
     */
    function UploadFile()
    {
        $res = Jaws_Utils::UploadFiles($_FILES, Jaws_Utils::upload_tmp_dir());
        if (Jaws_Error::IsError($res)) {
            $response = array('type' => 'error',
                              'message' => $res->getMessage());
        } else {
            $response = array('type' => 'notice',
                              'filename' => $res['file'][0]['host_filename'],
                              'filetype' => $res['file'][0]['host_filetype'],
                              'filesize' => $res['file'][0]['host_filesize']);
        }

        $response = $GLOBALS['app']->UTF8->json_encode($response);
        return "<script>parent.onUpload($response);</script>";
    }

    /**
     * Downloads file (not force)
     *
     * @access  public
     * @return  mixed   File data or Jaws_Error
     */
    function OpenFile()
    {
        return $this->Download(true);
    }

    /**
     * Downloads file (force download)
     *
     * @access  public
     * @return  mixed   File data or Jaws_Error
     */
    function DownloadFile()
    {
        return $this->Download(false);
    }

    /**
     * Downloads file (stream)
     *
     * @access  public
     * @return  mixed   File data or Jaws_Error
     */
    function Download($open = true)
    {
        $id = jaws()->request->fetch('id');
        if (is_null($id)) {
            return Jaws_HTTPError::Get(500);
        }
        $id = (int)$id;
        $model = $this->gadget->model->load('Files');

        // Validate file
        $file = $model->GetFile($id);
        if (Jaws_Error::IsError($file)) {
            return Jaws_HTTPError::Get(500);
        }
        if (empty($file) || empty($file['filename'])) {
            return Jaws_HTTPError::Get(404);
        }

        // Validate user
        if (!$file['public']) {
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $access = $model->CheckAccess($id, $user);
            if ($access !== true) {
                return Jaws_HTTPError::Get(403);
            }
        }

        // Check for fie existance
        $uid = ($file['id'] == $file['reference'])? $file['user'] : $file['owner'];
        $filename = $GLOBALS['app']->getDataURL("directory/$uid/") . $file['filename'];
        if (!file_exists($filename)) {
            return Jaws_HTTPError::Get(404);
        }

        // Stream file
        if (!Jaws_Utils::Download($filename, $file['filename'], $file['filetype'], $open)) {
            return Jaws_HTTPError::Get(500);
        }

        return;
    }
}