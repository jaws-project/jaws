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
        $id = (int)jaws()->request->fetch('id');
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
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }
        try {
            $data = jaws()->request->fetch(
                array('id', 'parent', 'title', 'description', 'public', 'published')
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
            } else {
                $data['published'] = is_null($data['published']) ? true : $data['published'];
            }

            $originalFilename = '';
            // Upload file
            if (!empty($_FILES['file']['size'])) {
                $path = JAWS_DATA . 'directory';
                if (!is_dir($path)) {
                    if (!Jaws_Utils::mkdir($path)) {
                        throw new Exception('DIRECTORY_ERROR_FILE_UPLOAD');
                    }
                }

                $res = Jaws_Utils::UploadFiles($_FILES['file'], $path, '', null);
                if (Jaws_Error::IsError($res)) {
                    throw new Exception($res->getMessage());
                } else if ($res !== false) {
                    $data['host_filename'] = $res[0][0]['host_filename'];
                    $data['user_filename'] = $res[0][0]['user_filename'];
                    $data['mime_type'] = $res[0][0]['host_filetype'];
                    $data['file_size'] = $res[0][0]['host_filesize'];
                }
                $originalFilename = $data['host_filename'];
            } else {
                if (!empty($data['id'])) {
                    $currentFileInfo = $model->GetFile($data['id']);
                    $originalFilename = $currentFileInfo['host_filename'];
                }
            }

            // Upload thumbnail
            if (!empty($_FILES['thumbnail']['size'])) {
                $res = Jaws_Utils::UploadFiles($_FILES['thumbnail'], Jaws_Utils::upload_tmp_dir(), '', null);
                if (Jaws_Error::IsError($res)) {
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                } else {
                    $thumbnailInfo = array('type' => 'notice',
                        'user_filename' => $res[0][0]['user_filename'],
                        'host_filename' => $res[0][0]['host_filename'],
                        'mime_type' => $res[0][0]['host_filetype'],
                        'file_size' => $res[0][0]['host_filesize']);
                }

                // move thumbnail file from temp to data folder
                $thumbnailTempFilename = Jaws_Utils::upload_tmp_dir() . '/' . $thumbnailInfo['host_filename'];
                if (!empty($thumbnailInfo['host_filename']) && file_exists($thumbnailTempFilename)) {
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
            }

//            else {
//                unset($data['user_filename']);
//                unset($data['host_filename']);
//                unset($data['mime_type']);
//                unset($data['file_size']);
//            }
//            unset($data['thumbnailPath']);
//            unset($data['filename']);
            if(isset($data['user_filename'])) {
                $data['file_type'] = $this->getFileType($data['user_filename']);
            }
            $data['user'] = (int)$GLOBALS['app']->Session->GetAttribute('user');

            // update old file info
            if (!empty($data['id'])) {
                // check old file info - user have permission to change it?
                $currentUser = $GLOBALS['app']->Session->GetAttribute('user');
                $fileInfo = $model->GetFile($data['id']);
                if (Jaws_Error::IsError($fileInfo) || empty($fileInfo)) {
                    return Jaws_HTTPError::Get(500);
                }
                if (($fileInfo['user'] != $currentUser) || $fileInfo['public']) {
                    return Jaws_HTTPError::Get(403);
                }
                $res = $model->UpdateFile($data['id'], $data);
                if (Jaws_Error::IsError($res)) {
                    // TODO: delete uploaded file
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_CREATE'));
                }
                // Update Tags
                if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
                    $tags = jaws()->request->fetch('tags');
                    if (!empty($tags)) {
                        $tModel = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
                        $tModel->UpdateReferenceTags('Directory', 'file', $data['id'], $data['published'], time(), $tags);
                    }
                }
            } else {
                //insert new file
                unset($data['id']);
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
            }

            // shout Activities event
            $this->gadget->event->shout('Activities', array('action'=>'File'));

        } catch (Exception $e) {
            $GLOBALS['app']->Session->PushResponse(
                $e->getMessage(),
                'Directory.SaveFile',
                RESPONSE_ERROR
            );
        }

        if(empty($data['id'])) {
            $GLOBALS['app']->Session->PushResponse(
                _t('DIRECTORY_NOTICE_FILE_CREATED'),
                'Directory.SaveFile',
                RESPONSE_NOTICE
            );

        } else {
            $GLOBALS['app']->Session->PushResponse(
                _t('DIRECTORY_NOTICE_FILE_UPDATED'),
                'Directory.SaveFile',
                RESPONSE_NOTICE
            );
        }

        // shout Activities event
        $this->gadget->event->shout('Activities', array('action' => 'Pages'));

        Jaws_Header::Location(
            $this->gadget->urlMap(
                'Directory',
                array('id' => $data['parent'])
            )
        );
    }

    /**
     * Delete a file
     *
     * @access  public
     * @return  mixed   Number of forms or Jaws_Error
     */
    function DeleteFile()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            return Jaws_HTTPError::Get(403);
        }

        $id = (int)jaws()->request->fetch('fileId', 'post');

        $model = $this->gadget->model->load('Files');
        $fileInfo = $this->gadget->model->load('Files')->GetFile($id);
        if (Jaws_Error::IsError($fileInfo)) {
            return Jaws_HTTPError::Get(500);
        }
        if (empty($fileInfo)) {
            return Jaws_HTTPError::Get(404);
        }
        $currentUser = $GLOBALS['app']->Session->GetAttribute('user');
        if ($fileInfo['public'] || $fileInfo['user'] != $currentUser) {
            return Jaws_HTTPError::Get(403);
        }

        $res = $model->DeleteFile($fileInfo);
        if (Jaws_Error::isError($res)) {
            return $GLOBALS['app']->Session->GetResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_ITEMS_DELETED'), RESPONSE_NOTICE);
        }
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