<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetModel
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Model_Comments extends Jaws_Gadget_Model
{

    /**
     * Get a list of comments
     *
     * @access  public
     * @param   int     $id     ID of the comment
     * @return  mixed   A list of comments and Jaws_Error on error
     */
    function GetComments($id)
    {
        $cModel = Jaws_Gadget::getInstance('Comments')->loadModel('Comments');
        $comments = $cModel->GetComments($this->gadget->name, 0, $id, 'Image', array(1), true);
        if (Jaws_Error::IsError($comments)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETCOMMENTS'), _t('PHOO_NAME'));
        }

        $this->_AdditionalCommentsData($comments);
        return $comments;
    }

    /**
     * Puts avatar and format time for given comments
     *
     * @access  private
     * @param   array   $comments   comments array reference
     */
    function _AdditionalCommentsData(&$comments)
    {
        foreach ($comments as $k => $v) {
            $comments[$k]['avatar_source'] = Jaws_Gravatar::GetGravatar($v['email']);
            $comments[$k]['createtime']    = $v['createtime'];
        }
    }

    /**
     * Get a list of comments
     *
     * @access  public
     * @param   string  $filterby   Filter to use(postid, author, email, url, title, comment)
     * @param   string  $filter     Filter data
     * @return  mixed   Returns a list of comments and Jaws_Error on error
     */
    function GetCommentsFiltered($filterby, $filter)
    {
        $cModel = Jaws_Gadget::getInstance('Comments')->loadModel('Comments');
        $filterMode = '';
        switch($filterby) {
            case 'postid':
                $filterMode = COMMENT_FILTERBY_REFERENCE;
                break;
            case 'name':
                $filterMode = COMMENT_FILTERBY_NAME;
                break;
            case 'email':
                $filterMode = COMMENT_FILTERBY_EMAIL;
                break;
            case 'url':
                $filterMode = COMMENT_FILTERBY_URL;
                break;
            case 'title':
                $filterMode = COMMENT_FILTERBY_TITLE;
                break;
            case 'ip':
                $filterMode = COMMENT_FILTERBY_IP;
                break;
            case 'comment':
                $filterMode = COMMENT_FILTERBY_MESSAGE;
                break;
            case 'various':
                $filterMode = COMMENT_FILTERBY_VARIOUS;
                break;
            default:
                $filterMode = null;
                break;
        }

        $comments = $cModel->GetFilteredComments($this->gadget->name, $filterMode, $filter);
        if (Jaws_Error::IsError($comments)) {
            return new Jaws_Error(_t('PHOO_ERROR_FILETEREDCOMMENTS'), _t('PHOO_NAME'));
        }

        $commentsGravatar = array();
        foreach ($comments as $r) {
            $r['avatar_source'] = Jaws_Gravatar::GetGravatar($r['email']);
            $r['createtime'] = $r['createtime'];
            $commentsGravatar[] = $r;
        }

        return $commentsGravatar;
    }

    /**
     * Get a comment
     *
     * @access  public
     * @param   int     $id  ID of the comment
     * @return  mixed   Properties of a comment and Jaws_Error on error
     */
    function GetComment($id)
    {
        $cModel = Jaws_Gadget::getInstance('Comments')->loadModel('Comments');
        $comment = $cModel->GetComment($id, $this->gadget->name);
        if (Jaws_Error::IsError($comment)) {
            return new Jaws_Error(_t('PHOO_ERROR_GETCOMMENT'), _t('PHOO_NAME'));
        }

        if ($comment) {
            $comment['avatar_source'] = Jaws_Gravatar::GetGravatar($comment['email']);
            $comment['createtime']    = $comment['createtime'];
            $comment['comments']      = $comment['msg_txt'];
        }

        return $comment;
    }

    /**
     * This function mails the comments to the owner
     *
     * @access  public
     * @param   int    $link       The permanent link
     * @param   string $title      The email title
     * @param   string $from_email The email to sendto
     * @param   string $comment    The body of the email (The actual comment)
     * @param   string $url        The url of the blog id
     */
    function MailComment($link, $title, $from_email, $comment, $url)
    {
        $subject   = $title;
        $comment .= "<br /><br />";
        $comment .= _t("PHOO_COMMENT_MAIL_VISIT_URL", $GLOBALS['app']->getSiteURL('/'). $link, $title);

        $mail = new Jaws_Mail;
        $mail->SetFrom($from_email);
        $mail->AddRecipient('');
        $mail->SetSubject($subject);
        $mail->SetBody($comment, 'html');
        $result = $mail->send();
    }
}