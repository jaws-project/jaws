<?php
/**
 * Comments Gadget
 *
 * @category   Gadget
 * @package    Comments
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Comments_Actions_Comments extends Comments_HTML
{
    /**
     * Get Comments action params
     *
     * @access  public
     * @return  array list of RecentComments action params
     */
    function CommentsLayoutParams()
    {
        $result = array();

        $result[] = array(
            'title' => _t('COMMENTS_COMMENTS_PER_PAGE'),
            'value' => $this->gadget->GetRegistry('comments_per_page')
        );

        $result[] = array(
            'title' => _t('GLOBAL_ORDERBY'),
            'value' => array(
                1 => _t('GLOBAL_CREATETIME'). ' &uarr;',
                0 => _t('GLOBAL_CREATETIME'). ' &darr;',
            )
        );

        return $result;
    }

    /**
     * Displays a block of pages belongs to the specified group
     *
     * @access  public
     * @param   int    $perPage
     * @param   int    $orderBy
     * @internal param string $gadget
     * @internal param mixed $limit limit recent comments (int)
     * @return  string  XHTML content
     */
    function Comments($perPage = 0, $orderBy = 0)
    {
        $tpl = new Jaws_Template('gadgets/Comments/templates/');
        $tpl->Load('Comments.html');
        $tpl->SetBlock('new_comment');
        $tpl->SetVariable('title', _t('COMMENTS_COMMENTS'));

        $allow_comments_config = $this->gadget->GetRegistry('allow_comments', 'Settings');
        switch ($allow_comments_config) {
            case 'restricted':
                $allow_comments_config = $GLOBALS['app']->Session->Logged();
                break;

            default:
                $allow_comments_config = $allow_comments_config == 'true';
        }

        if ($allow_comments_config) {
            $tpl->SetBlock('new_comment/fieldset');
            $tpl->SetVariable('base_script', BASE_SCRIPT);
            $tpl->SetVariable('message', _t('COMMENTS_MESSAGE'));
            $tpl->SetVariable('send', _t('COMMENTS_SEND'));

            $name  = $GLOBALS['app']->Session->GetCookie('visitor_name');
            $email = $GLOBALS['app']->Session->GetCookie('visitor_email');
            $url   = $GLOBALS['app']->Session->GetCookie('visitor_url');

            $rand = rand();
            $tpl->SetVariable('rand', $rand);
            if (!$GLOBALS['app']->Session->Logged()) {
                $tpl->SetBlock('new_comment/fieldset/info-box');
                $url_value = empty($url)? 'http://' : Jaws_XSS::filter($url);
                $tpl->SetVariable('url', _t('GLOBAL_URL'));
                $tpl->SetVariable('urlvalue', $url_value);
                $tpl->SetVariable('rand', $rand);
                $tpl->SetVariable('name', _t('GLOBAL_NAME'));
                $tpl->SetVariable('namevalue', isset($name) ? Jaws_XSS::filter($name) : '');
                $tpl->SetVariable('email', _t('GLOBAL_EMAIL'));
                $tpl->SetVariable('emailvalue', isset($email) ? Jaws_XSS::filter($email) : '');
                $tpl->ParseBlock('new_comment/fieldset/info-box');
            }

            $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
            if ($mPolicy->LoadCaptcha($captcha, $entry, $label, $description)) {
                $tpl->SetBlock('new_comment/fieldset/captcha');
                $tpl->SetVariable('lbl_captcha', $label);
                $tpl->SetVariable('captcha', $captcha);
                if (!empty($entry)) {
                    $tpl->SetVariable('captchavalue', $entry);
                }
                $tpl->SetVariable('captcha_msg', $description);
                $tpl->ParseBlock('new_comment/fieldset/captcha');
            }

            $tpl->ParseBlock('new_comment/fieldset');
        } else {
            $tpl->SetBlock('new_comment/unregistered');
            $tpl->SetVariable('msg', _t('GLOBAL_ERROR_ACCESS_RESTRICTED',
                $GLOBALS['app']->Map->GetURLFor('Users', 'LoginBox'),
                $GLOBALS['app']->Map->GetURLFor('Users', 'Registration')));
            $tpl->ParseBlock('new_comment/unregistered');
        }

        if ($response = $GLOBALS['app']->Session->PopSimpleResponse('Comments')) {
            $tpl->SetBlock('new_comment/response');
            $tpl->SetVariable('msg', $response);
            $tpl->ParseBlock('new_comment/response');
        }

        $tpl->SetVariable('comments_messages', $this->GetMessages($perPage, $orderBy));
        $tpl->ParseBlock('new_comment');

        return $tpl->Get();
    }

    /**
     * Get the comments messages list
     *
     * @access  public
     * @param   int    $perPage
     * @param   int    $orderBy
     * @return  string  XHTML template content
     */
    function GetMessages($perPage, $orderBy)
    {
        $request =& Jaws_Request::getInstance();
        $rqst = $request->get(array('order','perpage', 'page'), 'get');
        $page = empty($rqst['page'])? 1 : (int)$rqst['page'];
        if($perPage==0 && $orderBy==0) {
            $perPage = (int)$rqst['perpage'];
            $orderBy = (int)$rqst['order'];
        }

        $model = $GLOBALS['app']->LoadGadget('Comments', 'Model');
        $comments = $model->GetComments('comments', $perPage, null, null, array(COMMENT_STATUS_APPROVED), false,
                                              ($page - 1) * $perPage, $orderBy);
        $comments_count = $model->HowManyFilteredComments('comments', '', '', 1);

        $tpl = new Jaws_Template('gadgets/Comments/templates/');
        $tpl->Load('Comments.html');
        $tpl->SetBlock('comments');
        if (!Jaws_Error::IsError($comments) && $comments != null) {
            $date = $GLOBALS['app']->loadDate();
            foreach ($comments as $entry) {
                $tpl->SetBlock('comments/entry');
                $tpl->SetVariable('name', Jaws_XSS::filter($entry['name']));
                $tpl->SetVariable('email', Jaws_XSS::filter($entry['email']));
                $tpl->SetVariable('url', Jaws_XSS::filter($entry['url']));
                $tpl->SetVariable('updatetime', $date->Format($entry['createtime']));
                $tpl->SetVariable('message', Jaws_String::AutoParagraph($entry['msg_txt']));
                $tpl->ParseBlock('comments/entry');
            }
        }

        // page navigation
        $this->GetPagesNavigation(
            $tpl,
            'comments',
            $page,
            $perPage,
            $comments_count,
            _t('COMMENTS_COMMENTS_COUNT', $comments_count),
            'Comments',
            array('perpage'=>$perPage,
                  'order'=>$orderBy )
        );

        $tpl->ParseBlock('comments');
        return $tpl->Get();
    }

    /**
     * Adds a new entry to the comments, sets cookie with user data and redirects to main page
     *
     * @access  public
     * @return  void
     */
    function PostMessage()
    {
        $request =& Jaws_Request::getInstance();
        $post  = $request->get(array('message', 'name', 'email', 'url'), 'post');
        $model = $GLOBALS['app']->LoadGadget('Comments', 'Model');

        if ($GLOBALS['app']->Session->Logged()) {
            $post['name']  = $GLOBALS['app']->Session->GetAttribute('nickname');
            $post['email'] = $GLOBALS['app']->Session->GetAttribute('email');
            $post['url']   = $GLOBALS['app']->Session->GetAttribute('url');
        }

        if (trim($post['message']) == ''|| trim($post['name']) == '') {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('COMMENTS_DONT_SEND_EMPTY_MESSAGES'), 'Comments');
            Jaws_Header::Referrer();
        }

        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        $resCheck = $mPolicy->CheckCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $GLOBALS['app']->Session->PushSimpleResponse($resCheck->getMessage(), 'Comments');
            Jaws_Header::Referrer();
        }

        $permalink = $GLOBALS['app']->GetSiteURL();
        $status = $this->gadget->GetRegistry('default_comment_status');
        if ($GLOBALS['app']->Session->GetPermission('Comments', 'ManageComments')) {
            $status = COMMENT_STATUS_APPROVED;
        }

        $res = $model->NewComment(
            'comments', 0, '', $post['name'], $post['email'],
            $post['url'], $post['message'], $_SERVER['REMOTE_ADDR'], $permalink, $status
        );


        if (Jaws_Error::isError($res)) {
            $GLOBALS['app']->Session->PushSimpleResponse($res->getMessage(), 'Comments');
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('GLOBAL_MESSAGE_SENT'), 'Comments');
        }

        Jaws_Header::Referrer();
    }

}