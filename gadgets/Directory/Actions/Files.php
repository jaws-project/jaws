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
class Directory_Actions_Files extends Jaws_Gadget_HTML
{
    /**
     * Builds the file management form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function FileForm()
    {
        $type = jaws()->request->fetch('type', 'post');
        $tpl = $this->gadget->loadTemplate('File.html');
        $tpl->SetBlock($type);
        $tpl->SetVariable('lbl_title', _t('DIRECTORY_FILE_TITLE'));
        $tpl->SetVariable('lbl_parent', _t('DIRECTORY_FILE_PARENT'));
        $tpl->SetVariable('lbl_desc', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('lbl_share', _t('DIRECTORY_SHARE'));
        $tpl->SetVariable('lbl_edit', _t('GLOBAL_EDIT'));
        $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
        $tpl->SetVariable('lbl_submit', _t('GLOBAL_SUBMIT'));
        $tpl->SetVariable('lbl_cancel', _t('GLOBAL_CANCEL'));
        if ($type === 'view') {
            $tpl->SetVariable('title', '{title}');
            $tpl->SetVariable('desc', '{description}');
            $tpl->SetVariable('url', '{url}');
        }
        $tpl->ParseBlock($type);
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
            $data = jaws()->request->fetch(array('title', 'description', 'parent', 'url', 'filename'));
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }
            $data['user'] = $data['owner'] = (int)$GLOBALS['app']->Session->GetAttribute('user');
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
            } else {
                if (empty($data['filename'])) {
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                } else {
                    $filename = Jaws_Utils::upload_tmp_dir(). '/' . $data['filename'];
                    if (file_exists($filename)) {
                        $target = $path . '/' . $data['filename'];
                        // FIXME: we need to extract file basename and extension
                        // if (file_exists($target)) {
                            // $target .= '_'. uniqid(floor(microtime()*1000));
                        // }
                        @rename($filename, $target);
                    } else {
                        throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                    }
                }
            }

            // Insert record
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
            $res = $model->InsertFile($data);
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
            $id = jaws()->request->fetch('id');
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $data = jaws()->request->fetch(array('title', 'description', 'parent', 'url', 'filename'));
            if (empty($data['title'])) {
                throw new Exception(_t('DIRECTORY_ERROR_INCOMPLETE_DATA'));
            }
            $data['title'] = Jaws_XSS::defilter($data['title']);
            $data['description'] = Jaws_XSS::defilter($data['description']);

            // File upload
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
            } else {
                if ($data['filename'] === ':nochange:') {
                    unset($data['filename']);
                } else if (empty($data['filename'])) {
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                } else {
                    $filename = Jaws_Utils::upload_tmp_dir(). '/'. $data['filename'];
                    if (file_exists($filename)) {
                        @rename($filename, $path . '/' . $data['filename']);
                    } else {
                        throw new Exception(_t('DIRECTORY_ERROR_FILE_UPLOAD'));
                    }
                }
            }

            // Update record
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');
            $res = $model->UpdateFile($id, $data);
            if (Jaws_Error::IsError($res)) {
                throw new Exception(_t('DIRECTORY_ERROR_FILE_UPDATE'));
            }
        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse($e->getMessage(), RESPONSE_ERROR);
        }

        return $GLOBALS['app']->Session->GetResponse(_t('DIRECTORY_NOTICE_FILE_UPDATED'), RESPONSE_NOTICE);
    }

    /**
     * Deletes file
     *
     * @access  public
     * @return  mixed   Response array or Jaws_Error on error
     */
    function DeleteFile()
    {
        try {
            $id = (int)jaws()->request->fetch('id');
            $model = $GLOBALS['app']->LoadGadget('Directory', 'Model', 'Files');

            // Delete from disk
            $file = $model->GetFile($id);
            if (Jaws_Error::IsError($res)) {
                throw new Exception($res->getMessage());
            }
            $file = $GLOBALS['app']->getDataURL('directory/' . $file['user'] . '/' . $file['filename']);
            if (file_exists($file)) {
                if (!Jaws_Utils::delete($file)) {
                    throw new Exception(_t('DIRECTORY_ERROR_FILE_DELETE'));
                }
            }

            // Delete from database
            $res = $model->DeleteFile($id);
            if (Jaws_Error::IsError($res)) {
                throw new Exception($res->getMessage());
            }
        } catch (Exception $e) {
            return $GLOBALS['app']->Session->GetResponse(
                $e->getMessage(),
                RESPONSE_ERROR
            );
        }

        return $GLOBALS['app']->Session->GetResponse(
            _t('DIRECTORY_NOTICE_FILE_DELETED'),
            RESPONSE_NOTICE
        );
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
                              'message' => $res['file'][0]['host_filename']);
        }

        $response = $GLOBALS['app']->UTF8->json_encode($response);
        return "<script>parent.onUpload($response);</script>";
    }

}