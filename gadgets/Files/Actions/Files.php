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
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   int     $reference  Reference ID
     * @param   int     $type       Category type of files
     * @param   int     $user       User owner of tag(0: for global tags)
     * @return  void
     */
    function displayReferenceFiles(&$tpl, $gadget, $action, $reference, $type = 0, $user = 0)
    {
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/files");

        if (!empty($reference)) {
            $files = $this->gadget->model->load('Files')->getFiles(
                array(
                    'gadget'    => $gadget,
                    'action'    => $action,
                    'reference' => $reference,
                    'type'      => $type
                )
            );

            foreach ($files as $file) {
                $tpl->SetBlock("$block/files/file");
                $tpl->SetVariable('title', $file['title']);
                $tpl->SetVariable('postname', $file['postname']);
                $tpl->SetVariable('filehits', $file['filehits']);
                $tpl->SetVariable('lbl_file', _t('FORUMS_POSTS_ATTACHMENT'));
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
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   int     $reference  Reference ID
     * @param   int     $type       Category type of files
     * @param   int     $user       User owner of tag(0: for global tags)
     * @return  void
     */
    function loadReferenceFiles(&$tpl, $gadget, $action, $reference, $type = 0, $user = 0)
    {
        $this->AjaxMe('index.js');
        $block = $tpl->GetCurrentBlockPath();
        $tpl->SetBlock("$block/files");

        $tpl->SetVariable('lbl_file',_t('FORUMS_POSTS_ATTACHMENT'));
        $tpl->SetVariable('lbl_extra_file', _t('FORUMS_POSTS_EXTRA_ATTACHMENT'));
        $tpl->SetVariable('lbl_remove_file',_t('FORUMS_POSTS_ATTACHMENT_REMOVE'));
            
        if (!empty($reference)) {
            $files = $this->gadget->model->load('Files')->getFiles(
                array(
                    'gadget'    => $gadget,
                    'action'    => $action,
                    'reference' => $reference,
                    'type'      => $type
                )
            );

            foreach ($files as $file) {
                $tpl->SetBlock("$block/files/file");
                $tpl->SetVariable('fid', $file['id']);
                $tpl->SetVariable('lbl_filename', $file['title']);
                $tpl->SetVariable('lbl_remove_file', _t('FORUMS_POSTS_ATTACHMENT_REMOVE'));
                $tpl->ParseBlock("$block/files/file");
            }
        }

        $tpl->ParseBlock("$block/files");
    }

    /**
     * Upload/Insert/Update reference files
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   int     $reference  Reference ID
     * @param   int     $type       Category type of files
     * @param   int     $user       User owner of tag(0: for global tags)
     * @return  mixed   TRUE otherwise Jaws_Error on error
     */
    function uploadReferenceFiles($gadget, $action, $reference, $type = 0, $user = 0)
    {
        $filesModel = $this->gadget->model->load('Files');
        $oldFiles = $filesModel->getFiles(
            array(
                'gadget'    => $gadget,
                'action'    => $action,
                'reference' => $reference,
                'type'      => $type
            )
        );

        //FIXME: need improvement for multi files delete
        $remainFiles = $this->app->request->fetch('current_files:array');
        if (empty($remainFiles)) {
            $filesModel->deleteFiles(
                array(
                    'gadget'    => $gadget,
                    'action'    => $action,
                    'reference' => $reference,
                    'type'      => $type
                )
            );
        } else {
            foreach ($oldFiles as $file) {
                if (!in_array($file['id'], $remainFiles)) {
                    $filesModel->deleteFiles(
                        array(
                            'gadget'    => $gadget,
                            'action'    => $action,
                            'reference' => $reference,
                            'type'      => $type
                        ),
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
                array(
                    'gadget'    => $gadget,
                    'action'    => $action,
                    'reference' => $reference,
                    'type'      => $type
                ),
                $newFiles['files']
            );
        }

        return true;
    }
}