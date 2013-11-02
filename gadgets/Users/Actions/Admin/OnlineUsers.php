<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
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
        $gridBox =& Piwi::CreateWidget('VBox');
        $gridBox->SetID('tags_box');
        $gridBox->SetStyle('width: 100%;');

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->pageBy(1024);
        $datagrid->useMultipleSelection();
        $datagrid->SetID('onlineusers_datagrid');
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_USERNAME'));
        $column1->SetStyle('width:100px;');
        $datagrid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('USERS_USERS_NICKNAME'), false, null);
        $datagrid->AddColumn($column2);
        $column3 = Piwi::CreateWidget('Column', _t('USERS_ONLINE_ADMIN'), false, null);
        $column3->SetStyle('width:80px;');
        $datagrid->AddColumn($column3);
        $column4 = Piwi::CreateWidget('Column', _t('GLOBAL_IP'), false, null);
        $column4->SetStyle('width:100px;');
        $datagrid->AddColumn($column4);
        $column5 = Piwi::CreateWidget('Column', _t('USERS_ONLINE_LAST_ACTIVETIME'), false, null);
        $column5->SetStyle('width:128px;');
        $datagrid->AddColumn($column5);
        $datagrid->SetStyle('margin-top: 0px; width: 100%;');

        //Tools
        $gridForm =& Piwi::CreateWidget('Form');
        $gridForm->SetID('tags_form');
        $gridForm->SetStyle('float: right');

        $gridFormBox =& Piwi::CreateWidget('HBox');

        $actions =& Piwi::CreateWidget('Combo', 'online_users_actions');
        $actions->SetID('ou_actions_combo');
        $actions->SetTitle(_t('GLOBAL_ACTIONS'));
        $actions->AddOption('&nbsp;', '');
        $actions->AddOption(_t('GLOBAL_DELETE'), 'delete');
        $actions->AddOption(_t('USERS_ONLINE_BLOCKING_IP'), 'block_ip');
        $actions->AddOption(_t('USERS_ONLINE_BLOCKING_AGENT'), 'block_agent');

        $execute =& Piwi::CreateWidget('Button', 'executeOnlineUsersAction', '',
            STOCK_YES);
        $execute->AddEvent(ON_CLICK, "javascript:onlineUsersDGAction(document.getElementById('ou_actions_combo'));");

        $gridFormBox->Add($actions);
        $gridFormBox->Add($execute);
        $gridForm->Add($gridFormBox);

        //Pack everything
        $gridBox->Add($datagrid);
        $gridBox->Add($gridForm);

        return $gridBox->Get();
    }

    /**
     * Prepares list of online users for datagrid
     *
     * @access  public
     * @param   array   $filters    Grid filters
     * @return  array   Grid data
     */
    function GetOnlineUsers($filters)
    {
        $sessions = $GLOBALS['app']->Session->GetSessions(false);
        if (Jaws_Error::IsError($sessions)) {
            return array();
        }

        $retData = array();
        $objDate = $GLOBALS['app']->loadDate();

        foreach ($sessions as $session) {
            $usrData = array();
            $usrData['__KEY__']      = $session['sid'];
            if (empty($session['username'])) {
                $usrData['username'] = _t('USERS_ONLINE_ANONY');
            } else {
                $uProfile =& Piwi::CreateWidget(
                    'Link',
                    $session['username'],
                    $this->gadget->urlMap('Profile',  array('user' => $session['username']))
                );
                $usrData['username'] = $uProfile->Get();
            }
            $usrData['nickname'] = $session['nickname'];
            $usrData['superadmin'] = $session['superadmin']? _t('GLOBAL_YES') : _t('GLOBAL_NO');
            $usrData['ip'] = "<abbr title='{$session['agent']}'>". long2ip($session['ip']). "</abbr>";
            if ($session['online']) {
                $usrData['last_activetime'] = "<label class='lastactive' title='"._t('USERS_ONLINE_ACTIVE')."'>".
                    $objDate->Format($session['updatetime'], 'Y-m-d H:i')."</label>";
            } else {
                $usrData['last_activetime'] = "<s class='lastactive' title='"._t('USERS_ONLINE_INACTIVE')."'>".
                    $objDate->Format($session['updatetime'], 'Y-m-d H:i')."</s>";
            }

            $retData[] = $usrData;
        }

        return $retData;
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

        $tpl = $this->gadget->loadAdminTemplate('OnlineUsers.html');
        $tpl->SetBlock('OnlineUsers');

        // Session Status
        $filterSession =& Piwi::CreateWidget('Combo', 'filter_session_status');
        $filterSession->AddOption(_t('GLOBAL_ALL'), -1, false);
        $filterSession->AddOption(_t('USERS_ONLINE_FILTER_SESSION_STATUS_ACTIVE'), 1);
        $filterSession->AddOption(_t('USERS_ONLINE_FILTER_SESSION_STATUS_INACTIVE'), 0);
        $filterSession->AddEvent(ON_CHANGE, "javascript: searchOnlineUsers();");
        $filterSession->SetDefault(-1);
        $tpl->SetVariable('filter_session_status', $filterSession->Get());
        $tpl->SetVariable('lbl_filter_session_status', _t('USERS_ONLINE_FILTER_SESSION_STATUS'));

        // Membership
        $filterMembership =& Piwi::CreateWidget('Combo', 'filter_membership');
        $filterMembership->AddOption(_t('GLOBAL_ALL'), -1, false);
        $filterMembership->AddOption(_t('USERS_ONLINE_FILTER_MEMBERSHIP_MEMBERS'), 1);
        $filterMembership->AddOption(_t('USERS_ONLINE_FILTER_MEMBERSHIP_ANONYMOUS'), 0);
        $filterMembership->AddEvent(ON_CHANGE, "javascript: searchOnlineUsers();");
        $filterMembership->SetDefault(-1);
        $tpl->SetVariable('filter_membership', $filterMembership->Get());
        $tpl->SetVariable('lbl_filter_membership', _t('USERS_ONLINE_FILTER_MEMBERSHIP'));

        $tpl->SetVariable('online_users_datagrid', $this->OnlineUsersDataGrid());
        $tpl->SetVariable('menubar', $this->MenuBar('OnlineUsers'));

        $tpl->SetVariable('confirmThrowout',   _t('USERS_ONLINE_CONFIRM_THROWOUT'));
        $tpl->SetVariable('confirmBlockIP',    _t('USERS_ONLINE_CONFIRM_BLOCKIP'));
        $tpl->SetVariable('confirmBlockAgent', _t('USERS_ONLINE_CONFIRM_BLOCKAGENT'));
        $tpl->ParseBlock('OnlineUsers');

        return $tpl->Get();
    }

}