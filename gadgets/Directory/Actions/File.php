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
    function UploadFile()
    {
        $tpl = $this->gadget->template->load('UploadFile.html');
        $tpl->SetBlock('uploadUI');

        $this->SetTitle(_t('DIRECTORY_UPLOAD_FILE'));
        $tpl->SetVariable('title', _t('DIRECTORY_UPLOAD_FILE'));

        $tpl->ParseBlock('uploadUI');
        return $tpl->Get();
    }
}