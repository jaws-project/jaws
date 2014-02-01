<?php
/**
 * PrivateMessage Gadget
 *
 * @category    Gadget
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PrivateMessage_Actions_Attachment extends Jaws_Gadget_Action
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
            return Jaws_HTTPError::Get(403);
        }

        $rqst = jaws()->request->fetch(array('uid', 'mid', 'aid'), 'get');
        $user = $GLOBALS['app']->Session->GetAttribute('user');

        $mModel = $this->gadget->model->load('Message');
        $aModel = $this->gadget->model->load('Attachment');
        $message = $mModel->GetMessage($rqst['mid'], false, false);
        if (Jaws_Error::IsError($message)) {
            return Jaws_HTTPError::Get(500);
        }

        // Check permissions
        if ((!($message['from'] == $user && $message['to'] == 0) && $message['to'] != $user) ||
            $user != $rqst['uid']
        ) {
            return Jaws_HTTPError::Get(403);
        }

        $attachment = $aModel->GetAttachment($rqst['aid'], $rqst['mid']);
        if (!empty($attachment)) {
            $filepath = JAWS_DATA . 'pm' . DIRECTORY_SEPARATOR . 'attachments' . DIRECTORY_SEPARATOR . $attachment['filename'];
            if (file_exists($filepath)) {
                if (Jaws_Utils::Download($filepath, $attachment['title'], $attachment['filetype'])) {
                    return;
                }
                return Jaws_HTTPError::Get(500);
            }
        }

        $this->SetActionMode('Attachment', 'normal', 'standalone');
        return Jaws_HTTPError::Get(404);
    }

    /**
     * Uploads attachment file
     *
     * @access  public
     * @return  string  javascript script segment
     */
    function UploadFile()
    {
        $file_num = jaws()->request->fetch('attachment_number', 'post');

        $file = Jaws_Utils::UploadFiles($_FILES, Jaws_Utils::upload_tmp_dir(), '', null);
        if (Jaws_Error::IsError($file)) {
            $response = array('type'    => 'error',
                'message' => $file->getMessage());
        } else {
            $response = array('type' => 'notice', 'file_info' => array(
                'title' => $file['attachment' . $file_num][0]['user_filename'],
                'filename' => $file['attachment' . $file_num][0]['host_filename'],
                'filesize_format' =>  Jaws_Utils::FormatSize($file['attachment' . $file_num][0]['host_filesize']),
                'filesize' => $file['attachment' . $file_num][0]['host_filesize'],
                'filetype' => $file['attachment' . $file_num][0]['host_filetype']));
        }

        $response = Jaws_UTF8::json_encode($response);
        return "<script type='text/javascript'>parent.onUpload($response);</script>";
    }

}