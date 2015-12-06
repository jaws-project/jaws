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
class Directory_Actions_Admin_Files extends Jaws_Gadget_Action
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
        $tpl = $this->gadget->template->loadAdmin('File.html');
        $tpl->SetBlock($mode);
        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_desc', _t('DIRECTORY_FILE_DESC'));
        $tpl->SetVariable('lbl_hidden', _t('DIRECTORY_FILE_HIDDEN'));
        $tpl->SetVariable('lbl_url', _t('DIRECTORY_FILE_URL'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        if ($mode === 'edit') {
            $tpl->SetVariable('lbl_file', _t('DIRECTORY_FILE'));
            $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));
        } else {
            $tpl->SetVariable('lbl_filename', _t('DIRECTORY_FILE_FILENAME'));
            $tpl->SetVariable('lbl_type', _t('DIRECTORY_FILE_TYPE'));
            $tpl->SetVariable('lbl_size', _t('DIRECTORY_FILE_SIZE'));
            $tpl->SetVariable('lbl_bytes', _t('DIRECTORY_BYTES'));
            $tpl->SetVariable('lbl_created', _t('DIRECTORY_FILE_CREATED'));
            $tpl->SetVariable('lbl_modified', _t('DIRECTORY_FILE_MODIFIED'));
            $tpl->SetVariable('title', '{title}');
            $tpl->SetVariable('desc', '{description}');
            $tpl->SetVariable('user_filename', '{user_filename}');
            $tpl->SetVariable('hidden', '{hidden}');
            $tpl->SetVariable('type', '{type}');
            $tpl->SetVariable('filetype', '{filetype}');
            $tpl->SetVariable('size', '{size}');
            $tpl->SetVariable('filesize', '{filesize}');
            $tpl->SetVariable('url', '{url}');
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
                array('title', 'description', 'parent', 'hidden',
                    'user_filename', 'host_filename', 'filetype', 'filesize')
            );
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }

            $model = $this->gadget->model->loadAdmin('Files');

            // Validate parent
            if ($data['parent'] != 0) {
                $parent = $model->GetFile($data['parent']);
                if (Jaws_Error::IsError($parent)) {
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                }
            }

            $data['is_dir'] = false;
            $data['hidden'] = $data['hidden']? true : false;
            $data['title'] = Jaws_XSS::defilter($data['title']);
            $data['description'] = Jaws_XSS::defilter($data['description']);

            // Upload file
            $path = $GLOBALS['app']->getDataURL('directory');
            if (!is_dir($path)) {
                if (!Jaws_Utils::mkdir($path)) {
                    throw new Exception('DIRECTORY_ERROR_FILE_UPLOAD');
                }
            }
            $res = Jaws_Utils::UploadFiles($_FILES, $path, '', null);
            if (Jaws_Error::IsError($res)) {
                throw new Exception($res->getMessage());
            } else if ($res !== false) {
                $data['host_filename'] = $res['file'][0]['host_filename'];
                $data['user_filename'] = $res['file'][0]['user_filename'];
                $data['filetype'] = $res['file'][0]['host_filetype'];
                $data['filesize'] = $res['file'][0]['host_filesize'];
            } else { // file has been uploaded before
                if (empty($data['host_filename'])) {
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                } else {
                    $filename = Jaws_Utils::upload_tmp_dir(). '/' . $data['host_filename'];
                    if (file_exists($filename)) {
                        $target = $path . '/' . $data['host_filename'];
                        $res = Jaws_Utils::rename($filename, $target, false);
                        if ($res === false) {
                            throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                        }
                        $data['host_filename'] = basename($res);
                    } else {
                        throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                    }
                }
            }

            // Insert record
            unset($data['filename']);
            $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $res = $model->Insert($data);
            if (Jaws_Error::IsError($res)) {
                // TODO: delete uploaded file
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
                array('id', 'title', 'description', 'parent', 'hidden',
                    'user_filename', 'host_filename', 'filetype', 'filesize')
            );
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }
            $data['title'] = Jaws_XSS::defilter($data['title']);
            $data['description'] = Jaws_XSS::defilter($data['description']);

            $model = $this->gadget->model->loadAdmin('Files');

            // Validate file
            $id = (int)$data['id'];
            $file = $model->GetFile($id);
            if (Jaws_Error::IsError($file)) {
                throw new Exception($file->getMessage());
            }

            // Upload file
            $path = $GLOBALS['app']->getDataURL('directory');
            if (!is_dir($path)) {
                if (!Jaws_Utils::mkdir($path, 2)) {
                    throw new Exception('DIRECTORY_ERROR_FILE_UPLOAD');
                }
            }
            $res = Jaws_Utils::UploadFiles($_FILES, $path, '', null);
            if (Jaws_Error::IsError($res)) {
                throw new Exception($res->getMessage());
            } else if ($res !== false) {
                $data['host_filename'] = $res['file'][0]['host_filename'];
                $data['user_filename'] = $res['file'][0]['user_filename'];
                $data['filetype'] = $res['file'][0]['host_filetype'];
                $data['filesize'] = $res['file'][0]['host_filesize'];
            } else {
                if ($data['host_filename'] === ':nochange:') {
                    unset($data['host_filename']);
                } else if (empty($data['host_filename'])) {
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                } else {
                    $filename = Jaws_Utils::upload_tmp_dir(). '/' . $data['host_filename'];
                    if (file_exists($filename)) {
                        $target = $path . '/' . $data['host_filename'];
                        $res = Jaws_Utils::rename($filename, $target, false);
                        if ($res === false) {
                            throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                        }
                        $data['host_filename'] = basename($res);
                    } else {
                        throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                    }
                }
            }

            // Update file in database
            unset($data['user']);
            $data['updatetime'] = time();
            $data['hidden'] = $data['hidden']? true : false;
            $model = $this->gadget->model->loadAdmin('Files');
            $res = $model->Update($id, $data);
            if (Jaws_Error::IsError($res)) {
                throw new Exception(_t('DIRECTORY_ERROR_FILE_UPDATE'));
            }

        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_FILE_UPDATED'), RESPONSE_NOTICE);
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
//        return $this->gadget->urlMap($action, array('id' => $id));
        return BASE_SCRIPT . "?gadget=Directory&action=$action&id=" . $id;
    }

    /**
     * Reads text file content
     *
     * @access  public
     * @return  string  Textual content
     */
    function GetFileContent($id)
    {
        $model = $this->gadget->model->loadAdmin('Files');
        $file = $model->GetFile($id);
        if (Jaws_Error::IsError($file) || empty($file) || empty($file['host_filename'])) {
            return;
        }
        $filename = $GLOBALS['app']->getDataURL('directory/') . $file['host_filename'];
        if (!file_exists($filename)) {
            return;
        }
        return file_get_contents($filename);
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

        $tpl = $this->gadget->template->loadAdmin('Media.html');
        $tpl->SetBlock($type);
        if ($type === 'text') {
            $tpl->SetVariable('text', $this->GetFileContent($id));
        } else {
            $tpl->SetVariable('url', $this->GetDownloadURL($id, true));
        }
        $tpl->ParseBlock($type);

        return $this->gadget->ParseText($tpl->get(), 'Directory', 'index');
    }

    /**
     * Uploads file to system temp directory
     *
     * @access  public
     * @return  string  JavaScript snippet
     */
    function UploadFile()
    {
        $res = Jaws_Utils::UploadFiles($_FILES, Jaws_Utils::upload_tmp_dir(), '', null);
        if (Jaws_Error::IsError($res)) {
            $response = array('type' => 'error',
                              'message' => $res->getMessage());
        } else {
            $response = array('type' => 'notice',
                              'user_filename' => $res['file'][0]['user_filename'],
                              'host_filename' => $res['file'][0]['host_filename'],
                              'filetype' => $res['file'][0]['host_filetype'],
                              'filesize' => $res['file'][0]['host_filesize']);
        }

        $response = Jaws_UTF8::json_encode($response);
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
        $filename = $GLOBALS['app']->getDataURL("directory/") . $file['host_filename'];
        if (!file_exists($filename)) {
            return Jaws_HTTPError::Get(404);
        }

        // Stream file
        if (!Jaws_Utils::Download($filename, $file['user_filename'], $file['filetype'], $open)) {
            return Jaws_HTTPError::Get(500);
        }

        return true;
    }
}