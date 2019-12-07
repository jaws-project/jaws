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
     * Get upload reference files interface
     *
     * @access  public
     * @param   object  $tpl        Jaws_Template object
     * @param   array   $interface  Gadget interface(gadget, action, reference, ...)
     * @param   array   $options    User interface control options(maxsize, types, labels, ...)
     * @return  void
     */
    function displayReferenceFiles(&$tpl, $interface, $options = array())
    {
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
                    'url_file',
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
     * Get upload reference files interface
     *
     * @access  public
     * @param   object  $tpl        Jaws_Template object
     * @param   array   $interface  Gadget interface(gadget, action, reference, ...)
     * @param   array   $options    User interface control options(maxsize, types, labels, ...)
     * @return  void
     */
    function loadReferenceFiles(&$tpl, $interface, $options = array())
    {
        // FIXME:: add registry key for set maximum upload file size
        $defaultOptions = array(
            'maxsize'     => 33554432, // 32MB
            'maxcount'    => 0,        // unlimited
            'filetype'    => 0,
            'extensions'  => '',
            'preview'     => true,
            'capture'     => false,
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
        $interface['input_reference'] = (@$interface['input_reference'])?: $interface['reference'];

        $this->AjaxMe('index.js');
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/files");

        $tpl->SetVariable('lbl_file',$options['labels']['title']);
        $tpl->SetVariable('lbl_browse', $options['labels']['browse']);
        $tpl->SetVariable('lbl_remove', $options['labels']['remove']);
        $tpl->SetVariable('input_action', strtolower($interface['action']));
        $tpl->SetVariable('input_reference', strtolower($interface['input_reference']));
        $tpl->SetVariable('input_type', $interface['type']);
        $tpl->SetVariable('maxsize',  $options['maxsize']);
        $tpl->SetVariable('maxcount', $options['maxcount']);
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
                if ($options['preview']) {
                    switch (substr($file['mimetype'], 0, strpos($file['mimetype'], '/'))) {
                        case 'image':
                            $tpl->SetBlock("$block/files/file/image_preview");
                            $tpl->SetVariable('src', $this->app->getDataURL($filesPath. $file['filename']));
                            $tpl->ParseBlock("$block/files/file/image_preview");
                            break;
                    }
                }
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
            'extensions'  => '',
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
        $interface['input_reference'] = (@$interface['input_reference'])?: $interface['reference'];

        $filesModel = $this->gadget->model->load('Files');
        $oldFiles = $filesModel->getFiles($interface);

        $remainFiles = $this->app->request->fetch('old_files:array');
        $oldFilesCount = empty($remainFiles)? 0 : count($remainFiles);

        $newFilesCount = 0;
        $uploadFilesIndex = strtolower(
            'files_'. $interface['action'] . '_'. $interface['input_reference']. '_'. $interface['type']
        );
        if (array_key_exists($uploadFilesIndex, $_FILES)) {
            $newFilesCount = count($_FILES[$uploadFilesIndex]['name']);
        }
        // check max count of files
        if ($options['maxcount'] > 0 &&
            ($oldFilesCount + $newFilesCount) > $options['maxcount']
        ) {
            return Jaws_Error::raiseError(
                _t('GLOBAL_ERROR_UPLOAD_9'),
                __FUNCTION__,
                JAWS_ERROR_NOTICE
            );
        }

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
                }
            }
        }

        if (array_key_exists($uploadFilesIndex, $_FILES)) {
            $newFiles = Jaws_Utils::UploadFiles(
                $_FILES[$uploadFilesIndex],
                ROOT_DATA_PATH. strtolower('files/'. $interface['gadget']. '/'. $interface['action']),
                $options['extensions'],
                null,
                true,
                $options['maxsize']
            );

            if (Jaws_Error::IsError($newFiles)) {
                return $newFiles;
            }

            if (!empty($newFiles)) {
                return $filesModel->insertFiles(
                    $interface,
                    $newFiles[0]
                );
            }
        }

        return true;
    }

    /**
     * Download/View file
     *
     * @access  public
     * @param   array   $interface  Gadget interface(gadget, action, reference, ...)
     * @param   array   $options    User interface control options(maxsize, maxcount, extensions)
     * @return  mixed   TRUE otherwise Jaws_Error on error
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
            if (file_exists(ROOT_DATA_PATH. $filePath . $file['filename'])) {
                // increase file hits
                $this->gadget->model->load('Files')->hitDownload($file['id']);
                // download
                if (Jaws_Utils::Download(
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