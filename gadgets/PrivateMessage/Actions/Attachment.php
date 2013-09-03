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
            Jaws_Header::Location(
                $this->gadget->urlMap(
                    'LoginBox',
                    array('referrer'  => bin2hex(Jaws_Utils::getRequestURL(true)))
                )
            );
        }

        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        $rqst = jaws()->request->get(array('uid', 'mid', 'aid'), 'get');

        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $message = $model->GetMessage($rqst['mid']);
        if (Jaws_Error::IsError($message)) {
            return Jaws_HTTPError::Get(500);
        }

        if ($message['from'] != $rqst['uid']) {
            return Jaws_HTTPError::Get(500);
        }

        $attachment = $model->GetMessageAttachment($rqst['aid']);
        if (!empty($attachment) && ($attachment['message_id'] == $rqst['mid'])) {
            $filepath = JAWS_DATA . 'pm' . DIRECTORY_SEPARATOR . $rqst['uid'] . DIRECTORY_SEPARATOR .
                $attachment['host_filename'];
            if (file_exists($filepath)) {
                // increase download hits
                $model->HitAttachmentDownload($rqst['aid']);

                if (Jaws_Utils::Download($filepath, $attachment['user_filename'])) {
                    return;
                }

                return Jaws_HTTPError::Get(500);
            }
        }

        $this->SetActionMode('Attachment', 'normal', 'standalone');
        return Jaws_HTTPError::Get(404);
    }

}