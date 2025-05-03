<?php
/**
 * Comments Gadget
 *
 * @category   Gadget
 * @package    Comments
 * @author     ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2012-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Actions_Comments extends Jaws_Gadget_Action
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
        $tpl->SetVariable('title', $this::t('GUESTBOOK'));

        $tpl->SetVariable(
            'comments',
            $this->ShowComments('Comments', 'Guestbook', 0, array('action' => 'Guestbook'))
        );
        $tpl->SetVariable('comment-form', $this->ShowCommentsForm(
            'Comments',
            'Guestbook',
            0
        ));

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
     * @return  string  XHTML content
     */
    function ShowCommentsForm($gadget, $action, $reference)
    {
        $tpl = $this->gadget->template->load('CommentForm.html');
        $tpl->SetBlock('comment_form');
        $tpl->SetVariable('title', $this::t('COMMENTS'));

        $response = $this->gadget->session->pop('Comments');
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
        $tpl->SetVariable('private', $this::t('PRIVATE'));

        $allow_comments_config = $this->gadget->registry->fetch('allow_comments', 'Comments');
        switch ($allow_comments_config) {
            case 'restricted':
                $allow_comments_config = $this->app->session->user->logged;
                break;

            default:
                $allow_comments_config = $allow_comments_config == 'true';
        }

        if ($allow_comments_config) {
            $tpl->SetVariable('base_script', BASE_SCRIPT);
            $tpl->SetVariable('lbl_message', $this::t('MESSAGE'));
            $tpl->SetVariable('send', $this::t('SEND'));

            $rand = rand();
            $tpl->SetVariable('rand', $rand);
            if (!$this->app->session->user->logged) {
                $tpl->SetBlock('comment_form/info-box');
                $url_value = empty($data['url'])? 'http://' : $data['url'];
                $tpl->SetVariable('url', Jaws::t('URL'));
                $tpl->SetVariable('urlvalue', $url_value);
                $tpl->SetVariable('rand', $rand);
                $tpl->SetVariable('name', Jaws::t('NAME'));
                $tpl->SetVariable('namevalue', $data['name']);
                $tpl->SetVariable('email', Jaws::t('EMAIL'));
                $tpl->SetVariable('emailvalue', $data['email']);
                $tpl->ParseBlock('comment_form/info-box');
            }

            //captcha
            $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
            $mPolicy->loadCaptcha($tpl);

        } else {
            $tpl->SetBlock('comment_form/unregistered');
            $tpl->SetVariable('msg', Jaws::t('ERROR_ACCESS_RESTRICTED',
                $this->app->map->GetMappedURL('Users', 'Login'),
                $this->app->map->GetMappedURL('Users', 'Registration')));
            $tpl->ParseBlock('comment_form/unregistered');
        }

        $tpl->SetVariable('url2', Jaws::t('SPAMCHECK_EMPTY'));
        $tpl->SetVariable('url2_value', $data['url2']);
        $tpl->SetVariable('message', $data['message']);

        $tpl->SetVariable('bookmark', $gadget. '_'. $action);
        $response = $this->gadget->session->pop('Comments');
        if (!empty($response)) {
            $tpl->SetVariable('response_type', $response['type']);
            $tpl->SetVariable('response_text', $response['text']);
        }

        $tpl->ParseBlock('comment_form');
        return $tpl->Get();
    }

    /**
     * Displays a block of pages belongs to the specified group
     *
     * @access  public
     * @param   array   $interface  Gadget interface(gadget, action, reference, ...)
     * @return  array   template variables
     */
    function xShowCommentsForm($interface = array())
    {
        $defaultInterface = array(
            'gadget'     => '',
            'action'     => '',
            'reference'  => 0
        );
        $interface = array_merge($defaultInterface, $interface);

        // initiate assign with option array
        $assigns = array();
        $assigns['gadget'] = $interface['gadget'];
        $assigns['action'] = $interface['action'];
        $assigns['reference'] = $interface['reference'];
        $assigns['base_script'] = BASE_SCRIPT;
        $assigns['rand'] = rand();
        $assigns['msg_access_restricted'] = Jaws::t('ERROR_ACCESS_RESTRICTED',
            $this->app->map->GetMappedURL('Users', 'Login'),
            $this->app->map->GetMappedURL('Users', 'Registration'));

        $response = $this->gadget->session->pop('Comments');
        $assigns['response'] = $response;
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

        $allow_comments_config = $this->gadget->registry->fetch('allow_comments', 'Comments');
        switch ($allow_comments_config) {
            case 'restricted':
                $allow_comments_config = $this->app->session->user->logged;
                break;

            default:
                $allow_comments_config = $allow_comments_config == 'true';
        }
        $assigns['allow_comments_config'] = $allow_comments_config;

        $assigns['urlvalue'] = empty($data['url'])? 'http://' : $data['url'];
        $assigns['namevalue'] = $data['name'];
        $assigns['emailvalue'] = $data['email'];
        $assigns['url2_value'] = $data['url2'];
        $assigns['message'] = $data['message'];

        // captcha
        $assigns['captcha'] = Jaws_Gadget::getInstance('Policy')
            ->action
            ->load('Captcha')
            ->xloadCaptcha();

        return $assigns;

//        $response = $this->gadget->session->pop('Comments');
//        if (!empty($response)) {
//            $tpl->SetVariable('response_type', $response['type']);
//            $tpl->SetVariable('response_text', $response['text']);
//        }
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
        $compactView = $this->app->requestedActionMode == ACTION_MODE_LAYOUT;
        $rqst = $this->gadget->request->fetch(array('order', 'page'), 'get');
        $page = empty($rqst['page'])? 1 : (int)$rqst['page'];

        if ($this->app->requestedActionMode == ACTION_MODE_NORMAL && !empty($rqst['order'])) {
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

        $tpl->SetVariable('title', $this::t('COMMENTS'));
        $tpl->SetVariable('gadget', strtolower($gadget));

        $objDate = Jaws_Date::getInstance();
        if (!Jaws_Error::IsError($comments) && $comments != null) {
            foreach ($comments as $entry) {
                $tpl->SetBlock($block . '/entry');

                $tpl->SetVariable('postedby_lbl', $this::t('POSTEDBY'));

                if ($entry['user_registered_date']) {
                    $tpl->SetBlock($block . '/entry/registered_date');
                    $tpl->SetVariable('registered_date_lbl', $this::t('USERS_REGISTERED_DATE'));
                    $tpl->SetVariable('registered_date', $objDate->Format($entry['user_registered_date'], 'dd MMMM yyyy'));
                    $tpl->ParseBlock($block . '/entry/registered_date');
                }

                if (!empty($entry['username'])) {
                    // user's profile
                    $tpl->SetVariable(
                        'user_url',
                        $this->app->map->GetMappedURL(
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
                    Jaws_Gadget::getInstance('Users')->urlMap('Avatar', array('user'  => $entry['username']))
                );
                $tpl->SetVariable('insert_time', $objDate->Format($entry['insert_time']));
                $tpl->SetVariable('insert_time_iso', $objDate->ToISO($entry['insert_time']));
                $tpl->SetVariable('message', $this->gadget->plugin->parse($entry['msg_txt']));
                $tpl->SetVariable('message_abbr', (Jaws_UTF8::strlen($entry['msg_txt']) >= $max_size)?
                    Jaws_UTF8::substr($entry['msg_txt'], 0, $max_size).'...' :
                    $entry['msg_txt']
                );

                // Show like rating
                if (Jaws_Gadget::IsGadgetInstalled('Rating')) {
                    $ratingHTML = Jaws_Gadget::getInstance('Rating')->action->load('RatingTypes');
                    $ratingHTML->loadReferenceLike('Comments', 'comment', $entry['id'], 0, $tpl, 'comments/entry');
                }

                $tpl->SetBlock($block . '/entry/read_more');
                $tpl->SetVariable('read_more', $this::t('READ_MORE'));

                $tpl->SetVariable('read_more_url', $entry['reference_link']);
                $tpl->ParseBlock($block . '/entry/read_more');

                if (!empty($entry['reply'])) {
                    $tpl->SetBlock($block . '/entry/reply');
                    $tpl->SetVariable('lbl_replier', $this::t('REPLIER'));
                    $tpl->SetVariable('replier', $entry['replier_nickname']);
                    // user's profile
                    $tpl->SetVariable(
                        'replier_url',
                        $this->app->map->GetMappedURL(
                            'Users',
                            'Profile',
                            array('user' => $entry['replier_username'])
                        )
                    );
                    $tpl->SetVariable('reply', $entry['reply']);
                    $tpl->ParseBlock($block . '/entry/reply');
                }

                $reply_url = & Piwi::CreateWidget('Link', $this::t('REPLY_TO_COMMENT'),
                                                  'javascript:replyComment();');
                $tpl->SetVariable('reply-link', $reply_url->Get());

                $tpl->ParseBlock($block . '/entry');
            }
        }

        if (!$compactView) {
            $pagination_data['params']['order'] = $orderBy;
            // pagination
            $this->gadget->action->load('PageNavigation')->pagination(
                $tpl,
                $page,
                $perPage,
                $comments_count,
                $pagination_data['action'],
                $pagination_data['params'],
                $this::t('COMMENTS_COUNT', $comments_count),
                $gadget
            );

            // feeds actions
            $tpl->SetVariable('lbl_feeds', $this::t('COMMENTS_XML'));
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
     * Displays comments belongs to the specified gadget,action
     *
     * @access  public
     * @param   array   $interface  Gadget interface(gadget, action, reference, ...)
     * @param   array   $options    User interface control options(reference, pagination_data, user, per_page, order_by, ...)
     * @return  array   Comment's template variables
     */
    function xShowComments($interface = array(), $options = array())
    {
        $defaultOptions = array(
            'pagination_data'   => array(),
            'user'              => null,
            'per_page'          => null,
            'order_by'          => 0
        );
        $options = array_merge($defaultOptions, $options);

        $defaultInterface = array(
            'gadget'     => '',
            'action'     => '',
            'reference'  => 0
        );
        $interface = array_merge($defaultInterface, $interface);

        $max_size = 52;
        $compactView = $this->app->requestedActionMode == ACTION_MODE_LAYOUT;
        $rqst = $this->gadget->request->fetch(array('order', 'page'), 'get');
        $page = empty($rqst['page']) ? 1 : (int)$rqst['page'];

        if ($this->app->requestedActionMode == ACTION_MODE_NORMAL && !empty($rqst['order'])) {
            $options['order_by'] = (int)$rqst['order'];
        }

        if (empty($options['per_page'])) {
            $options['per_page'] = $this->gadget->registry->fetch('comments_per_page');
        }

        // initiate assign with option array
        $assigns = array();
        $assigns['gadget'] = $interface['gadget'];
        $assigns['compact_view'] = $compactView;

        $cModel = $this->gadget->model->load('Comments');
        $comments = $cModel->GetComments(
            $interface['gadget'],
            $interface['action'],
            $interface['reference'],
            '',
            Comments_Info::COMMENTS_STATUS_APPROVED,
            $options['per_page'],
            ($page - 1) * $options['per_page'],
            $options['order_by'],
            $options['user']
        );
        $comments_count = $cModel->GetCommentsCount(
            $interface['gadget'],
            $interface['action'],
            $interface['reference'],
            '',
            '',
            $options['user']
        );

        if (!Jaws_Error::IsError($comments) && $comments != null) {
            foreach ($comments as &$comment) {
                $comment['nickname'] = empty($comment['nickname']) ? $comment['name'] : $comment['nickname'];
                $comment['email'] = empty($comment['user_email']) ? $comment['email'] : $comment['user_email'];
                $comment['avatar'] = Jaws_Gadget::getInstance('Users')->urlMap('Avatar', array('user'  => $comment['username']));
                $comment['message_abbr'] = (Jaws_UTF8::strlen($comment['msg_txt']) >= $max_size)?
                    Jaws_UTF8::substr($comment['msg_txt'], 0, $max_size).'...' :
                    $comment['msg_txt'];
            }
        }

//        $tpl->SetVariable('title', $this::t('COMMENTS'));
        $assigns['comments'] = $comments;
        $assigns['gadget'] = $interface['gadget'];
        $assigns['action'] = $interface['action'];
        $assigns['reference'] = $interface['reference'];


/*        $objDate = Jaws_Date::getInstance();
        if (!Jaws_Error::IsError($comments) && $comments != null) {
            foreach ($comments as $entry) {
                $tpl->SetBlock($block . '/entry');

                $tpl->SetVariable('postedby_lbl', $this::t('POSTEDBY'));

                if ($entry['user_registered_date']) {
                    $tpl->SetBlock($block . '/entry/registered_date');
                    $tpl->SetVariable('registered_date_lbl', $this::t('USERS_REGISTERED_DATE'));
                    $tpl->SetVariable('registered_date', $objDate->Format($entry['user_registered_date'], 'dd MMMM yyyy'));
                    $tpl->ParseBlock($block . '/entry/registered_date');
                }

                if (!empty($entry['username'])) {
                    // user's profile
                    $tpl->SetVariable(
                        'user_url',
                        $this->app->map->GetMappedURL(
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
                    Jaws_Gadget::getInstance('Users')->urlMap('Avatar', array('user'  => $entry['username']))
                );
                $tpl->SetVariable('insert_time', $objDate->Format($entry['insert_time']));
                $tpl->SetVariable('insert_time_iso', $objDate->ToISO($entry['insert_time']));
                $tpl->SetVariable('message', $this->gadget->plugin->parse($entry['msg_txt']));
                $tpl->SetVariable('message_abbr', (Jaws_UTF8::strlen($entry['msg_txt']) >= $max_size)?
                    Jaws_UTF8::substr($entry['msg_txt'], 0, $max_size).'...' :
                    $entry['msg_txt']
                );

                // Show like rating
                if (Jaws_Gadget::IsGadgetInstalled('Rating')) {
                    $ratingHTML = Jaws_Gadget::getInstance('Rating')->action->load('RatingTypes');
                    $ratingHTML->loadReferenceLike('Comments', 'comment', $entry['id'], 0, $tpl, 'comments/entry');
                }

                $tpl->SetBlock($block . '/entry/read_more');
                $tpl->SetVariable('read_more', $this::t('READ_MORE'));

                $tpl->SetVariable('read_more_url', $entry['reference_link']);
                $tpl->ParseBlock($block . '/entry/read_more');

                if (!empty($entry['reply'])) {
                    $tpl->SetBlock($block . '/entry/reply');
                    $tpl->SetVariable('lbl_replier', $this::t('REPLIER'));
                    $tpl->SetVariable('replier', $entry['replier_nickname']);
                    // user's profile
                    $tpl->SetVariable(
                        'replier_url',
                        $this->app->map->GetMappedURL(
                            'Users',
                            'Profile',
                            array('user' => $entry['replier_username'])
                        )
                    );
                    $tpl->SetVariable('reply', $entry['reply']);
                    $tpl->ParseBlock($block . '/entry/reply');
                }

                $reply_url = & Piwi::CreateWidget('Link', $this::t('REPLY_TO_COMMENT'),
                                                  'javascript:replyComment();');
                $tpl->SetVariable('reply-link', $reply_url->Get());

                $tpl->ParseBlock($block . '/entry');
            }
        }

        if (!$compactView) {
            $options['pagination_data']['params']['order'] = $orderBy;
            // pagination
            $this->gadget->action->load('PageNavigation')->pagination(
                $tpl,
                $page,
                $options['per_page'],
                $comments_count,
                $options['pagination_data']['action'],
                $options['pagination_data']['params'],
                $this::t('COMMENTS_COUNT', $comments_count),
                $interface['gadget']
            );

            // feeds actions
            $tpl->SetVariable('lbl_feeds', $this::t('COMMENTS_XML'));
            $tpl->SetVariable(
                'atom_url',
                $this->gadget->urlMap(
                    'RecentCommentsAtom',
                    array('gadgetname' => $interface['gadget'], 'actionname' => $interface['action'], 'reference' => $interface['reference'])
                )
            );
            $tpl->SetVariable(
                'rss_url',
                $this->gadget->urlMap(
                    'RecentCommentsRSS',
                    array('gadgetname' => $interface['gadget'], 'actionname' => $interface['action'], 'reference' => $interface['reference'])
                )
            );
        }*/
        return $assigns;
    }

    /**
     * Get the comments messages list
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetMessages()
    {
        $rqst = $this->gadget->request->fetch(array('order','perpage', 'page'), 'get');
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
        if (!Jaws_Error::IsError($comments) && $comments != null) {
            foreach ($comments as $entry) {
                $tpl->SetBlock('comments/entry');

                $tpl->SetVariable('postedby_lbl', $this::t('POSTEDBY'));

                if ($entry['user_registered_date']) {
                    $tpl->SetBlock('comments/entry/registered_date');
                    $tpl->SetVariable('registered_date_lbl', $this::t('USERS_REGISTERED_DATE'));
                    $tpl->SetVariable('registered_date', $objDate->Format($entry['user_registered_date'], 'dd MMMM yyyy'));
                    $tpl->ParseBlock('comments/entry/registered_date');
                }

                if (!empty($entry['username'])) {
                    // user's profile
                    $tpl->SetVariable(
                        'user_url',
                        $this->app->map->GetMappedURL(
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
                    Jaws_Gadget::getInstance('Users')->urlMap('Avatar', array('user'  => $entry['username']))
                );
                $tpl->SetVariable('insert_time', $objDate->Format($entry['insert_time']));
                $tpl->SetVariable('insert_time_iso', $objDate->ToISO($entry['insert_time']));
                $tpl->SetVariable('message', Jaws_String::AutoParagraph($entry['msg_txt']));

                $tpl->ParseBlock('comments/entry');
            }
        }

        // pagination
        $this->gadget->action->load('PageNavigation')->pagination(
            $tpl,
            $page,
            $perPage,
            $comments_count,
            'Comments',
            array('perpage'=>$perPage, 'order'=>$orderBy),
            $this::t('COMMENTS_COUNT', $comments_count)
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
        $post  = $this->gadget->request->fetch(
            array(
                'message|text', 'name|text', 'email?null', 'url?null', 'url2?null', 'requested_gadget|text',
                'requested_action|text', 'reference|text', 'is_private|boolean?boolean'
            ),
            'post'
        );

        if ($this->app->session->user->logged) {
            $post['name']  = $this->app->session->user->nickname;
            $post['email'] = $this->app->session->user->email;
        }

        if (trim($post['message']) == '' || trim($post['name']) == '') {
            $this->gadget->session->push(
                $this::t('COMMENT_INCOMPLETE_FIELDS'),
                RESPONSE_ERROR,
                'Comments',
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
            $this->gadget->session->push(
                $this::t('FAILED_SPAM_CHECK_MESSAGES'),
                RESPONSE_ERROR,
                'Comments',
                $post
            );
            Jaws_Header::Referrer();
        }

        $mPolicy = Jaws_Gadget::getInstance('Policy')->action->load('Captcha');
        $resCheck = $mPolicy->checkCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $this->gadget->session->push(
                $resCheck->getMessage(),
                RESPONSE_ERROR,
                'Comments',
                $post
            );
            Jaws_Header::Referrer();
        }

        $permalink = $this->app->getSiteURL();
        $status = $this->gadget->registry->fetch('default_comment_status');
        if ($this->gadget->GetPermission('ManageComments')) {
            $status = Comments_Info::COMMENTS_STATUS_APPROVED;
        }

        $objHook = Jaws_Gadget::getInstance($post['requested_gadget'])->hook->load('Comments');
        if (Jaws_Error::IsError($objHook)) {
            $this->gadget->session->push(
                $objHook->getMessage(),
                RESPONSE_ERROR,
                'Comments',
                $post
            );
            Jaws_Header::Referrer();
        }

        $reference = $objHook->Execute($post['requested_action'], $post['reference']);
        if (empty($reference)) {
            $this->gadget->session->push(
                $this::t('ERROR_REFERENCE_EXISTS'),
                RESPONSE_ERROR,
                'Comments',
                $post
            );
            Jaws_Header::Referrer();
        }

        $res = $this->gadget->model->loadAdmin('Comments')->InsertComment(
            $post['requested_gadget'], $post['requested_action'], $post['reference'], 
            $reference['reference_title'], $reference['reference_link'], $post['name'],
            $post['email'], $post['url'], $post['message'], $permalink, $status, $post['is_private']
        );
        if (Jaws_Error::isError($res)) {
            $this->gadget->session->push(
                $res->getMessage(),
                RESPONSE_ERROR,
                'Comments',
                $post
            );
        } else {
            $this->EmailComment($reference, $post['message']);
            $this->gadget->session->push($this::t('MESSAGE_SENT'), RESPONSE_NOTICE, 'Comments');
        }

        return Jaws_Header::Location($reference['reference_link']);
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
        $site_url   = $this->app->getSiteURL('/');
        $site_name  = $this->gadget->registry->fetch('site_name', 'Settings');

        $tpl = $this->gadget->template->load('EmailComment.html');
        $tpl->SetBlock('notification');
        $tpl->SetVariable('comment', $message);
        $tpl->SetVariable('lbl_url', Jaws::t('URL'));

        $tpl->SetVariable('url',   $reference['reference_link']);
        $tpl->SetVariable('title', $reference['reference_title']);
        $tpl->SetVariable('site-name', $site_name);
        $tpl->SetVariable('site-url',  $site_url);
        $tpl->ParseBlock('notification');
        $template = $tpl->Get();

        $ObjMail = Jaws_Mail::getInstance();
        $ObjMail->SetFrom();
        if (empty($reference['author_email'])) {
            $ObjMail->AddRecipient('', 'to');
        } else {
            $ObjMail->AddRecipient($reference['author_email']);
            $ObjMail->AddRecipient('', 'cc');
        }

        $ObjMail->SetSubject($this::t('COMMENT_NOTIFICATION', $reference['reference_title']));
        $ObjMail->SetBody($template, array('format' => 'html'));
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
        $site_url   = $this->app->getSiteURL('/');
        $site_name  = $this->gadget->registry->fetch('site_name', 'Settings');

        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
        Jaws_Translate::getInstance()->LoadTranslation('Global', JAWS_COMPONENT_OTHERS, $site_language);
        Jaws_Translate::getInstance()->LoadTranslation('Comments', JAWS_COMPONENT_GADGET, $site_language);

        $tpl = $this->gadget->template->load('EmailReply.html');
        $tpl->SetBlock('notification');
        $tpl->SetVariable('lbl_message',  $this::t('MESSAGE|'. $site_language));
        $tpl->SetVariable('message',      $message);
        $tpl->SetVariable('replier',      $this::t('REPLY_BY|'. $site_language, $replier));
        $tpl->SetVariable('lbl_reply',    $this::t('REPLY|'. $site_language));
        $tpl->SetVariable('reply',        $reply);
        $tpl->SetVariable('site_name',    $site_name);
        $tpl->SetVariable('site_url',     $site_url);

        $tpl->ParseBlock('notification');
        $template = $tpl->Get();

        $ObjMail = Jaws_Mail::getInstance();
        $ObjMail->SetFrom();
        if (empty($email)) {
            $ObjMail->AddRecipient('', 'to');
        } else {
            $ObjMail->AddRecipient($email);
            $ObjMail->AddRecipient('', 'cc');
        }
        $ObjMail->SetSubject($this::t('YOU_GET_REPLY|'.$site_language));
        $ObjMail->SetBody($template, array('format' => 'html'));
        return $ObjMail->send();
    }

}