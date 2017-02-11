<?php
/**
 * Directory Gadget
 *
 * @category    GadgetModel
 * @package     Directory
 */
class Directory_Model_Admin_Files extends Jaws_Gadget_Model
{
    /**
     * Inserts a new file/directory
     *
     * @access  public
     * @param   array   $data    File data
     * @return  mixed   Query result
     */
    function InsertFile($data)
    {
        $data['public'] = (bool)$data['public'];
        $data['parent'] = (int)$data['parent'];
        if (!$data['public']) {
            $data['key'] = mt_rand(1000, 9999999999);
        }
        $data['create_time'] = $data['update_time'] = time();
        return  Jaws_ORM::getInstance()->table('directory')->insert($data)->exec();
    }

    /**
     * Update a file/directory
     *
     * @access  public
     * @param   int     $id      File id
     * @param   array   $data    File data
     * @return  mixed   Query result
     */
    function UpdateFile($id, $data)
    {
        $data['public'] = (bool)$data['public'];
        $data['parent'] = (int)$data['parent'];
        $data['update_time'] = time();
        return Jaws_ORM::getInstance()->table('directory')->update($data)->where('id', $id)->exec();
    }

    /**
     * Creates a new file
     *
     * @access  public
     * @return  array   Response array
     */
    function SaveFile($data)
    {
        try {
            $loggedUser = (int)$GLOBALS['app']->Session->GetAttribute('user');
            // Validate parent
            if ($data['parent'] != 0) {
                $parent = $this->gadget->model->load('Files')->GetFile($data['parent']);
                if (Jaws_Error::IsError($parent)) {
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                }
            }

            $data['is_dir'] = false;
            $data['public'] = (bool)$data['public'];
            $data['title'] = Jaws_XSS::defilter($data['title']);
            $data['description'] = Jaws_XSS::defilter($data['description']);
            if (!$this->gadget->GetPermission('PublishFiles')) {
                $data['published'] = !$data['public'];
            } else {
                $data['published'] = is_null($data['published']) ? true : (bool)$data['published'];
            }

            $dirPath = JAWS_DATA . 'directory';
            if (!is_dir($dirPath)) {
                if (!Jaws_Utils::mkdir($dirPath)) {
                    throw new Exception('DIRECTORY_ERROR_FILE_UPLOAD');
                }
            }

            if (!empty($data['id'])) {
                $dbFileInfo = $this->gadget->model->load('Files')->GetFile($data['id']);
                if (Jaws_Error::IsError($dbFileInfo) || empty($dbFileInfo)) {
                    return Jaws_HTTPError::Get(404);
                }
                if (($dbFileInfo['user'] != $loggedUser) ||
                    ($dbFileInfo['public'] && !$this->gadget->GetPermission('PublishFiles'))
                ) {
                    return Jaws_HTTPError::Get(403);
                }
            }

            $files = Jaws_Utils::UploadFiles($_FILES, $dirPath, '', null);
            if (Jaws_Error::IsError($files)) {
                throw new Exception($files->getMessage());
            }

            if (isset($files['file'])) {
                $data['host_filename'] = $files['file'][0]['host_filename'];
                $data['user_filename'] = $files['file'][0]['user_filename'];
                $data['mime_type'] = $files['file'][0]['host_filetype'];
                $data['file_size'] = $files['file'][0]['host_filesize'];
            } elseif (isset($dbFileInfo)) {
                $data['host_filename'] = $dbFileInfo['host_filename'];
                $data['user_filename'] = $dbFileInfo['user_filename'];
                $data['mime_type'] = $dbFileInfo['mime_type'];
                $data['file_size'] = $dbFileInfo['file_size'];
            } else {
                // File is mandatory
                throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
            }

            if (isset($files['thumbnail'])) {
                $thumbfile = $files['thumbnail'][0]['host_filename'];

                // Save resize thumbnail file
                $thumbSize = $this->gadget->registry->fetch('thumbnail_size');
                $thumbSize = empty($thumbSize) ? '128x128' : $thumbSize;
                $thumbSize = explode('x', $thumbSize);

                $objImage = Jaws_Image::factory();
                if (Jaws_Error::IsError($objImage)) {
                    throw new Exception($objImage->getMessage());
                }
                $res = $objImage->load($dirPath. '/'. $thumbfile);
                if (Jaws_Error::IsError($result)) {
                    throw new Exception($res->getMessage());
                }
                $objImage->resize($thumbSize[0], $thumbSize[1]);
                $res = $objImage->save($dirPath. '/'. basename($data['host_filename']). '.thumbnail.png', 'png');
                $objImage->free();
                if (Jaws_Error::IsError($res)) {
                    throw new Exception($res->getMessage());
                }

                Jaws_Utils::delete($dirPath. '/'. $thumbfile);
            }

            if(isset($data['user_filename'])) {
                $data['file_type'] = $this->gadget->model->load('Files')->getFileType($data['user_filename']);
            }

            if (!empty($data['id'])) {
                // update old file info
                $result = $this->UpdateFile($data['id'], $data);
                if (Jaws_Error::IsError($result)) {
                    // TODO: delete uploaded file
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_CREATE'));
                }

                // Update Tags
                if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
                    $tags = jaws()->request->fetch('tags');
                    if (!empty($tags)) {
                        $tModel = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
                        $tModel->UpdateReferenceTags(
                            'Directory',
                            'file',
                            $data['id'],
                            $data['published'],
                            time(),
                            $tags
                        );
                    }
                }

                return _t('DIRECTORY_NOTICE_FILE_UPDATED');
            } else {
                //insert new file
                unset($data['id']);
                $data['user'] = $loggedUser;
                $id = $this->InsertFile($data);
                if (Jaws_Error::IsError($id)) {
                    // TODO: delete uploaded file
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_CREATE'));
                }

                // Insert Tags
                if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
                    $tags = jaws()->request->fetch('tags');
                    if (!empty($tags)) {
                        $tModel = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
                        $tModel->InsertReferenceTags(
                            'Directory',
                            'file',
                            $id,
                            $data['published'],
                            time(),
                            $tags
                        );
                    }
                }

                // shout Activities event
                $this->gadget->event->shout('Activities', array('action'=>'File'));

                return _t('DIRECTORY_NOTICE_FILE_CREATED');
            }

        } catch (Exception $e) {
            return Jaws_Error::raiseError($e->getMessage(), __FUNCTION__);
        }

    }

    /**
     * Updates parent of the file/directory
     *
     * @access  public
     * @param   int     $id      File ID
     * @param   int     $parent  New file parent
     * @return  mixed   Query result
     */
    function Move($id, $parent)
    {
        $table = Jaws_ORM::getInstance()->table('directory');
        $table->update(array('parent' => $parent));
        return $table->where('id', $id)->exec();
    }

    /**
     * Deletes file/directory
     *
     * @access  public
     * @param   array   $data  File data
     * @return  mixed   Query result
     */
    function DeleteFile($data)
    {
        if ($data['is_dir']) {
            $files = $this->gadget->model->load('Files')->GetFiles(array('parent' => $data['id']));
            if (Jaws_Error::IsError($files)) {
                return false;
            }
            foreach ($files as $file) {
                $this->DeleteFile($file);
            }
        }

        // Delete file/folder and related shortcuts
        $table = Jaws_ORM::getInstance()->table('directory');
        $table->delete()->where('id', $data['id']);
        $res = $table->exec();
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        // Delete from disk
        if (!$data['is_dir']) {
            $filename = JAWS_DATA . 'directory/' . $data['host_filename'];
            if (file_exists($filename)) {
                if (!Jaws_Utils::delete($filename)) {
                    return false;
                }
            }

            // delete thumbnail file
            $fileInfo = pathinfo($filename);
            $thumbnailPath = JAWS_DATA . 'directory/' . $fileInfo['filename'] . '.thumbnail.png';
            if (file_exists($thumbnailPath)) {
                if (!Jaws_Utils::delete($thumbnailPath)) {
                    return false;
                }
            }
        }

        return true;
    }

}