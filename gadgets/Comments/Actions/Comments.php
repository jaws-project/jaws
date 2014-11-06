<?php
/**
 * Comments Gadget
 *
 * @category   Gadget
 * @package    Comments
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2012-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Actions_Comments extends Comments_Actions_Default
{
    /**
     * Displays GuestBook
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Guestbook()
    {
        $tpl = $this->gadget->template->load('Guestbook.html');
        $tpl->SetBlock('guestbook');
        $tpl->SetVariable('title', _t('COMMENTS_GUESTBOOK'));

        $tpl->SetVariable(
            'comments',
            $this->ShowComments('Comments', 'Guestbook', 0, array('action' => 'Guestbook'))
        );
        $redirect_to = $this->gadget->urlMap('Guestbook');
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

        $response = $GLOBALS['app']->Session->PopResponse('Comments');
        if (isset($response['data'])) {
            $data = $response['data'];
        } else {
            $data = array(
                'name'    => '',
                'email'   => '',
                'url'     => '',
                'url2'    => '',
                'message' => '',
            );
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

            $rand = rand();
            $tpl->SetVariable('rand', $rand);
            if (!$GLOBALS['app']->Session->Logged()) {
                $tpl->SetBlock('comment_form/info-box');
                $url_value = empty($data['url'])? 'http://' : $data['url'];
                $tpl->SetVariable('url', _t('GLOBAL_URL'));
                $tpl->SetVariable('urlvalue', $url_value);
                $tpl->SetVariable('rand', $rand);
                $tpl->SetVariable('name', _t('GLOBAL_NAME'));
                $tpl->SetVariable('namevalue', $data['name']);
                $tpl->SetVariable('email', _t('GLOBAL_EMAIL'));
                $tpl->SetVariable('emailvalue', $data['email']);
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
        $tpl->SetVariable('url2_value', $data['url2']);
        $tpl->SetVariable('message', $data['message']);

        $tpl->SetVariable('bookmark', $gadget. '_'. $action);
        $response = $GLOBALS['app']->Session->PopResponse('Comments');
        if (!empty($response)) {
            $tpl->SetVariable('type', $response['type']);
            $tpl->SetVariable('text', $response['text']);
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
     * @param   int     $user           User Id
     * @param   int     $perPage
     * @param   int     $orderBy
     * @internal param string $gadget
     * @internal param mixed $limit limit recent comments (int)
     * @return  string  XHTML content
     */
    function ShowComments($gadget, $action, $reference, $pagination_data, $user = null, $perPage = null, $orderBy = 0)
    {
        $max_size = 52;
        $compactView = $GLOBALS['app']->requestedActionMode == ACTION_MODE_LAYOUT;
        $rqst = jaws()->request->fetch(array('order', 'page'), 'get');
        $page = empty($rqst['page'])? 1 : (int)$rqst['page'];

        if ($GLOBALS['app']->requestedActionMode == ACTION_MODE_NORMAL && !empty($rqst['order'])) {
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
            $orderBy,
            $user
        );
        $comments_count = $cModel->GetCommentsCount($gadget, $action, $reference, '', '', $user);

        $tpl = $this->gadget->template->load('Comments.html');
        $block = 'comments';
        if ($compactView == true) {
            $block = 'comments_compact';
        }
        $tpl->SetBlock($block);

        $tpl->SetVariable('title', _t('COMMENTS_COMMENTS'));
        $tpl->SetVariable('gadget', strtolower($gadget));

        $objDate = Jaws_Date::getInstance();
        $usrModel = new Jaws_User;
        if (!Jaws_Error::IsError($comments) && $comments != null) {
            foreach ($comments as $entry) {
                $tpl->SetBlock($block . '/entry');

                $tpl->SetVariable('postedby_lbl', _t('COMMENTS_POSTEDBY'));

                if ($entry['user_registered_date']) {
                    $tpl->SetBlock($block . '/entry/registered_date');
                    $tpl->SetVariable('registered_date_lbl', _t('COMMENTS_USERS_REGISTERED_DATE'));
                    $tpl->SetVariable('registered_date', $objDate->Format($entry['user_registered_date'], 'd MN Y'));
                    $tpl->ParseBlock($block . '/entry/registered_date');
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
                $tpl->SetVariable('message', $this->gadget->ParseText($entry['msg_txt'], 'Comments', 'index'));
                $tpl->SetVariable('message_abbr', (Jaws_UTF8::strlen($entry['msg_txt']) >= $max_size)?
                    Jaws_UTF8::substr($entry['msg_txt'], 0, $max_size).'...' :
                    $entry['msg_txt']
                );

                if (Jaws_UTF8::strlen($entry['msg_txt']) >= $max_size) {
                    $tpl->SetBlock($block . '/entry/read_more');
                    $tpl->SetVariable('read_more', _t('COMMENTS_READ_MORE'));

                    switch ($entry['gadget']) {
                        case 'Blog':
                            $url = $GLOBALS['app']->Map->GetURLFor(
                                'Blog',
                                'SingleView',
                                array('id' => $entry['reference']),
                                true
                            );
                            $url = $url. '#comment'. $entry['id'];
                            break;

                        case 'Phoo':
                            $url = $GLOBALS['app']->Map->GetURLFor(
                                'Phoo',
                                'ViewImage',
                                array('id' => $entry['reference']),
                                true
                            );
                            $url = $url. '#comment'. $entry['id'];
                            break;

                        case 'Shoutbox':
                            $url = $GLOBALS['app']->Map->GetURLFor(
                                'Shoutbox',
                                'Comments',
                                array(),
                                true
                            );
                            $url = $url. '#comment'. $entry['id'];
                            break;

                        case 'Comments':
                            $url = $GLOBALS['app']->Map->GetURLFor(
                                'Comments',
                                'Guestbook',
                                array(),
                                true
                            );
                            $url = $url. '#comment'. $entry['id'];
                            break;

                        default:
                            $url = '';
                    }

                    $tpl->SetVariable('read_more_url', $url);
                    $tpl->ParseBlock($block . '/entry/read_more');
                }

                if (!empty($entry['reply'])) {
                    $tpl->SetBlock($block . '/entry/reply');
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
                    $tpl->ParseBlock($block . '/entry/reply');
                }

                $reply_url = & Piwi::CreateWidget('Link', _t('COMMENTS_REPLY_TO_COMMENT'),
                                                  'javascript:replyComment();');
                $tpl->SetVariable('reply-link', $reply_url->Get());

                $tpl->ParseBlock($block . '/entry');
            }
        }

        if (!$compactView) {
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

            // feeds actions
            $tpl->SetVariable('lbl_feeds', _t('COMMENTS_COMMENTS_XML'));
            $tpl->SetVariable(
                'atom_url',
                $this->gadget->urlMap(
                    'RecentCommentsAtom',
                    array('gadgetname' => $gadget, 'actionname' => $action, 'reference' => $reference)
                )
            );
            $tpl->SetVariable(
                'rss_url',
                $this->gadget->urlMap(
                    'RecentCommentsRSS',
                    array('gadgetname' => $gadget, 'actionname' => $action, 'reference' => $reference)
                )
            );
        }

        $tpl->ParseBlock($block);
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
            $this->gadget->name,
            '',
            '',
            '',
            array(Comments_Info::COMMENTS_STATUS_APPROVED),
            $perPage,
            ($page - 1) * $perPage,
            $orderBy
        );
        $comments_count = $model->GetCommentsCount(
            $this->gadget->name,
            '',
            '',
            '',
            array(Comments_Info::COMMENTS_STATUS_APPROVED)
        );

        $tpl = $this->gadget->template->load('Comments.html');
        $tpl->SetBlock('comments');
        $tpl->SetVariable('gadget', strtolower($this->gadget->name));

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
        $post  = jaws()->request->fetch(
            array(
                'message', 'name', 'email', 'url', 'url2', 'requested_gadget',
                'requested_action', 'reference', 'is_private'
            ),
            'post'
        );

        if ($GLOBALS['app']->Session->Logged()) {
            $post['name']  = $GLOBALS['app']->Session->GetAttribute('nickname');
            $post['email'] = $GLOBALS['app']->Session->GetAttribute('email');
            $post['url']   = $GLOBALS['app']->Session->GetAttribute('url');
        }

        if (trim($post['message']) == ''|| trim($post['name']) == '') {
            $GLOBALS['app']->Session->PushResponse(
                _t('COMMENTS_COMMENT_INCOMPLETE_FIELDS'),
                'Comments',
                RESPONSE_ERROR,
                $post
            );
            Jaws_Header::Referrer();
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
            Jaws_Header::Referrer();
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
            Jaws_Header::Referrer();
        }

        $permalink = $GLOBALS['app']->GetSiteURL();
        $status = $this->gadget->registry->fetch('default_comment_status');
        if ($this->gadget->GetPermission('ManageComments')) {
            $status = Comments_Info::COMMENTS_STATUS_APPROVED;
        }

        $objHook = Jaws_Gadget::getInstance($post['requested_gadget'])->hook->load('Comments');
        if (Jaws_Error::IsError($objHook)) {
            $GLOBALS['app']->Session->PushResponse(
                $objHook->getMessage(),
                'Comments',
                RESPONSE_ERROR,
                $post
            );
            Jaws_Header::Referrer();
        }

        $reference = $objHook->Execute($post['requested_action'], $post['reference']);
        if (empty($reference)) {
            $GLOBALS['app']->Session->PushResponse(
                _t('COMMENTS_ERROR_REFERENCE_EXISTS'),
                'Comments',
                RESPONSE_ERROR,
                $post
            );
            Jaws_Header::Referrer();
        }

        $res = $this->gadget->model->load('EditComments')->insertComment(
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
            $this->EmailComment($reference, $post['message']);
            $GLOBALS['app']->Session->PushResponse(_t('COMMENTS_MESSAGE_SENT'), 'Comments');
        }

        Jaws_Header::Location($reference['url']);
    }

    /**
     * Mails the comments to the owner and author
     *
     * @access  public
     * @param   array   $reference  Reference information
     * @param   string  $message    Message content
     * @return  mixed   True if successful otherwise Jaws_Error
     */
    function EmailComment($reference, $message)
    {
        $site_url   = $GLOBALS['app']->getSiteURL('/');
        $site_name  = $this->gadget->registry->fetch('site_name', 'Settings');

        $tpl = $this->gadget->template->load('EmailComment.html');
        $tpl->SetBlock('notification');
        $tpl->SetVariable('comment', $message);
        $tpl->SetVariable('lbl_url', _t('GLOBAL_URL'));

        $tpl->SetVariable('url',   $reference['url']);
        $tpl->SetVariable('title', $reference['title']);
        $tpl->SetVariable('site-name', $site_name);
        $tpl->SetVariable('site-url',  $site_url);
        $tpl->ParseBlock('notification');
        $template = $tpl->Get();

        $ObjMail = new Jaws_Mail;
        $ObjMail->SetFrom();
        if (empty($reference['author_email'])) {
            $ObjMail->AddRecipient('', 'to');
        } else {
            $ObjMail->AddRecipient($reference['author_email']);
            $ObjMail->AddRecipient('', 'cc');
        }

        $ObjMail->SetSubject(_t('COMMENTS_COMMENT_NOTIFICATION', $reference['title']));
        $ObjMail->SetBody($template, 'html');
        return $ObjMail->send();
    }

    /**
     * Mails reply to the sender
     *
     * @access  public
     * @param   string  $email      Comment sender's email
     * @param   string  $message    Message
     * @param   string  $reply      Reply message
     * @param   int     $replier    Replier Id
     * @return  mixed   True on successfully or Jaws_Error on failure
     */
    function EmailReply($email, $message, $reply, $replier)
    {
        $site_url   = $GLOBALS['app']->getSiteURL('/');
        $site_name  = $this->gadget->registry->fetch('site_name', 'Settings');

        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
        Jaws_Translate::getInstance()->LoadTranslation('Global', JAWS_COMPONENT_OTHERS, $site_language);
        Jaws_Translate::getInstance()->LoadTranslation('Comments', JAWS_COMPONENT_GADGET, $site_language);

        $tpl = $this->gadget->template->load('EmailReply.html');
        $tpl->SetBlock('notification');
        $tpl->SetVariable('lbl_message',  _t_lang($site_language, 'COMMENTS_MESSAGE'));
        $tpl->SetVariable('message',      $message);
        $tpl->SetVariable('replier',      _t_lang($site_language, 'COMMENTS_REPLY_BY', $replier));
        $tpl->SetVariable('lbl_reply',    _t_lang($site_language, 'COMMENTS_REPLY'));
        $tpl->SetVariable('reply',        $reply);
        $tpl->SetVariable('site_name',    $site_name);
        $tpl->SetVariable('site_url',     $site_url);

        $tpl->ParseBlock('notification');
        $template = $tpl->Get();

        $ObjMail = new Jaws_Mail;
        $ObjMail->SetFrom();
        if (empty($email)) {
            $ObjMail->AddRecipient('', 'to');
        } else {
            $ObjMail->AddRecipient($email);
            $ObjMail->AddRecipient('', 'cc');
        }
        $ObjMail->SetSubject(_t_lang('COMMENTS_YOU_GET_REPLY'));
        $ObjMail->SetBody($template, 'html');
        return $ObjMail->send();
    }

}