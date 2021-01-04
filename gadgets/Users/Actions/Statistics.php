<?php
/**
 * Users Core Gadget Admin
 *
 * @category   Gadget
 * @package    Users
 */
class Users_Actions_Statistics extends Jaws_Gadget_Action
{
    /**
     * Show online users list
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function OnlineUsers()
    {
        // Load the template
        $tpl = $this->gadget->template->load('Statistics.html');
        $tpl->SetBlock('OnlineUsers');
        $tpl->SetVariable('title', $this::t('ACTIONS_ONLINEUSERS'));

        $uniqueOnline = array();
        $sessions = $this->app->session->getSessions();
        if (!Jaws_Error::isError($sessions)) {
            foreach($sessions as $session) {
                if (!empty($session['username'])) {
                    $tpl->SetBlock('OnlineUsers/user');
                    if (!array_key_exists($session['user'], $uniqueOnline)) {
                        $uniqueOnline[$session['user']] = true;
                        $tpl->SetVariable('username', $session['username']);
                        $tpl->SetVariable('nickname', $session['nickname']);
                        $tpl->SetVariable('url_user', $this->gadget->urlMap('Profile',  array('user' => $session['username'])));
                        $tpl->ParseBlock('OnlineUsers/user');
                    }
                }
            }
        }

        if (empty($uniqueOnline)) {
            $tpl->SetBlock('OnlineUsers/no_online');
            $tpl->SetVariable('no_online', $this::t('ONLINE_NO_ONLINE'));
            $tpl->ParseBlock('OnlineUsers/no_online');
        }

        $tpl->ParseBlock('OnlineUsers');
        return $tpl->Get();
    }

    /**
     * Show online users statistics
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function OnlineStatistics()
    {
        // Load the template
        $tpl = $this->gadget->template->load('Statistics.html');
        $tpl->SetBlock('OnlineStatistics');
        $tpl->SetVariable('title', $this::t('ACTIONS_ONLINESTATISTICS'));
        $tpl->SetVariable('lbl_registered_users', $this::t('ONLINE_REGISTERED_COUNT'));
        $tpl->SetVariable('lbl_guests_users', $this::t('ONLINE_GUESTS_COUNT'));

        $sessions = $this->app->session->getSessions();
        $registered = count(array_filter(array_map(
            function ($sess) {
                return $sess['username'];
            },
            $sessions
        )));
        $tpl->SetVariable('registered_users', $registered);
        $tpl->SetVariable('guest_users', count($sessions) - $registered);

        $tpl->ParseBlock('OnlineStatistics');
        return $tpl->Get();
    }


    /**
     * Display latest registered users
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function LatestRegistered()
    {
        // Load the template
        $tpl = $this->gadget->template->load('Statistics.html');
        $tpl->SetBlock('LatestRegistered');
        $tpl->SetVariable('title', $this::t('ACTIONS_LATESTREGISTERED'));

        // latest registered users limit
        $limit = (int)$this->gadget->registry->fetch('latest_limit');
        $limit = empty($limit)? 10 : $limit;

        $users = $this->app->users->GetUsers(false, false, null, 1, '', 'id desc', $limit);
        foreach($users as $user) {
            $tpl->SetBlock('LatestRegistered/user');
            $tpl->SetVariable('username', $user['username']);
            $tpl->SetVariable('nickname', $user['nickname']);
            $tpl->SetVariable('url_user', $this->gadget->urlMap('Profile',  array('user' => $user['username'])));
            $tpl->ParseBlock('LatestRegistered/user');
        }

        $tpl->ParseBlock('LatestRegistered');
        return $tpl->Get();
    }

}