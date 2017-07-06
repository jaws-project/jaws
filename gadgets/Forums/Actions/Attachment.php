<?php
/**
 * Forums Gadget
 *
 * @category    Gadget
 * @package     Forums
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright   2012-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Actions_Attachment extends Jaws_Gadget_Action
{
    /**
     * Download post attachment
     *
     * @access  public
     * @return  string   Requested file content or HTML error page
     */
    function Attachment()
    {
        $rqst = $this->gadget->request->fetch(array('fid', 'tid', 'pid', 'attach'), 'get');
        $pModel = $this->gadget->model->load('Posts');
        $post = $pModel->GetPost($rqst['pid'], $rqst['tid'], $rqst['fid']);
        if (Jaws_Error::IsError($post)) {
            $this->SetActionMode('Attachment', 'normal', 'standalone');
            return Jaws_HTTPError::Get(500);
        }
        $aModel = $this->gadget->model->load('Attachments');
        $attachment = $aModel->GetAttachmentInfo($rqst['attach']);
        if (Jaws_Error::IsError($attachment)) {
            $this->SetActionMode('Attachment', 'normal', 'standalone');
            return Jaws_HTTPError::Get(500);
        }

        if (!empty($attachment)) {
            $filepath = JAWS_DATA. 'forums/'. $attachment['filename'];
            if (file_exists($filepath)) {
                // increase download hits
                $result = $aModel->HitAttachmentDownload($rqst['attach']);
                if (Jaws_Error::IsError($result)) {
                    // do nothing
                }

                if (Jaws_Utils::Download($filepath, $attachment['title'])) {
                    return;
                }

                $this->SetActionMode('Attachment', 'normal', 'standalone');
                return Jaws_HTTPError::Get(500);
            }
        }

        $this->SetActionMode('Attachment', 'normal', 'standalone');
        return Jaws_HTTPError::Get(404);
    }

}