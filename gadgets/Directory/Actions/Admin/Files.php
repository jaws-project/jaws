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
     * Determines file type according to the file extension
     */
    function getFileType($filename)
    {
        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
        if (empty($fileExt)) {
            return Directory_Info::FILE_TYPE_UNKNOWN;
        }
        $FileTypes = array(
            Directory_Info::FILE_TYPE_TEXT    => array('txt', 'doc', 'xml', 'html', 'htm', 'css', 'js', 'php', 'sh'),
            Directory_Info::FILE_TYPE_IMAGE   => array('gif', 'png', 'jpg', 'jpeg', 'raw', 'bmp', 'tiff', 'svg'),
            Directory_Info::FILE_TYPE_AUDIO   => array('wav', 'mp3', 'm4v', 'ogg'),
            Directory_Info::FILE_TYPE_VIDEO   => array('mpg', 'mpeg', 'avi', 'wma', 'rm', 'asf', 'flv', 'mov', 'mp4'),
            Directory_Info::FILE_TYPE_ARCHIVE => array('zip', 'rar', 'tar', 'gz', 'tgz', 'bz2', '7z', '7zip')
        );
        foreach ($FileTypes as $type => $exts) {
            foreach ($exts as $ext) {
                if ($fileExt == $ext) {
                    return $type;
                }
            }
        }
        return Directory_Info::FILE_TYPE_UNKNOWN;
    }

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
        $tpl->SetVariable('lbl_tags', _t('DIRECTORY_FILE_TAGS'));
        $tpl->SetVariable('lbl_hidden', _t('DIRECTORY_FILE_HIDDEN'));
        $tpl->SetVariable('lbl_url', _t('DIRECTORY_FILE_URL'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        if ($mode === 'edit') {
            $editor =& $GLOBALS['app']->LoadEditor('Directory', 'description');
            $editor->TextArea->SetStyle('width:100%; height:60px;');
            $tpl->SetVariable('description', $editor->get());
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
            $tpl->SetVariable('tags', '{tags}');
            $tpl->SetVariable('user_filename', '{user_filename}');
            $tpl->SetVariable('hidden', '{hidden}');
            $tpl->SetVariable('type', '{type}');
            $tpl->SetVariable('mime_type', '{mime_type}');
            $tpl->SetVariable('size', '{size}');
            $tpl->SetVariable('file_size', '{file_size}');
            $tpl->SetVariable('link', '{link}');
            $tpl->SetVariable('create_time', '{create_time}');
            $tpl->SetVariable('update_time', '{update_time}');
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
                    'user_filename', 'host_filename', 'mime_type', 'file_size')
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
                $data['mime_type'] = $res['file'][0]['host_filetype'];
                $data['file_size'] = $res['file'][0]['host_filesize'];
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
            $data['file_type'] = $this->getFileType($data['user_filename']);
            $id = $model->Insert($data);
            if (Jaws_Error::IsError($id)) {
                // TODO: delete uploaded file
                throw new Exception(_t('DIRECTORY_ERROR_FILE_CREATE'));
            }

            // Insert Tags
            if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
                $tags = jaws()->request->fetch('tags');
                if (!empty($tags)) {
                    $tModel = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
                    $tModel->InsertReferenceTags('Directory', 'file', $id, !$data['hidden'], time(), $tags);
                }
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
                    'user_filename', 'host_filename', 'mime_type', 'file_size')
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
                $data['mime_type'] = $res['file'][0]['host_filetype'];
                $data['file_size'] = $res['file'][0]['host_filesize'];
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
            $data['update_time'] = time();
            $data['hidden'] = $data['hidden']? true : false;
            $data['file_type'] = $this->getFileType($data['user_filename']);
            $model = $this->gadget->model->loadAdmin('Files');
            $res = $model->Update($id, $data);
            if (Jaws_Error::IsError($res)) {
                throw new Exception(_t('DIRECTORY_ERROR_FILE_UPDATE'));
            }

            // Update Tags
            if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
                $tags = jaws()->request->fetch('tags');
                $tModel = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
                $tModel->UpdateReferenceTags('Directory', 'file', $id, !$data['hidden'], time(), $tags);
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
    function GetDownloadURL($id = null)
    {
        if ($id === null) {
            $id = (int)jaws()->request->fetch('id');
        }
        return $this->gadget->urlMap('Download', array('id' => $id), true);
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
            $tpl->SetVariable('url', $this->GetDownloadURL($id));
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
                              'mime_type' => $res['file'][0]['host_filetype'],
                              'file_size' => $res['file'][0]['host_filesize']);
        }

        $response = Jaws_UTF8::json_encode($response);
        return "<script>parent.onUpload($response);</script>";
    }
}