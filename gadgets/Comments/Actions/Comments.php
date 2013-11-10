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
class Comments_Actions_Comments extends Comments_Actions_Default
{

    /**
     * Displays GuestBook
     *
     * @access  public
     * @param   bool    $preview_mode  Show preview section?
     * @return  string  XHTML content
     */
    function Comments($preview_mode = false)
    {
        $tpl = $this->gadget->template->load('Guestbook.html');
        $tpl->SetBlock('guestbook');
        $tpl->SetVariable('title', _t('COMMENTS_COMMENTS'));

        $tpl->SetVariable('comments', $this->ShowComments('Comments', 'Guestbook', 0, array('action' => 'Comments')));
        if ($preview_mode) {
            $tpl->SetVariable('preview', $this->ShowPreview());
        }

        $redirect_to = $this->gadget->urlMap('Comments');
        $tpl->SetVariable('comment-form', $this->ShowCommentsForm('Comments', 'Guestbook', 0, $redirect_to));

        $tpl->ParseBlock('guestbook');
        return $tpl->Get();
    }


    /**
     * Displays a block of pages belongs to the specified group
     *
     * @access  public
     * @param   string  $gadget
     * @param   string  $action
     * @param   int     $reference
     * @param   string  $redirect_to
     * @return  string  XHTML content
     */
    function ShowCommentsForm($gadget, $action, $reference, $redirect_to)
    {
        $tpl = $this->gadget->template->load('CommentForm.html');
        $tpl->SetBlock('comment_form');
        $tpl->SetVariable('title', _t('COMMENTS_COMMENTS'));

        // check for posting value
        $post = jaws()->request->fetch(array('name', 'email', 'url', 'title', 'message'), 'post');
        if(isset($post['message'])) {
            $tpl->SetVariable('message', $post['message']);
        }


        $tpl->SetVariable('gadget', $gadget);
        $tpl->SetVariable('action', $action);
        $tpl->SetVariable('reference', $reference);
        $tpl->SetVariable('redirect_to', $redirect_to. '#'. $gadget. '_'. $action);
        $tpl->SetVariable('private', _t('COMMENTS_PRIVATE'));

        $allow_comments_config = $this->gadget->registry->fetch('allow_comments', 'Comments');
        switch ($allow_comments_config) {
            case 'restricted':
                $allow_comments_config = $GLOBALS['app']->Session->Logged();
                break;

            default:
                $allow_comments_config = $allow_comments_config == 'true';
        }

        if ($allow_comments_config) {
            $tpl->SetVariable('base_script', BASE_SCRIPT);
            $tpl->SetVariable('lbl_message', _t('COMMENTS_MESSAGE'));
            $tpl->SetVariable('send', _t('COMMENTS_SEND'));

            $name  = $GLOBALS['app']->Session->GetCookie('visitor_name');
            $email = $GLOBALS['app']->Session->GetCookie('visitor_email');
            $url   = $GLOBALS['app']->Session->GetCookie('visitor_url');

            if(isset($post['name'])) {
                $name =  $post['name'];
            }
            if(isset($post['email'])) {
                $email =  $post['email'];
            }
            if(isset($post['url'])) {
                $url =  $post['url'];
            }

            $rand = rand();
            $tpl->SetVariable('rand', $rand);
            if (!$GLOBALS['app']->Session->Logged()) {
                $tpl->SetBlock('comment_form/info-box');
                $url_value = empty($url)? 'http://' : Jaws_XSS::filter($url);
                $tpl->SetVariable('url', _t('GLOBAL_URL'));
                $tpl->SetVariable('urlvalue', $url_value);
                $tpl->SetVariable('rand', $rand);
                $tpl->SetVariable('name', _t('GLOBAL_NAME'));
                $tpl->SetVariable('namevalue', isset($name) ? Jaws_XSS::filter($name) : '');
                $tpl->SetVariable('email', _t('GLOBAL_EMAIL'));
                $tpl->SetVariable('emailvalue', isset($email) ? Jaws_XSS::filter($email) : '');
                $tpl->ParseBlock('comment_form/info-box');
            }

            //captcha
            $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $mPolicy->loadCaptcha($tpl, 'comment_form');

        } else {
            $tpl->SetBlock('comment_form/unregistered');
            $tpl->SetVariable('msg', _t('GLOBAL_ERROR_ACCESS_RESTRICTED',
                $GLOBALS['app']->Map->GetURLFor('Users', 'LoginBox'),
                $GLOBALS['app']->Map->GetURLFor('Users', 'Registration')));
            $tpl->ParseBlock('comment_form/unregistered');
        }

        $tpl->SetVariable('url2', _t('GLOBAL_SPAMCHECK_EMPTY'));
        $tpl->SetVariable('url2_value', '');
        $tpl->SetVariable('preview', _t('GLOBAL_PREVIEW'));

        $response = $GLOBALS['app']->Session->PopResponse('Comments');
        if (!empty($response)) {
            $tpl->SetBlock('comment_form/response');
            $tpl->SetVariable('bookmark', $gadget. '_'. $action);
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
            $tpl->ParseBlock('comment_form/response');
        }

        $tpl->ParseBlock('comment_form');
        return $tpl->Get();
    }


    /**
     * Displays a block of pages belongs to the specified group
     *
     * @access  public
     * @param   string  $gadget          Gadget name
     * @param   string  $action          Gadget action
     * @param   int     $reference
     * @param   array   $pagination_data
     * @param   int     $perPage
     * @param   int     $orderBy
     * @internal param string $gadget
     * @internal param mixed $limit limit recent comments (int)
     * @return  string  XHTML content
     */
    function ShowComments($gadget, $action, $reference, $pagination_data, $perPage = null, $orderBy = 0)
    {
        $rqst = jaws()->request->fetch(array('order', 'page'), 'get');
        $page = empty($rqst['page'])? 1 : (int)$rqst['page'];

        if(!empty($rqst['order'])) {
            $orderBy = (int)$rqst['order'];
        }

        if(empty($perPage)) {
            $perPage = $this->gadget->registry->fetch('comments_per_page');
        }

        $cModel = $this->gadget->model->load('Comments');
        $comments = $cModel->GetComments(
            $gadget,
            $action,
            $reference,
            '',
            Comments_Info::COMMENTS_STATUS_APPROVED,
            $perPage,
            ($page - 1) * $perPage,
            $orderBy
        );
        $comments_count = $cModel->GetCommentsCount($gadget, $action, $reference);

        $tpl = $this->gadget->template->load('Comments.html');
        $tpl->SetBlock('comments');

        $tpl->SetVariable('gadget', $gadget);

        $objDate = Jaws_Date::getInstance();
        $usrModel = new Jaws_User;
        if (!Jaws_Error::IsError($comments) && $comments != null) {
            foreach ($comments as $entry) {
                $tpl->SetBlock('comments/entry');

                $tpl->SetVariable('postedby_lbl', _t('COMMENTS_POSTEDBY'));

                if ($entry['user_registered_date']) {
                    $tpl->SetBlock('comments/entry/registered_date');
                    $tpl->SetVariable('registered_date_lbl', _t('COMMENTS_USERS_REGISTERED_DATE'));
                    $tpl->SetVariable('registered_date', $objDate->Format($entry['user_registered_date'], 'd MN Y'));
                    $tpl->ParseBlock('comments/entry/registered_date');
                }

                if (!empty($entry['username'])) {
                    // user's profile
                    $tpl->SetVariable(
                        'user_url',
                        $GLOBALS['app']->Map->GetURLFor(
                            'Users',
                            'Profile',
                            array('user' => $entry['username'])
                        )
                    );

                } else {
                    $tpl->SetVariable('user_url', Jaws_XSS::filter($entry['url']));
                }

                $nickname = empty($entry['nickname']) ? $entry['name'] : $entry['nickname'];
                $email = empty($entry['user_email']) ? $entry['email'] : $entry['user_email'];

                $tpl->SetVariable('nickname', Jaws_XSS::filter($nickname));
                $tpl->SetVariable('email', Jaws_XSS::filter($email));
                $tpl->SetVariable('username', Jaws_XSS::filter($entry['username']));
                // user's avatar
                $tpl->SetVariable(
                    'avatar',
                    $usrModel->GetAvatar(
                        $entry['avatar'],
                        $entry['email'],
                        80
                    )
                );
                $tpl->SetVariable('insert_time', $objDate->Format($entry['createtime']));
                $tpl->SetVariable('insert_time_iso', $objDate->ToISO($entry['createtime']));
                $tpl->SetVariable('message', Jaws_String::AutoParagraph($entry['msg_txt']));

                if (!empty($entry['reply'])) {
                    $tpl->SetBlock('comments/entry/reply');
                    $tpl->SetVariable('lbl_replier', _t('COMMENTS_REPLIER'));
                    $tpl->SetVariable('replier', $entry['replier_nickname']);
                    // user's profile
                    $tpl->SetVariable(
                        'replier_url',
                        $GLOBALS['app']->Map->GetURLFor(
                            'Users',
                            'Profile',
                            array('user' => $entry['replier_username'])
                        )
                    );
                    $tpl->SetVariable('reply', $entry['reply']);
                    $tpl->ParseBlock('comments/entry/reply');
                }

                $reply_url = & Piwi::CreateWidget('Link', _t('COMMENTS_REPLY_TO_COMMENT'),
                                                  'javascript:replyComment();');
                $tpl->SetVariable('reply-link', $reply_url->Get());

                $tpl->ParseBlock('comments/entry');
            }
        }

        $pagination_data['params']['order'] = $orderBy;

        // page navigation
        $this->GetPagesNavigation(
            $tpl,
            'comments',
            $page,
            $perPage,
            $comments_count,
            _t('COMMENTS_COMMENTS_COUNT', $comments_count),
            $gadget,
            $pagination_data['action'],
            $pagination_data['params']
        );

        $tpl->ParseBlock('comments');
        return $tpl->Get();

    }

    /**
     * Displays a preview of the given comment
     *
     * @access       public
     * @return       template content
     */
    function Preview()
    {
        return $this->Comments(true);
    }

    /**
     * Displays a preview of the given comment
     *
     * @access  public
     * @return  string XHTML template content
     */
    function ShowPreview()
    {
        $names = array(
            'name', 'email', 'url', 'title', 'message', 'createtime',
            'ip_address', 'reference'
        );
        $post = jaws()->request->fetch($names, 'post');
        if(empty($post['message'])) {
            return;
        }

        if ($GLOBALS['app']->Session->Logged()) {
            $post['name'] = $GLOBALS['app']->Session->GetAttribute('nickname');
            $post['email'] = $GLOBALS['app']->Session->GetAttribute('email');
            $post['url'] = $GLOBALS['app']->Session->GetAttribute('url');
        }

        $tpl = $this->gadget->template->load('Comments.html');
        $tpl->SetBlock('comment-preview');

        $usrModel = new Jaws_User;
        $objDate = Jaws_Date::getInstance();

        $tpl->SetVariable('name', $post['name']);
        $tpl->SetVariable('email', $post['email']);
        $tpl->SetVariable('url', $post['url']);
        if (is_null($post['ip_address'])) {
            $post['ip_address'] = $_SERVER['REMOTE_ADDR'];
        }
        $tpl->SetVariable('message', Jaws_String::AutoParagraph($post['message']));
        if (!isset($post['createtime'])) {
            $date = Jaws_Date::getInstance();
            $post['createtime'] = $date->Format(time());
        }

        $tpl->SetVariable('postedby_lbl', _t('COMMENTS_POSTEDBY'));

        $currentUser = $GLOBALS['app']->Session->GetAttribute('user');
        if (!empty($currentUser)) {
            $userInfo = $usrModel->GetUser($currentUser);
            $nickname = $userInfo['nickname'];
            $email = $userInfo['email'];
            $username = $userInfo['username'];
            $avatar = "";
            if(isset($userInfo['avatar'])) {
                $avatar = $userInfo['avatar'];
            }

            $tpl->SetBlock('comment-preview/registered_date');
            $tpl->SetVariable('registered_date_lbl', _t('COMMENTS_USERS_REGISTERED_DATE'));
            $tpl->SetVariable('registered_date', $objDate->Format($userInfo['registered_date'], 'd MN Y'));
            $tpl->ParseBlock('comment-preview/registered_date');

            // user's profile
            $tpl->SetVariable(
                'user_url',
                $GLOBALS['app']->Map->GetURLFor(
                    'Users',
                    'Profile',
                    array('user' => $username)
                )
            );

        } else {
            $nickname = $post['name'];
            $email = $post['email'];
            $username = '';
            $avatar = '';

            $tpl->SetVariable('user_url', $post['url']);
        }

        $tpl->SetVariable('nickname', $nickname);
        $tpl->SetVariable('email', $email);
        $tpl->SetVariable('username', $username);
        // user's avatar
        $tpl->SetVariable(
            'avatar',
            $usrModel->GetAvatar(
                $avatar,
                $email,
                80
            )
        );

        $tpl->SetVariable('insert_time', $post['createtime']);
        $tpl->SetVariable('insert_time_iso', $objDate->ToISO($post['createtime']));
        $tpl->SetVariable('ip_address', $post['ip_address']);

        $tpl->ParseBlock('comment-preview');
        return $tpl->Get();
    }

    /**
     * Get the comments messages list
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetMessages()
    {
        $rqst = jaws()->request->fetch(array('order','perpage', 'page'), 'get');
        $page = empty($rqst['page'])? 1 : (int)$rqst['page'];

        if(!empty($rqst['perpage'])) {
            $perPage = (int)$rqst['perpage'];
            $orderBy = (int)$rqst['order'];
        } else {
            $perPage = $this->gadget->registry->fetch('comments_per_page');
            $orderBy = 0;
        }

        $model = $this->gadget->model->load('Comments');
        $comments = $model->GetComments(
            'comments',
            $perPage,
            null,
            null,
            array(Comments_Info::COMMENTS_STATUS_APPROVED),
            false,
            ($page - 1) * $perPage,
            $orderBy
        );
        $comments_count = $model->HowManyFilteredComments('comments', '', 1);

        $tpl = $this->gadget->template->load('Comments.html');
        $tpl->SetBlock('comments');

        $objDate = Jaws_Date::getInstance();
        $usrModel = new Jaws_User;
        if (!Jaws_Error::IsError($comments) && $comments != null) {
            foreach ($comments as $entry) {
                $tpl->SetBlock('comments/entry');

                $tpl->SetVariable('postedby_lbl', _t('COMMENTS_POSTEDBY'));

                if ($entry['user_registered_date']) {
                    $tpl->SetBlock('comments/entry/registered_date');
                    $tpl->SetVariable('registered_date_lbl', _t('COMMENTS_USERS_REGISTERED_DATE'));
                    $tpl->SetVariable('registered_date', $objDate->Format($entry['user_registered_date'], 'd MN Y'));
                    $tpl->ParseBlock('comments/entry/registered_date');
                }

                if (!empty($entry['username'])) {
                    // user's profile
                    $tpl->SetVariable(
                        'user_url',
                        $GLOBALS['app']->Map->GetURLFor(
                            'Users',
                            'Profile',
                            array('user' => $entry['username'])
                        )
                    );

                } else {
                    $tpl->SetVariable('user_url', Jaws_XSS::filter($entry['url']));
                }

                $nickname = empty($entry['nickname']) ? $entry['name'] : $entry['nickname'];
                $email = empty($entry['user_email']) ? $entry['email'] : $entry['user_email'];

                $tpl->SetVariable('nickname', Jaws_XSS::filter($nickname));
                $tpl->SetVariable('email', Jaws_XSS::filter($email));
                $tpl->SetVariable('username', Jaws_XSS::filter($entry['username']));
                // user's avatar
                $tpl->SetVariable(
                    'avatar',
                    $usrModel->GetAvatar(
                        $entry['avatar'],
                        $entry['email'],
                        80
                    )
                );
                $tpl->SetVariable('insert_time', $objDate->Format($entry['createtime']));
                $tpl->SetVariable('insert_time_iso', $objDate->ToISO($entry['createtime']));
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
        $post  = jaws()->request->fetch(array('message', 'name', 'email', 'url', 'url2', 'requested_gadget',
                                              'requested_action', 'reference', 'redirect_to', 'is_private'), 'post');

        $redirectTo = str_replace('&amp;', '&', $post['redirect_to']);
        if ($GLOBALS['app']->Session->Logged()) {
            $post['name']  = $GLOBALS['app']->Session->GetAttribute('nickname');
            $post['email'] = $GLOBALS['app']->Session->GetAttribute('email');
            $post['url']   = $GLOBALS['app']->Session->GetAttribute('url');
        }

        if (trim($post['message']) == ''|| trim($post['name']) == '') {
            $GLOBALS['app']->Session->PushResponse(
                _t('COMMENTS_DONT_SEND_EMPTY_MESSAGES'),
                'Comments',
                RESPONSE_ERROR,
                $post
            );
            Jaws_Header::Location($redirectTo);
        }

        /* lets check if it's spam
        * it's rather common that spam engines
        * fill out all inputs and this one is hidden
        * via CSS so not many engines are smart enough
        * to not fill this out
        */
        if (!empty($post['url2'])) {
            $GLOBALS['app']->Session->PushResponse(
                _t('COMMENTS_FAILED_SPAM_CHECK_MESSAGES'),
                'Comments',
                RESPONSE_ERROR,
                $post
            );
            Jaws_Header::Location($redirectTo);
        }

        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $resCheck = $mPolicy->checkCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $GLOBALS['app']->Session->PushResponse(
                $resCheck->getMessage(),
                'Comments',
                RESPONSE_ERROR,
                $post
            );
            Jaws_Header::Location($redirectTo);
        }

        $permalink = $GLOBALS['app']->GetSiteURL();
        $status = $this->gadget->registry->fetch('default_comment_status');
        if ($this->gadget->GetPermission('ManageComments')) {
            $status = Comments_Info::COMMENTS_STATUS_APPROVED;
        }

        $model = $this->gadget->model->load('EditComments');
        $res = $model->insertComment(
            $post['requested_gadget'], $post['reference'], $post['requested_action'], $post['name'],
            $post['email'], $post['url'], $post['message'], $_SERVER['REMOTE_ADDR'],
            $permalink, $status, $post['is_private']
        );

        if (Jaws_Error::isError($res)) {
            $GLOBALS['app']->Session->PushResponse(
                $res->getMessage(),
                'Comments',
                RESPONSE_ERROR,
                $post
            );
        } else {
            $GLOBALS['app']->Session->PushResponse(_t('COMMENTS_MESSAGE_SENT'), 'Comments');
        }

        Jaws_Header::Location($redirectTo);
    }

}