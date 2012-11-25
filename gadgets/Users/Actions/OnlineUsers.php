<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Actions_OnlineUsers extends Jaws_GadgetHTML
{
    /**
     * Show online user's list
     *
     * @access  public
     * @param   string  Optional username
     * @return  string  XHTML template content
     */
    function OnlineUsers()
    {
        // Load the template
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('OnlineUsers.html');
        $tpl->SetBlock('OnlineUsers');
        $tpl->SetVariable('title', _t('USERS_LAYOUT_ONLINE_USERS'));

        $sessions = $GLOBALS['app']->Session->GetSessions();
        foreach($sessions as $session) {
            $tpl->SetBlock('OnlineUsers/users');

            $uProfile =& Piwi::CreateWidget(
                'Link',
                $session['username'],
                $this->GetURLFor('Profile',  array('user' => $session['username']))
            );

            $tpl->SetVariable('username', $uProfile->Get());
            $tpl->ParseBlock('OnlineUsers/users');

        }

        $tpl->ParseBlock('OnlineUsers');
        return $tpl->Get();
    }

    /**
     * Show online user's statistics
     *
     * @access  public
     * @param   string  Optional username
     * @return  string  XHTML template content
     */
    function OnlineStatistics()
    {

        // Load the template
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('OnlineUsers.html');
        $tpl->SetBlock('OnlineStatistics');
        $tpl->SetVariable('title', _t('USERS_LAYOUT_ONLINE_USERS'));
        $tpl->SetVariable('registered_users_label', _t('USERS_ONLINE_REGISTERED_USERS_COUNT'));
        $tpl->SetVariable('guest_users_label', _t('USERS_ONLINE_GUEST_USERS_COUNT'));

        $sessions = $GLOBALS['app']->Session->GetSessions();
        $registeredCount = 0;
        $guestCount = 0;
        foreach ($sessions as $session) {
            if (empty($session['username'])) {
                $guestCount++;
            } else {
                $registeredCount++;
            }
        }

        $tpl->SetVariable('registered_users', $registeredCount);
        $tpl->SetVariable('guest_users', $guestCount);

        $tpl->ParseBlock('OnlineStatistics');
        return $tpl->Get();
    }


    /**
     * Get LatestRegisteredUsers action params(number of latest registered users)
     *
     * @access  public
     * @return  array list of Profile action params(list of numbers)
     */
    function LatestRegisteredUsersLayoutParams()
    {
        $result = array();
        $params[5] = _t('USERS_LATEST_N_REGISTERED_USERS', 5);
        $params[10] = _t('USERS_LATEST_N_REGISTERED_USERS', 10);
        $params[20] = _t('USERS_LATEST_N_REGISTERED_USERS', 20);

        $result[] = array(
            'value' => $params
        );

        return $result;
    }

    /**
     * Show latest registered user
     *
     * @access  public
     * @param   string  Optional username
     * @return  string  XHTML template content
     */
    function LatestRegisteredUsers($limit)
    {
        // Load the template
        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('OnlineUsers.html');
        $tpl->SetBlock('LatestRegisteredUser');
        $tpl->SetVariable('title', _t('USERS_LAYOUT_LATEST_REGISTERED_USERS'));
        $tpl->SetVariable('registered_users_label', _t('USERS_ONLINE_REGISTERED_USERS_COUNT'));
        $tpl->SetVariable('guest_users_label', _t('USERS_ONLINE_GUEST_USERS_COUNT'));

        require_once JAWS_PATH . 'include/Jaws/User.php';
        $usrModel = new Jaws_User;
        $users = $usrModel->GetUsers(false, null, 1, '', '[id] DESC', $limit);
        foreach($users as $user) {
            $tpl->SetBlock('LatestRegisteredUser/users');

            $uProfile =& Piwi::CreateWidget(
                'Link',
                $user['username'],
                $this->GetURLFor('Profile',  array('user' => $user['username']))
            );

            $tpl->SetVariable('username', $uProfile->Get());
            $tpl->ParseBlock('LatestRegisteredUser/users');
        }

        $tpl->ParseBlock('LatestRegisteredUser');
        return $tpl->Get();
    }

}