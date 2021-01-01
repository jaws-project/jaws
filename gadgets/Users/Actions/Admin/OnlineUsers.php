<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 */
class Users_Actions_Admin_OnlineUsers extends Users_Actions_Admin_Default
{
    /**
     * Builds group administration UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function OnlineUsers()
    {
        $this->AjaxMe('script.js');
        $this->gadget->define('confirmDelete', Jaws::t('CONFIRM_DELETE'));
        $this->gadget->define('datagridNoItems', Jaws::t('NOTFOUND'));
        $this->gadget->define('wrongPassword', $this::t('MYACCOUNT_PASSWORDS_DONT_MATCH'));
        $this->gadget->define('LANGUAGE', array(
            'username'=> $this::t('USERS_USERNAME'),
            'nickname'=> $this::t('USERS_NICKNAME'),
            'superadmin'=> $this::t('ONLINE_ADMIN'),
            'ip'=> Jaws::t('IP'),
            'session_type'=> $this::t('ONLINE_SESSION_TYPE'),
            'last_activetime'=> $this::t('ONLINE_LAST_ACTIVETIME'),
            'yes'=> Jaws::t('YES'),
            'no'=> Jaws::t('NO'),
            'delete'=> Jaws::t('DELETE'),
        ));
        $assigns = array();
        $assigns['menubar'] =  empty($menubar)? $this->MenuBar('OnlineUsers') : $menubar;

        $assigns['session_status_items'] = array(
            1 => $this::t('ONLINE_FILTER_SESSION_STATUS_ACTIVE'),
            0 => $this::t('ONLINE_FILTER_SESSION_STATUS_INACTIVE'),
        );
        $assigns['membership_items'] = array(
            1 => $this::t('ONLINE_FILTER_MEMBERSHIP_MEMBERS'),
            0 => $this::t('ONLINE_FILTER_MEMBERSHIP_ANONYMOUS'),
        );
        $assigns['session_types'] = $this->GetSessionTypes();

        return $this->gadget->template->xLoadAdmin('OnlineUsers.html')->render($assigns);
    }

    /**
     * Prepares list of GetOnlineUsers for datagrid
     *
     * @access  public
     * @return  array  Grid data
     */
    function GetOnlineUsers()
    {
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );
        $filters = array();
        $filters['active'] = ($post['filters']['filter_active'] == '-1')? null : (bool)$post['filters']['filter_active'];
        $filters['logged'] = ($post['filters']['filter_logged'] == '-1')? null : (bool)$post['filters']['filter_logged'];
        $filters['type'] = ($post['filters']['filter_session_type'] == '-1')? null : $post['filters']['filter_session_type'];

        $sessions = $this->app->session->getSessions(
            $filters['logged'],
            $filters['active'],
            $filters['type'],
            $post['limit'],
            $post['offset']
        );
        if (Jaws_Error::IsError($sessions)) {
            return $this->gadget->session->response($sessions->GetMessage(), RESPONSE_ERROR);
        }

        $objDate = Jaws_Date::getInstance();
        foreach ($sessions as &$session) {
            if (empty($session['username'])) {
                $session['username'] = $this::t('ONLINE_ANONY');
            } else {
                $uProfile =& Piwi::CreateWidget(
                    'Link',
                    $session['username'],
                    $this->gadget->urlMap('Profile',  array('user' => $session['username']))
                );
                $session['username'] = $uProfile->Get();
            }
            $session['superadmin'] = $session['superadmin']? Jaws::t('YES') : Jaws::t('NO');
            $session['ip'] = "<abbr title='{$session['agent_text']}'>".
                $session['proxy']. '('. $session['client']. ")</abbr>";
            if ($session['online']) {
                $session['last_activetime'] = "<label class='lastactive' title='".$this::t('ONLINE_ACTIVE')."'>".
                    $objDate->Format($session['update_time'], 'Y-m-d H:i')."</label>";
            } else {
                $session['last_activetime'] = "<s class='lastactive' title='".$this::t('ONLINE_INACTIVE')."'>".
                    $objDate->Format($session['update_time'], 'Y-m-d H:i')."</s>";
            }
        }

        $sessionsCount = $this->app->session->getSessionsCount(
            $filters['logged'],
            $filters['active'],
            $filters['type']
        );
        if (Jaws_Error::IsError($sessionsCount)) {
            return $this->gadget->session->response($sessionsCount->GetMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => $sessionsCount,
                'records' => $sessions
            )
        );
    }

    /**
     * Get Session Types
     *
     * @access  public
     * @return  array Array with the session type names.
     */
    function GetSessionTypes()
    {
        $result = array();
        $path = ROOT_JAWS_PATH. 'include/Jaws/Session/';
        $adr = scandir($path);
        foreach ($adr as $file) {
            if (substr($file, -4) == '.php') {
                $result[$file] = substr($file, 0, -4);
            }
        }
        sort($result);
        return $result;
    }
}