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
     * Builds online users datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function OnlineUsersDataGrid()
    {
        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->pageBy(50);
        $datagrid->useMultipleSelection();
        $datagrid->SetID('onlineusers_datagrid');
        $column1 = Piwi::CreateWidget('Column', Jaws::t('USERNAME'));
        $column1->SetStyle('width:100px;');
        $datagrid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', $this::t('USERS_NICKNAME'), false, null);
        $datagrid->AddColumn($column2);
        $column3 = Piwi::CreateWidget('Column', $this::t('ONLINE_ADMIN'), false, null);
        $column3->SetStyle('width:80px;');
        $datagrid->AddColumn($column3);
        $column4 = Piwi::CreateWidget('Column', Jaws::t('IP'), false, null);
        $column4->SetStyle('width:100px;');
        $datagrid->AddColumn($column4);
        $column5 = Piwi::CreateWidget('Column', $this::t('ONLINE_SESSION_TYPE'), false, null);
        $column5->SetStyle('width:100px;');
        $datagrid->AddColumn($column5);
        $column6 = Piwi::CreateWidget('Column', $this::t('ONLINE_LAST_ACTIVETIME'), false, null);
        $column6->SetStyle('width:128px;');
        $datagrid->AddColumn($column6);
        $datagrid->SetStyle('margin-top: 0px; width: 100%;');

        return $datagrid->Get();
    }

    /**
     * Prepares list of online users for datagrid
     *
     * @access  public
     * @return  array   Grid data
     */
    function GetOnlineUsers()
    {
        $filters = $this->gadget->request->fetch(array('active', 'logged', 'session_type', 'offset'), 'post');
        $filters['active'] = ($filters['active'] == '-1')? null : (bool)$filters['active'];
        $filters['logged'] = ($filters['logged'] == '-1')? null : (bool)$filters['logged'];
        $filters['type'] = ($filters['session_type'] == '-1')? null : $filters['session_type'];
        $filters['offset'] = (int)$filters['offset'];

        $sessions = $this->app->session->getSessions(
            $filters['logged'], $filters['active'], $filters['type'], 50, $filters['offset']);
        if (Jaws_Error::IsError($sessions)) {
            return array();
        }

        $retData = array();
        $objDate = Jaws_Date::getInstance();
        foreach ($sessions as $session) {
            $usrData = array();
            $usrData['__KEY__'] = $session['id'];
            if (empty($session['username'])) {
                $usrData['username'] = $this::t('ONLINE_ANONY');
            } else {
                $uProfile =& Piwi::CreateWidget(
                    'Link',
                    $session['username'],
                    $this->gadget->urlMap('Profile',  array('user' => $session['username']))
                );
                $usrData['username'] = $uProfile->Get();
            }
            $usrData['nickname'] = $session['nickname'];
            $usrData['superadmin'] = $session['superadmin']? Jaws::t('YES') : Jaws::t('NO');
            $usrData['ip'] = "<abbr title='{$session['agent_text']}'>".
                $session['proxy']. '('. $session['client']. ")</abbr>";
            $usrData['type'] = $session['type'];
            if ($session['online']) {
                $usrData['last_activetime'] = "<label class='lastactive' title='".$this::t('ONLINE_ACTIVE')."'>".
                    $objDate->Format($session['update_time'], 'Y-m-d H:i')."</label>";
            } else {
                $usrData['last_activetime'] = "<s class='lastactive' title='".$this::t('ONLINE_INACTIVE')."'>".
                    $objDate->Format($session['update_time'], 'Y-m-d H:i')."</s>";
            }

            $retData[] = $usrData;
        }

        return $retData;
    }

    /**
     * Get online users count
     *
     * @access  public
     * @return  int  Total of online users
     */
    function GetOnlineUsersCount()
    {
        $filters = $this->gadget->request->fetchAll('post');
        $filters['active'] = ($filters['active'] == '-1')? null : (bool)$filters['active'];
        $filters['logged'] = ($filters['logged'] == '-1')? null : (bool)$filters['logged'];
        $filters['type'] = ($filters['session_type'] == '-1')? null : $filters['session_type'];
        $sessionsCount = $this->app->session->getSessionsCount(
            $filters['logged'], $filters['active'], $filters['type']);
        if (Jaws_Error::IsError($sessionsCount)) {
            return array();
        }

        return $sessionsCount;
    }

    /**
     * Builds online users admin UI
     *
     * @access  public
     * @return  string  XHTML content
     */
    function OnlineUsers()
    {
        $this->gadget->CheckPermission('ManageOnlineUsers');
        $this->AjaxMe('script.js');
        // set default value of javascript variables
        $this->gadget->define('confirmThrowout',   $this::t('ONLINE_CONFIRM_THROWOUT'));
        $this->gadget->define('confirmBlockIP',    $this::t('ONLINE_CONFIRM_BLOCKIP'));
        $this->gadget->define('confirmBlockAgent', $this::t('ONLINE_CONFIRM_BLOCKAGENT'));

        $tpl = $this->gadget->template->loadAdmin('OnlineUsers.html');
        $tpl->SetBlock('OnlineUsers');
        $tpl->SetVariable('menubar', $this->MenuBar('OnlineUsers'));

        // Active
        $active =& Piwi::CreateWidget('Combo', 'active');
        $active->setID('filter_active');
        $active->AddOption(Jaws::t('ALL'), -1, false);
        $active->AddOption($this::t('ONLINE_FILTER_SESSION_STATUS_ACTIVE'), 1);
        $active->AddOption($this::t('ONLINE_FILTER_SESSION_STATUS_INACTIVE'), 0);
        $active->AddEvent(ON_CHANGE, "Jaws_Gadget.getInstance('Users').searchOnlineUsers();");
        $active->SetDefault(-1);
        $tpl->SetVariable('filter_active', $active->Get());
        $tpl->SetVariable('lbl_filter_active', $this::t('ONLINE_FILTER_SESSION_STATUS'));

        // Logged
        $logged =& Piwi::CreateWidget('Combo', 'logged');
        $logged->setID('filter_logged');
        $logged->AddOption(Jaws::t('ALL'), -1, false);
        $logged->AddOption($this::t('ONLINE_FILTER_MEMBERSHIP_MEMBERS'), 1);
        $logged->AddOption($this::t('ONLINE_FILTER_MEMBERSHIP_ANONYMOUS'), 0);
        $logged->AddEvent(ON_CHANGE, "Jaws_Gadget.getInstance('Users').searchOnlineUsers();");
        $logged->SetDefault(-1);
        $tpl->SetVariable('filter_logged', $logged->Get());
        $tpl->SetVariable('lbl_filter_logged', $this::t('ONLINE_FILTER_MEMBERSHIP'));

        // Session type
        $logged =& Piwi::CreateWidget('Combo', 'session_type');
        $logged->setID('filter_session_type');
        $logged->AddOption(Jaws::t('ALL'), -1, false);
        $sessionTypes = $this->GetSessionTypes();
        if (count($sessionTypes) > 0) {
            foreach ($sessionTypes as $type) {
                $logged->AddOption($type, $type);
            }
        }
        $logged->AddEvent(ON_CHANGE, "Jaws_Gadget.getInstance('Users').searchOnlineUsers();");
        $logged->SetDefault(-1);
        $tpl->SetVariable('filter_session_type', $logged->Get());
        $tpl->SetVariable('lbl_filter_session_type', $this::t('ONLINE_SESSION_TYPE'));

        // Datagrid
        $tpl->SetVariable('online_users_datagrid', $this->OnlineUsersDataGrid());

        // Actions
        $actions =& Piwi::CreateWidget('Combo', 'online_users_actions');
        $actions->SetID('online_users_actions');
        $actions->SetTitle(Jaws::t('ACTIONS'));
        $actions->AddOption('&nbsp;', '');
        $actions->AddOption(Jaws::t('DELETE'), 'delete');
        $actions->AddOption($this::t('ONLINE_BLOCKING_IP'), 'block_ip');
        $actions->AddOption($this::t('ONLINE_BLOCKING_AGENT'), 'block_agent');
        $tpl->SetVariable('actions_combo', $actions->Get());

        $btnExecute =& Piwi::CreateWidget('Button', 'executeOnlineUsersAction', '', STOCK_YES);
        $btnExecute->AddEvent(
            ON_CLICK,
            "Jaws_Gadget.getInstance('Users').onlineUsersDGAction($('#online_users_actions'));"
        );
        $tpl->SetVariable('btn_execute', $btnExecute->Get());

        $tpl->ParseBlock('OnlineUsers');
        return $tpl->Get();
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