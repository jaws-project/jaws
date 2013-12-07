<?php
/**
 * Blog Gadget
 *
 * @category   GadgetModel
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Model_Comments extends Jaws_Gadget_Model
{

    /**
     * Get a list of comments
     *
     * @access  public
     * @param   int     $id         ID of the comment
     * @return  mixed   Returns a list of comments and Jaws_Error on error
     */
    function GetComments($id)
    {
        $cModel = Jaws_Gadget::getInstance('Comments')->model->load('Comments');
        $comments = $cModel->GetComments($this->gadget->name, 0, $id, 'entry', array(1), true);
        if (Jaws_Error::IsError($comments)) {
            return new Jaws_Error(_t('BLOG_ERROR_GETTING_COMMENTS'));
        }

        $this->_AdditionalCommentsData($comments);
        return $comments;
    }

    /**
     * Puts avatar and format time for given comments
     *
     * @access  private
     * @param   array   $comments   reference to comments array
     * @param   string  $prenum
     */
    function _AdditionalCommentsData(&$comments, $prenum = '')
    {
        $num = 0;
        foreach ($comments as $k => $v) {
            $num++;
            $comments[$k]['avatar_source'] = Jaws_Gravatar::GetGravatar($v['email']);
            $comments[$k]['createtime']    = $v['createtime'];
            $comments[$k]['num'] = $prenum.$num;
        }
    }

    /**
     * Get a list of comments
     *
     * @access  public
     * @param   string  $filterby Filter to use(postid, author, email, url, title, comment)
     * @param   string  $filter   Filter data
     * @param   string  $status   Spam status (approved, waiting, spam)
     * @param   mixed   $limit    Data limit (numeric/boolean)
     * @return  mixed   Returns a list of comments and Jaws_Error on error
     */
    function GetCommentsFiltered($filterby, $filter, $status, $limit)
    {
        $cModel = Jaws_Gadget::getInstance('Comments')->model->load('Comments');
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
            case 'status':
                $filterMode = COMMENT_FILTERBY_STATUS;
                break;
            default:
                $filterMode = null;
                break;
        }

        $comments = $cModel->GetFilteredComments($this->gadget->name, $filterMode, $filter, $status, $limit);
        if (Jaws_Error::IsError($comments)) {
            return new Jaws_Error(_t('BLOG_ERROR_GETTING_FILTERED_COMMENTS'));
        }

        $commentsGravatar = array();
        foreach ($comments as $r) {
            $r['avatar_source'] = Jaws_Gravatar::GetGravatar($r['email']);
            $r['createtime']    = $r['createtime'];
            $commentsGravatar[] = $r;
        }

        return $commentsGravatar;
    }

    /**
     * Get a comment
     *
     * @access  public
     * @param   int     $id     ID of the comment
     * @return  mixed   Properties of a comment and Jaws_Error on error
     */
    function GetComment($id)
    {
        $cModel = Jaws_Gadget::getInstance('Comments')->model->load('Comments');
        $comment = $cModel->GetComment($id, $this->gadget->name);
        if (Jaws_Error::IsError($comment)) {
            return new Jaws_Error(_t('BLOG_ERROR_GETTING_COMMENT'));
        }

        if ($comment) {
            $comment['avatar_source'] = Jaws_Gravatar::GetGravatar($comment['email']);
            $comment['createtime']    = $comment['createtime'];
            $comment['comments']      = $comment['msg_txt'];
        }

        return $comment;
    }

    /**
     * This function mails the comments to the admin and
     * to the user when he asks for it.
     *
     * @access  public
     * @param   int     $id            The blog id.
     * @param   string  $title      The email title
     * @param   string  $from_email The email to sendto
     * @param   string  $comment    The body of the email (The actual comment)
     * @param   string  $url        The url of the blog id.
     */
    function MailComment($id, $title, $from_email, $comment, $url)
    {
        $usersTable = Jaws_ORM::getInstance()->table('users');
        $usersTable->select('users.email')->join('blog', 'users.id', 'blog.user_id')->where('blog.id', $id);
        $author_email = $usersTable->fetchOne();
        if (Jaws_Error::IsError($author_email)) {
            $author_email = '';
        }

        $site_url   = $GLOBALS['app']->getSiteURL('/');
        $site_name  = $this->gadget->registry->fetch('site_name', 'Settings');

        $tpl = $this->gadget->template->load('SendComment.html');
        $tpl->SetBlock('comment');
        $tpl->SetVariable('comment',   $comment);
        $tpl->SetVariable('lbl-url',   _t("BLOG_COMMENT_MAIL_VISIT"));
        $entry_url =& Piwi::CreateWidget(
            'Link',
            $title,
            $this->gadget->urlMap('SingleView', array('id' => $id), true)
        );
        $tpl->SetVariable('url',       $entry_url->Get());
        $tpl->SetVariable('site-name', $site_name);
        $tpl->SetVariable('site-url',  $site_url);
        $tpl->ParseBlock('comment');
        $template = $tpl->Get();

        $mail = new Jaws_Mail;
        $subject = _t('BLOG_COMMENT_REPLY', $id). ' - ' . $title;
        $mail->SetFrom($from_email);
        $mail->AddRecipient($author_email);
        $mail->AddRecipient('', 'cc');
        $mail->SetSubject($subject);
        $mail->SetBody($template, 'html');
        $result = $mail->send();
    }
}