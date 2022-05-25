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
        $this->gadget->CheckPermission('ManageOnlineUsers');
        $this->AjaxMe('script.js');
        $this->gadget->define('confirmThrowOut',    $this::t('ONLINE_CONFIRM_THROWOUT'));
        $this->gadget->define('confirmBlockIP',     $this::t('ONLINE_CONFIRM_BLOCKIP'));
        $this->gadget->define('confirmBlockAgent',  $this::t('ONLINE_CONFIRM_BLOCKAGENT'));
        $this->gadget->define('datagridNoItems',    Jaws::t('NOTFOUND'));
        $this->gadget->define('LANGUAGE', array(
            'username'=> $this::t('USERS_USERNAME'),
            'nickname'=> $this::t('USERS_NICKNAME'),
            'superadmin'=> $this::t('ONLINE_ADMIN'),
            'ip'=> Jaws::t('IP'),
            'session_type'=> $this::t('ONLINE_SESSION_TYPE'),
            'last_activetime'=> $this::t('ONLINE_LAST_ACTIVETIME'),
            'yes'=> Jaws::t('YESS'),
            'no'=> Jaws::t('NOO'),
            'active'=> $this::t('ONLINE_ACTIVE'),
            'inactive'=> $this::t('ONLINE_INACTIVE'),
            'anonymous'=> $this::t('ONLINE_ANONY'),
            'delete'=> Jaws::t('DELETE'),
            'block_ip'=> $this::t('ONLINE_BLOCKING_IP'),
            'block_agent'=> $this::t('ONLINE_BLOCKING_AGENT'),
        ));
        $assigns = array();
        $assigns['menubar'] =  empty($menubar)? $this->MenuBar('OnlineUsers') : $menubar;

        $assigns['session_status_items'] = array(
            0 => $this::t('ONLINE_FILTER_SESSION_STATUS_INACTIVE'),
            1 => $this::t('ONLINE_FILTER_SESSION_STATUS_ACTIVE'),
        );
        $assigns['membership_items'] = array(
            0 => $this::t('ONLINE_FILTER_MEMBERSHIP_ANONYMOUS'),
            1 => $this::t('ONLINE_FILTER_MEMBERSHIP_MEMBERS'),
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
            if (!empty($session['username'])) {
                $session['user_profile_url'] = $this->gadget->urlMap('Profile',  array('user' => $session['username']));
            }
            $session['last_activetime'] =  $objDate->Format($session['update_time'], 'Y-m-d H:i');
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
     * Delete the session(s)
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteSessions()
    {
        $this->gadget->CheckPermission('ManageOnlineUsers');
        $sessionIds = $this->gadget->request->fetch('ids:array', 'post');

        foreach ($sessionIds as $sid) {
            if (!$this->app->session->delete($sid)) {
                return $this->gadget->session->response(
                    $this::t('ONLINE_SESSION_NOT_DELETED'),
                    RESPONSE_ERROR
                );
            }
        }
        return $this->gadget->session->response(
            $this::t('ONLINE_SESSION_DELETED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Block IP address
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function IPsBlock()
    {
        $this->gadget->CheckPermission('ManageOnlineUsers');
        $this->gadget->CheckPermission('ManageIPs');
        $sessionIds = $this->gadget->request->fetch('ids:array', 'post');
        $mPolicy = Jaws_Gadget::getInstance('Policy')->model->loadAdmin('IP');

        foreach ($sessionIds as $sid) {
            $session = $this->app->session->getSession($sid);

            if (!$mPolicy->AddIPRange($session['ip'], null, true)) {
                return $this->gadget->session->response(
                    Jaws_Gadget::t('POLICY.RESPONSE_IP_NOT_ADDED'),
                    RESPONSE_ERROR
                );
            }
        }
        return $this->gadget->session->response(
            Jaws_Gadget::t('POLICY.RESPONSE_IP_ADDED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Block agents
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AgentsBlock()
    {
        $this->gadget->CheckPermission('ManageOnlineUsers');
        $this->gadget->CheckPermission('ManageAgents');
        $sessionIds = $this->gadget->request->fetch('ids:array', 'post');
        $mPolicy = Jaws_Gadget::getInstance('Policy')->model->loadAdmin('Agent');

        foreach ($sessionIds as $sid) {
            $session = $this->app->session->getSession($sid);

            if (!$mPolicy->AddAgent($session['agent'], true)) {
                return $this->gadget->session->response(
                    Jaws_Gadget::t('POLICY.RESPONSE_AGENT_NOT_ADDEDD'),
                    RESPONSE_ERROR
                );
            }
        }
        return $this->gadget->session->response(
            Jaws_Gadget::t('POLICY.RESPONSE_AGENT_ADDED'),
            RESPONSE_NOTICE
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