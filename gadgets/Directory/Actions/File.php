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
     * Upload File UI
     *
     * @access  public
     * @return  string  HTML content
     */
    function UploadFileUI()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $parent = (int)jaws()->request->fetch('parent');
        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('UploadFile.html');
        $tpl->SetBlock('uploadUI');

        $this->SetTitle(_t('DIRECTORY_UPLOAD_FILE'));
        $tpl->SetVariable('title', _t('DIRECTORY_UPLOAD_FILE'));

        $tpl->SetVariable('parentId', $parent);

        $tpl->SetVariable('lbl_file', _t('DIRECTORY_FILE'));
        $tpl->SetVariable('lbl_thumbnail', _t('DIRECTORY_THUMBNAIL'));
        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_desc', _t('DIRECTORY_FILE_DESC'));
        $tpl->SetVariable('lbl_tags', _t('DIRECTORY_FILE_TAGS'));
        $tpl->SetVariable('lbl_url', _t('DIRECTORY_FILE_URL'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        $tpl->SetVariable('lbl_ok', _t('GLOBAL_OK'));

        if ($this->gadget->GetPermission('PublishFiles')) {
            $tpl->SetBlock('uploadUI/published');
            $tpl->SetVariable('lbl_published', _t('GLOBAL_PUBLISHED'));
            $tpl->ParseBlock('uploadUI/published');
        }

        $description =& $GLOBALS['app']->LoadEditor('Directory', 'description', false);
        $description->setId('description');
        $description->TextArea->SetRows(8);
        $tpl->SetVariable('description', $description->Get());

        $tpl->SetVariable('root', _t('DIRECTORY_HOME'));
        $tpl->SetVariable('root_url', $this->gadget->urlMap('Directory'));
        $tpl->SetVariable('path', $this->GetPath($parent));

        $tpl->ParseBlock('uploadUI');
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
     * Uploads file to system temp directory
     *
     * @access  public
     * @return  string  JavaScript snippet
     */
    function UploadFile()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $type = jaws()->request->fetch('type', 'post');

        $res = Jaws_Utils::UploadFiles($_FILES, Jaws_Utils::upload_tmp_dir(), '', null);
        if (Jaws_Error::IsError($res)) {
            $response = array('type' => 'error',
                'message' => $res->getMessage());
        } else {
            $response = array('type' => 'notice',
                'user_filename' => $res['file'][0]['user_filename'],
                'host_filename' => $res['file'][0]['host_filename'],
                'mime_type' => $res['file'][0]['host_filetype'],
                'file_size' => $res['file'][0]['host_filesize'],
                'upload_type' => $type);
        }

        $response = Jaws_UTF8::json_encode($response);
        return "<script>parent.onUpload($response);</script>";
    }

    /**
     * Creates a new file
     *
     * @access  public
     * @return  array   Response array
     */
    function CreateFile()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        try {
            $data = jaws()->request->fetch(
                array('parent', 'title', 'description', 'parent', 'published',
                    'user_filename', 'host_filename', 'mime_type', 'file_size', 'thumbnailPath')
            );
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }

            $model = $this->gadget->model->load('Files');

            // Validate parent
            if ($data['parent'] != 0) {
                $parent = $model->GetFile($data['parent']);
                if (Jaws_Error::IsError($parent)) {
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                }
            }

            $data['is_dir'] = false;
            $data['title'] = Jaws_XSS::defilter($data['title']);
            $data['description'] = Jaws_XSS::defilter($data['description']);
            if (!$this->gadget->GetPermission('PublishFiles')) {
                $data['published'] = false;
            }

            // Upload file
            $path = JAWS_DATA . 'directory';
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
                    // move file from temp to data folder
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

                    // move thumbnail file from temp to data folder
                    $thumbnailTempFilename = Jaws_Utils::upload_tmp_dir(). '/' . $data['thumbnailPath'];
                    if (!empty($data['thumbnailPath']) && file_exists($thumbnailTempFilename)) {

                        $pathInfo = pathinfo($data['host_filename']);
                        $originalFilename = $pathInfo['filename'];
                        $thumbnailFinalFilename = $originalFilename . '.thumbnail.png';

                        // Save resize thumbnail file
                        $thumbSize = $this->gadget->registry->fetch('thumbnail_size');
                        $thumbSize = empty($thumbSize) ? '128x128' : $thumbSize;
                        $thumbSize = explode('x', $thumbSize);

                        $objImage = Jaws_Image::factory();
                        if (Jaws_Error::IsError($objImage)) {
                            return Jaws_Error::raiseError($objImage->getMessage());
                        }
                        $objImage->load($thumbnailTempFilename);
                        $objImage->resize($thumbSize[0], $thumbSize[1]);
                        $res = $objImage->save($path . '/' . $thumbnailFinalFilename, 'png');
                        $objImage->free();
                        if (Jaws_Error::IsError($res)) {
                            return $res;
                        }
                        Jaws_Utils::delete($thumbnailTempFilename);
                    }
                    unset($data['thumbnailPath']);
                }
            }

            // Insert record
            unset($data['filename']);
            $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $data['file_type'] = $this->getFileType($data['user_filename']);
            $id = $model->InsertFile($data);
            if (Jaws_Error::IsError($id)) {
                // TODO: delete uploaded file
                throw new Exception(_t('DIRECTORY_ERROR_FILE_CREATE'));
            }

            // Insert Tags
            if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
                $tags = jaws()->request->fetch('tags');
                if (!empty($tags)) {
                    $tModel = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
                    $tModel->InsertReferenceTags('Directory', 'file', $id, $data['published'], time(), $tags);
                }
            }

            // shout Activities event
            $this->gadget->event->shout('Activities', array('action'=>'File'));

        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_FILE_CREATED'), RESPONSE_NOTICE);
    }

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

}