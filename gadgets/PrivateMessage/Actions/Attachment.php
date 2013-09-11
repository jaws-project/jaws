<?php
/**
 * PrivateMessage Gadget
 *
 * @category    Gadget
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class PrivateMessage_Actions_Attachment extends Jaws_Gadget_HTML
{
    /**
     * Download message attachment
     *
     * @access  public
     * @return  string   Requested file content or HTML error page
     */
    function Attachment()
    {
        if (!$GLOBALS['app']->Session->Logged()) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(403);
        }

        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        $rqst = jaws()->request->fetch(array('uid', 'mid', 'aid'), 'get');

        $mModel = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $aModel = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Attachment');
        $message = $mModel->GetMessage($rqst['mid']);
        if (Jaws_Error::IsError($message)) {
            return Jaws_HTTPError::Get(500);
        }

        if ($message['user'] != $rqst['uid']) {
            return Jaws_HTTPError::Get(500);
        }

        $attachment = $aModel->GetMessageAttachment($rqst['aid']);
        if (!empty($attachment) && ($attachment['message'] == $rqst['mid'])) {
            $filepath = JAWS_DATA . 'pm' . DIRECTORY_SEPARATOR . $rqst['uid'] . DIRECTORY_SEPARATOR .
                $attachment['filename'];
            if (file_exists($filepath)) {
                if (Jaws_Utils::Download($filepath, $attachment['title'])) {
                    return;
                }

                return Jaws_HTTPError::Get(500);
            }
        }

        $this->SetActionMode('Attachment', 'normal', 'standalone');
        return Jaws_HTTPError::Get(404);
    }

}