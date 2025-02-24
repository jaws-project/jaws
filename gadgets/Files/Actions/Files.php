<?php
/**
 * Files Gadget
 *
 * @category   Gadget
 * @package    Files
 */
class Files_Actions_Files extends Jaws_Gadget_Action
{
    /**
     * Get display uploaded reference files interface
     *
     * @access  public
     * @param   object  $tpl        Jaws_Template object
     * @param   array   $interface  Gadget interface(gadget, action, reference, ...)
     * @param   array   $options    User interface control options(maxsize, types, labels, ...)
     * @return  void
     */
    function displayReferenceFiles(&$tpl = null, $interface = array(), $options = array())
    {
        // FIXME: temporary solution
        if (@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            $postedData = $this->gadget->request->fetch(
                array('interface:array', 'options:array'),
                'post'
            );
            list($interface, $options) = array_values($postedData);

            if (!empty($interface['reference'])) {
                // call gadget hook for check access permission
                $objHook = Jaws_Gadget::getInstance($interface['gadget'])->hook->load('Files');
                if (!Jaws_Error::IsError($objHook)) {
                    $allowed = $objHook->Execute($interface);
                    if (Jaws_Error::IsError($allowed) || !$allowed) {
                        return $this->gadget->session->response(
                            Jaws::t('HTTP_ERROR_TITLE_403'),
                            RESPONSE_ERROR,
                            null,
                            403
                        );
                    }
                }

                $files = $this->gadget->model->load('Files')->getFiles($interface);
                foreach ($files as $ndx => $file) {
                    $files[$ndx]['fileurl'] = $this->gadget->urlMap(
                        'file',
                        array('id' => $file['id'], 'key' => $file['filekey'])
                    );
                    unset(
                        $files[$ndx]['id'], $files[$ndx]['type'], $files[$ndx]['public'],
                        $files[$ndx]['filename'], $files[$ndx]['mimetype'], $files[$ndx]['filetype'],
                        $files[$ndx]['filesize'], $files[$ndx]['filetime'], $files[$ndx]['filekey']
                    );
                }

                $tpl = $this->gadget->template->load('Files.html', array('rawStore' => true));
                $tpl->SetBlock('files/file');
                $template = $tpl->GetRawBlockContent('', false);

                return $this->gadget->session->response(
                    '',
                    RESPONSE_NOTICE,
                    array(
                        'files' => $files,
                        'template' => $template
                    )
                );
            }

            return $this->gadget->session->response(
                'reference is empty',
                RESPONSE_ERROR,
                null,
                404
            );
        }

        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/files");

        if (!empty($interface['reference'])) {
            try {
                // call gadget hook for check access permission
                $objHook = Jaws_Gadget::getInstance($interface['gadget'])->hook->load('Files');
                if (!Jaws_Error::IsError($objHook)) {
                    $allowed = $objHook->Execute($interface);
                    if (Jaws_Error::IsError($allowed) || !$allowed) {
                        throw new Exception(Jaws::t('HTTP_ERROR_TITLE_403'), 403);
                    }
                }

                $files = $this->gadget->model->load('Files')->getFiles($interface);
                foreach ($files as $file) {
                    $tpl->SetBlock("$block/files/file");
                    $tpl->SetVariable('title', $file['title']);
                    $tpl->SetVariable('postname', $file['postname']);
                    $tpl->SetVariable('filehits', $file['filehits']);
                    $tpl->SetVariable('lbl_file', $options['labels']['title']);
                    $tpl->SetVariable(
                        'fileurl',
                        $this->gadget->urlMap(
                            'file',
                            array('id' => $file['id'], 'key' => $file['filekey'])
                        )
                    );

                    $tpl->ParseBlock("$block/files/file");
                }
            } catch (Exception $error) {
                // do nothing
            }

        }

        $tpl->ParseBlock("$block/files");
    }

    /**
     * Get display uploaded reference files interface(new template engine version)
     *
     * @access  public
     * @param   array   $interface  Gadget interface(gadget, action, reference, ...)
     * @param   array   $options    User interface control options(maxsize, types, labels, ...)
     * @return  array   Files array
     */
    function xdisplayReferenceFiles($interface = array(), $options = array())
    {
        $result = array();
        if (!empty($interface['reference'])) {
            try {
                // call gadget hook for check access permission
                $objHook = Jaws_Gadget::getInstance($interface['gadget'])->hook->load('Files');
                if (!Jaws_Error::IsError($objHook)) {
                    $allowed = $objHook->Execute($interface);
                    if (Jaws_Error::IsError($allowed) || !$allowed) {
                        throw new Exception(Jaws::t('HTTP_ERROR_TITLE_403'), 403);
                    }
                }

                $files = $this->gadget->model->load('Files')->getFiles($interface);
                if (!Jaws_Error::IsError($files)) {
                    $result = $files;
                }
            } catch (Exception $error) {
                // do nothing
            }
        }

        return $result;
    }

    /**
     * Get upload reference files interface
     *
     * @access  public
     * @param   object  $tpl        Jaws_Template object
     * @param   array   $interface  Gadget interface(gadget, action, reference, ...)
     * @param   array   $options    User interface control options(maxsize, types, labels, ...)
     * @return  void
     */
    function loadReferenceFiles(&$tpl = null, $interface = array(), $options = array())
    {
        // FIXME: temporary solution
        if (@$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            $postedData = $this->gadget->request->fetch(
                array('interface:array', 'options:array'),
                'post'
            );
            list($interface, $options) = array_values($postedData);

            if (!empty($interface['reference'])) {
                // call gadget hook for check access permission
                $objHook = Jaws_Gadget::getInstance($interface['gadget'])->hook->load('Files');
                if (!Jaws_Error::IsError($objHook)) {
                    $allowed = $objHook->Execute($interface);
                    if (Jaws_Error::IsError($allowed) || !$allowed) {
                        return $this->gadget->session->response(
                            Jaws::t('HTTP_ERROR_TITLE_403'),
                            RESPONSE_ERROR,
                            null,
                            403
                        );
                    }
                }

                $filesPath = strtolower('files/'. $interface['gadget']. '/'. $interface['action']. '/');
                $files = $this->gadget->model->load('Files')->getFiles($interface);
                foreach ($files as $ndx => $file) {
                    $files[$ndx]['fileurl'] = $this->gadget->urlMap(
                        'file',
                        array('id' => $file['id'], 'key' => $file['filekey'])
                    );
                    unset(
                        $files[$ndx]['type'], $files[$ndx]['public'],
                        $files[$ndx]['mimetype'], $files[$ndx]['filetype'],
                        $files[$ndx]['filetime'], $files[$ndx]['filekey']
                    );
                }

                return $this->gadget->session->response(
                    '',
                    RESPONSE_NOTICE,
                    $files
                );
            }

            return $this->gadget->session->response(
                'reference is empty',
                RESPONSE_ERROR,
                null,
                404
            );
        }

        // FIXME:: add registry key for set maximum upload file size
        $defaultOptions = array(
            'maxsize'    => 33554432, // 32MB
            'mincount'   => 0,
            'maxcount'   => 8,
            'dimension'  => '',
            'filetype'   => 0,
            'extensions' => '',
            'preview'    => true,
            'capture'    => false,
        );
        $options = array_merge($defaultOptions, $options);

        $defaultInterface = array(
            'gadget'     => '',
            'action'     => '',
            'reference'  => 0,
            'type'       => 0,
        );
        $interface = array_merge($defaultInterface, $interface);
        // optional input_reference for new record(without reference id)
        // or update/insert multi references together
        if (!array_key_exists('input_reference', $interface)) {
            $interface['input_reference'] = $interface['reference'];
        }

        $this->AjaxMe('index.js');
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/files");

        $tpl->SetVariable('lbl_file',   $options['labels']['title']);
        $tpl->SetVariable('lbl_browse', $options['labels']['browse']);
        $tpl->SetVariable('lbl_remove', $options['labels']['remove']);
        $tpl->SetVariable('input_action',    strtolower($interface['action']));
        $tpl->SetVariable('input_reference', strtolower($interface['input_reference']));
        $tpl->SetVariable('input_type', $interface['type']);
        $tpl->SetVariable('maxsize',    $options['maxsize']);
        $tpl->SetVariable('mincount',   $options['mincount']);
        $tpl->SetVariable('maxcount',   $options['maxcount']);
        $tpl->SetVariable('dimension',  $options['dimension']);
        $tpl->SetVariable('extensions', $options['extensions']);
        $tpl->SetVariable('capture', $options['capture']? 'capture' : '');     
        $tpl->SetVariable('preview', $options['preview']);
        // set accept file type
        $accept = '*';
        $extensions = array_filter(explode(',', (string)$options['extensions']));
        if (!empty($extensions)) {
            array_walk($extensions, 'ltrim', '.');
            $accept = '.'. implode(',.', $extensions);
        } elseif (in_array($options['filetype'], array(2, 3, 4, 5, 6, 7))) {
            $assigns['mimetype'] = strtolower(array_flip(JAWS_FILE_TYPE)[$options['filetype']]). '/*';
        }
        $tpl->SetBlock("$block/files/accept");
        $tpl->SetVariable('accept', $accept);
        $tpl->ParseBlock("$block/files/accept");

        if (!empty($interface['reference'])) {
            try {
                // call gadget hook for check access permission
                $objHook = Jaws_Gadget::getInstance($interface['gadget'])->hook->load('Files');
                if (!Jaws_Error::IsError($objHook)) {
                    $allowed = $objHook->Execute($interface);
                    if (Jaws_Error::IsError($allowed) || !$allowed) {
                        throw new Exception(Jaws::t('HTTP_ERROR_TITLE_403'), 403);
                    }
                }

                $files = $this->gadget->model->load('Files')->getFiles($interface);
                $filesPath = strtolower('files/'. $interface['gadget']. '/'. $interface['action']. '/');
                foreach ($files as $file) {
                    $tpl->SetBlock("$block/files/file");

                    $fileurl = $this->gadget->urlMap(
                        'file',
                        array('id' => $file['id'], 'key' => $file['filekey'])
                    );

                    if ($options['preview']) {
                        switch (substr($file['mimetype'], 0, strpos($file['mimetype'], '/'))) {
                            case 'image':
                                $tpl->SetBlock("$block/files/file/image_preview");
                                $tpl->SetVariable('fileurl', $fileurl);
                                $tpl->ParseBlock("$block/files/file/image_preview");
                                break;
                        }
                    }

                    $tpl->SetVariable('fileurl', $fileurl);
                    $tpl->SetVariable('input_action', strtolower($interface['action']));
                    $tpl->SetVariable('input_reference', strtolower($interface['input_reference']));
                    $tpl->SetVariable('input_type', $interface['type']);
                    $tpl->SetVariable('fid', $file['id']);
                    $tpl->SetVariable('filename', $file['title']);
                    $tpl->SetVariable('filesize', $file['filesize']);
                    $tpl->SetVariable('lbl_remove', $options['labels']['remove']);
                    $tpl->ParseBlock("$block/files/file");
                }
            } catch (Exception $error) {
                // do nothing
            }
        }

        $tpl->ParseBlock("$block/files");
    }

    /**
     * Get upload reference files interface(new template engine version)
     *
     * @access  public
     * @param   array   $interface  Gadget interface(gadget, action, reference, ...)
     * @param   array   $options    User interface control options(maxsize, types, labels, ...)
     * @return  array   Array of upload files interface data & options
     */
    function xloadReferenceFiles($interface = array(), $options = array())
    {
        // FIXME:: add registry key for set maximum upload file size
        $defaultOptions = array(
            'maxsize'    => 33554432, // 32MB
            'mincount'   => 0,
            'maxcount'   => 8,
            'dimension'  => '',
            'filetype'   => 0,
            'extensions' => '',
            'preview'    => true,
            'capture'    => false,
        );
        $options = array_merge($defaultOptions, $options);

        $defaultInterface = array(
            'gadget'     => '',
            'action'     => '',
            'reference'  => 0,
            'type'       => 0,
        );
        $interface = array_merge($defaultInterface, $interface);
        // optional input_reference for new record(without reference id)
        // or update/insert multi references together
        if (!array_key_exists('input_reference', $interface)) {
            $interface['input_reference'] = $interface['reference'];
        }

        $this->AjaxMe('index.js');

        // initiate assign with option array 
        $assigns = $options;
        // accept file type
        $accept = '*';
        $extensions = array_filter(explode(',', (string)$options['extensions']));
        if (!empty($extensions)) {
            array_walk($extensions, 'ltrim', '.');
            $accept = '.'. implode(',.', $extensions);
        } else {
            switch ($options['filetype']) {
                case JAWS_FILE_TYPE['TEXT']:
                    $accept = 'text/*';
                    break;

                case JAWS_FILE_TYPE['IMAGE']:
                    $accept = 'image/*';
                    break;

                case JAWS_FILE_TYPE['AUDIO']:
                    $accept = 'audio/*';
                    break;

                case JAWS_FILE_TYPE['VIDEO']:
                    $accept = 'video/*';
                    break;
            }
        }
        $assigns['accept'] = $accept;

        $assigns['interface'] = $interface;
        $assigns['input_action'] = strtolower($interface['action']);
        $assigns['input_reference'] = strtolower($interface['input_reference']);
        $assigns['input_type'] = $interface['type'];

        // files
        $assigns['files'] = array();
        if (!empty($interface['reference'])) {
            $files = $this->gadget->model->load('Files')->getFiles($interface);
            if (!Jaws_Error::IsError($files)) {
                $assigns['files'] = $files;
            }
        }

        return $assigns;
    }

    /**
     * inspect reference files
     *
     * @access  public
     * @param   array   $interface  Interface(gadget, action, reference)
     * @return  int|Jaws_Error      Returns uploaded files or Jaws_Error on failure
     */
    function inspectReferenceFiles(array &$interface, $options = array())
    {
        // FIXME:: add registry key for set maximum upload file size
        $defaultOptions = array(
            'maxsize'     => 33554432, // 32MB
            'mincount'    => 0,
            'maxcount'    => 8,
            'dimension'   => '',
            'extensions'  => '',
            'imageformat' => '',
        );
        $options = array_merge($defaultOptions, $options);

        $defaultInterface = array(
            'gadget'     => '',
            'action'     => '',
            'folder'     => '',
            'reference'  => 0,
            'type'       => 0,
        );
        $interface = array_merge($defaultInterface, $interface);

        // optional input_reference for new record(without reference id)
        // or update/insert multi references together
        if (!array_key_exists('input_reference', $interface)) {
            $interface['input_reference'] = $interface['reference'];
        }

        $filesModel = $this->gadget->model->load('Files');
        $oldFiles = $filesModel->getFiles($interface);

        // unique name of upload action/reference interface
        $uploadFilesIndex = strtolower(
            $interface['action'] . '_'. $interface['input_reference']. '_'. $interface['type']
        );

        $is_in_upload_interface = (bool)$this->app->request->fetch("files_upload_interface");
        if (!$is_in_upload_interface) {
            return false;
        }

        $oldFilesIndex = 'old_files_'. $uploadFilesIndex;
        $remains = $this->app->request->fetch("$oldFilesIndex:array?array");
        $oldFilesCount = empty($remains)? 0 : count($remains);

        $newFilesCount = 0;
        $newFilesIndex = 'new_files_'. $uploadFilesIndex;
        if (array_key_exists($newFilesIndex, $_FILES)) {
            $newFilesCount = count($_FILES[$newFilesIndex]['name']);
        }
        // check min count of files
        if (($oldFilesCount + $newFilesCount) < $options['mincount']) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_UPLOAD_MIN_COUNT'),
                406,
                JAWS_ERROR_NOTICE
            );
        }
        // check max count of files
        if (($options['maxcount'] > 0) && ($oldFilesCount + $newFilesCount) > $options['maxcount']) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_UPLOAD_MAX_COUNT'),
                406,
                JAWS_ERROR_NOTICE
            );
        }

        $inspectResult = array(
            'news' => array(),
            'olds' => $oldFiles, 
            'remains' => $remains,
            'interface' => $interface,
        );

        if (array_key_exists($newFilesIndex, $_FILES)) {
            $uploadPath = ROOT_DATA_PATH. strtolower('files/'. $interface['gadget']. '/'. $interface['action']. '/');
            $uploadPath.= ((string)$interface['folder'] === '')? '' : ($interface['folder']. '/');
            $newFiles = $this->gadget->fileManagement::uploadFiles(
                $_FILES[$newFilesIndex],
                $uploadPath,
                $options['extensions'],
                null,
                false,
                $options['maxsize'],
                $options['dimension'],
                $options['imageformat']
            );

            if (Jaws_Error::IsError($newFiles)) {
                return $newFiles;
            }

            if (!empty($newFiles)) {
                $inspectResult['news'] = $newFiles[0];
            }
        }

        return $inspectResult;
    }

    /**
     * Upload/Insert/Update reference files
     *
     * @access  public
     * @param   array   $interface      Gadget interface(gadget, action, reference, ...)
     * @param   array   $options        User interface control options(maxsize, maxcount, extensions)
     * @param   array|bool  $inspectResult  Result of inspectReferenceFiles method
     * @return  mixed   Array of files id otherwise Jaws_Error on error
     */
    function uploadReferenceFiles($interface, array $options = array(), $inspectResult = array())
    {
        if ($inspectResult === false) {
            return false;
        } else if (empty($inspectResult)) {
            $inspectResult = $this->inspectReferenceFiles($interface, $options);
            if (Jaws_Error::IsError($inspectResult) || empty($inspectResult)) {
                return empty($inspectResult)? false : $inspectResult;
            }
        } else {
            $interface = array_merge(
                $inspectResult['interface'],
                array(
                    'reference' => $interface['reference'],
                    'folder' => $interface['folder']?? '',
                )
            );
        }

        $resultFiles = array(
            'news' => array(),
            'olds' => array(),
        );
        $filesModel = $this->gadget->model->load('Files');

        //FIXME: need improvement for multi files delete
        foreach ($inspectResult['olds'] as $file) {
            if (!in_array($file['id'], $inspectResult['remains'])) {
                $filesModel->deleteFiles(
                    $interface,
                    $file['id']
                );
            } else {
                $resultFiles['olds'][] = $file;
            }
        }

        $move2folder = ($interface['reference'] != $interface['input_reference']) && !empty($interface['folder']);
        if (!empty($inspectResult['news'])) {
            $result = $filesModel->insertFiles(
                $interface,
                $inspectResult['news'],
                $move2folder
            );
            if (!Jaws_Error::IsError($result)) {
                call_user_func_array('array_push', array_merge(array(&$resultFiles['news']), $result));
            }
        }

        return $resultFiles;
    }

    /**
     * Get interface(gadget/action/reference) files
     *
     * @access  public
     * @return  array   Response array include files attributes
     */
    function files()
    {
        $interface = $this->gadget->request->fetch(
            array('gadget', 'action', 'reference', 'type', 'public|boolean'),
            'post',
            'interface'
        );
        $interface = array_filter($interface, static function($val){return !is_null($val);});

        $error_code = 404;
        $error_message = Jaws::t('HTTP_ERROR_TITLE_404');
        if (!empty($interface['reference'])) {
            try {
                // call gadget hook for check access permission
                $objHook = Jaws_Gadget::getInstance($interface['gadget'])->hook->load('Files');
                if (!Jaws_Error::IsError($objHook)) {
                    $allowed = $objHook->Execute($interface);
                    if (Jaws_Error::IsError($allowed) || !$allowed) {
                        throw new Exception(Jaws::t('HTTP_ERROR_TITLE_403'), 403);
                    }
                }

                $files = $this->gadget->model->load('Files')->getFiles($interface);
                if (!Jaws_Error::IsError($files)) {
                    return $this->gadget->session->response(
                        '',
                        RESPONSE_NOTICE,
                        $files
                    );
                }
            } catch (Exception $error) {
                $error_code = $error->getCode();
                $error_message = $error->getMessage();
            }
        }

        return $this->gadget->session->response(
            $error_message,
            RESPONSE_ERROR,
            null,
            $error_code
        );
    }

    /**
     * Download/View file
     *
     * @access  public
     * @return  string   Requested file content or HTML error page
     */
    function file()
    {
        $get = $this->gadget->request->fetch(array('id', 'key'), 'get');
        $file = $this->gadget->model->load('Files')->getFile((int)$get['id']);
        if (Jaws_Error::IsError($file)) {
            $this->SetActionMode('file', 'normal', 'standalone');
            return Jaws_HTTPError::Get(500);
        }

        if (!empty($file) && $file['filekey'] == $get['key']) {
            $filepath = strtolower('files/'. $file['gadget']. '/'. $file['action']. '/');
            $filepath.= ((string)$file['folder'] === '')? '' : ($file['folder'] . '/');
            if ($this->gadget->fileManagement::file_exists(ROOT_DATA_PATH. $filepath . $file['filename'])) {
                // set response type to raw  because HTTP headers managed by fileManagement::download method
                $this->app->request->update('restype', 'raw');
                // increase file hits
                $this->gadget->model->load('Files')->hitDownload($file['id']);
                // download
                if ($this->gadget->fileManagement::download(
                    ROOT_DATA_PATH. $filepath . $file['filename'],
                    $file['postname'],
                    $file['mimetype']
                )) {
                    return;
                }
            }
        }

        $this->SetActionMode('file', 'normal', 'standalone');
        return Jaws_HTTPError::Get(404);
    }

}