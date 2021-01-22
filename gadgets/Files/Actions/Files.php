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
            $interface = $this->gadget->request->fetchAll('post');
            if (!empty($interface['reference'])) {
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
                RESPONSE_ERROR
            );
        }

        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/files");

        if (!empty($interface['reference'])) {
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
        // initiate assign with option array 
        $assigns = $options;
        $assigns['interface'] = $interface;

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
            $interface = $this->gadget->request->fetchAll('post');
            if (!empty($interface['reference'])) {
                $filesPath = strtolower('files/'. $interface['gadget']. '/'. $interface['action']. '/');
                $files = $this->gadget->model->load('Files')->getFiles($interface);
                foreach ($files as $ndx => $file) {
                    $files[$ndx]['url_file'] = $this->gadget->urlMap(
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
                RESPONSE_ERROR
            );
        }

        // FIXME:: add registry key for set maximum upload file size
        $defaultOptions = array(
            'maxsize'    => 33554432, // 32MB
            'maxcount'   => 0,        // unlimited
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
        $tpl->SetVariable('maxcount',   $options['maxcount']);
        $tpl->SetVariable('dimension',  $options['dimension']);
        $tpl->SetVariable('extensions', $options['extensions']);
        $tpl->SetVariable('capture', $options['capture']? 'capture' : '');     
        $tpl->SetVariable('preview', $options['preview']);
        // set accept file type
        if ($options['filetype'] > 0) {
            $tpl->SetBlock("$block/files/accept");
            switch ($options['filetype']) {
                case JAWS_FILE_TYPE['TEXT']:
                    $tpl->SetVariable('type', 'text');
                    break;

                case JAWS_FILE_TYPE['IMAGE']:
                    $tpl->SetVariable('type', 'image');
                    break;

                case JAWS_FILE_TYPE['AUDIO']:
                    $tpl->SetVariable('type', 'audio');
                    break;

                case JAWS_FILE_TYPE['VIDEO']:
                    $tpl->SetVariable('type', 'video');
                    break;

                default:
                    $tpl->SetVariable('type', '*');
            }
            $tpl->ParseBlock("$block/files/accept");
        }

        if (!empty($interface['reference'])) {
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
            'maxcount'   => 0,        // unlimited
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
        // file type
        if (in_array($options['filetype'], array(2, 3, 4, 5))) {
            $assigns['mimetype'] = strtolower(array_flip(JAWS_FILE_TYPE)[$options['filetype']]). '/*';
        }

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
     * Upload/Insert/Update reference files
     *
     * @access  public
     * @param   array   $interface  Gadget interface(gadget, action, reference, ...)
     * @param   array   $options    User interface control options(maxsize, maxcount, extensions)
     * @return  mixed   TRUE otherwise Jaws_Error on error
     */
    function uploadReferenceFiles($interface, $options = array())
    {
        // FIXME:: add registry key for set maximum upload file size
        $defaultOptions = array(
            'maxsize'     => 33554432, // 32MB
            'maxcount'    => 0,        // unlimited
            'dimension'   => '',
            'extensions'  => '',
            'imageformat' => ''
        );
        $options = array_merge($defaultOptions, $options);

        $defaultInterface = array(
            'gadget'      => '',
            'action'      => '',
            'reference'   => 0,
            'type'        => 0,
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

        $oldFilesIndex = 'old_files_'. $uploadFilesIndex;
        $remainFiles = $this->app->request->fetch("$oldFilesIndex:array");
        $oldFilesCount = empty($remainFiles)? 0 : count($remainFiles);

        $newFilesCount = 0;
        $newFilesIndex = 'new_files_'. $uploadFilesIndex;
        if (array_key_exists($newFilesIndex, $_FILES)) {
            $newFilesCount = count($_FILES[$newFilesIndex]['name']);
        }
        // check max count of files
        if ($options['maxcount'] > 0 &&
            ($oldFilesCount + $newFilesCount) > $options['maxcount']
        ) {
            return Jaws_Error::raiseError(
                Jaws::t('ERROR_UPLOAD_9'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

        $resultFiles = array();
        //FIXME: need improvement for multi files delete
        if (empty($remainFiles)) {
            $filesModel->deleteFiles($interface);
        } else {
            foreach ($oldFiles as $file) {
                if (!in_array($file['id'], $remainFiles)) {
                    $filesModel->deleteFiles(
                        $interface,
                        $file['id']
                    );
                } else {
                    $resultFiles[] = $file;
                }
            }
        }

        if (array_key_exists($newFilesIndex, $_FILES)) {
            $newFiles = $this->gadget->fileManagement::uploadFiles(
                $_FILES[$newFilesIndex],
                ROOT_DATA_PATH. strtolower('files/'. $interface['gadget']. '/'. $interface['action']),
                $options['extensions'],
                null,
                true,
                $options['maxsize'],
                $options['dimension'],
                $options['imageformat']
            );

            if (Jaws_Error::IsError($newFiles)) {
                return $newFiles;
            }

            if (!empty($newFiles)) {
                $result = $filesModel->insertFiles(
                    $interface,
                    $newFiles[0]
                );
                if (!Jaws_Error::IsError($result)) {
                    call_user_func_array('array_push', array_merge(array(&$resultFiles), $result));
                }
            }
        }

        return $resultFiles;
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
            $filePath = strtolower('files/'. $file['gadget']. '/'. $file['action']. '/');
            if ($this->gadget->fileManagement::file_exists(ROOT_DATA_PATH. $filePath . $file['filename'])) {
                // increase file hits
                $this->gadget->model->load('Files')->hitDownload($file['id']);
                // download
                if ($this->gadget->fileManagement::download(
                    ROOT_DATA_PATH. $filePath . $file['filename'],
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