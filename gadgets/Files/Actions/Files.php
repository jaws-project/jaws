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
                        'File',
                        array('id' => $file['id'])
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
            'extensions'  => '',
            'preview'     => true,
        );
        $options = array_merge($defaultOptions, $options);

        $this->AjaxMe('index.js');
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/files");

        $tpl->SetVariable('lbl_file',$options['labels']['title']);
        $tpl->SetVariable('lbl_extra_file', $options['labels']['extra']);
        $tpl->SetVariable('lbl_remove_file', $options['labels']['remove']);
        $tpl->SetVariable('maxsize', $options['maxsize']);
        $tpl->SetVariable('extensions', $options['extensions']);
        $tpl->SetVariable('preview', $options['preview']);

        if (!empty($interface['reference'])) {
            $files = $this->gadget->model->load('Files')->getFiles($interface);
            foreach ($files as $file) {
                $tpl->SetBlock("$block/files/file");
                $tpl->SetVariable('fid', $file['id']);
                $tpl->SetVariable('lbl_filename', $file['title']);
                $tpl->SetVariable('lbl_remove_file', $options['labels']['remove']);
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
     * @return  mixed   TRUE otherwise Jaws_Error on error
     */
    function uploadReferenceFiles($interface)
    {
        $filesModel = $this->gadget->model->load('Files');
        $oldFiles = $filesModel->getFiles($interface);

        //FIXME: need improvement for multi files delete
        $remainFiles = $this->app->request->fetch('current_files:array');
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

        $newFiles = Jaws_Utils::UploadFiles(
            $_FILES,
            ROOT_DATA_PATH. 'files',
            '',
            null
        );

        if (Jaws_Error::IsError($newFiles)) {
            return $newFiles;
        }

        if (!empty($newFiles)) {
            return $filesModel->insertFiles(
                $interface,
                $newFiles['files']
            );
        }

        return true;
    }
}