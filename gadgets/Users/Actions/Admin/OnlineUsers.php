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
class Users_Actions_Admin_OnlineUsers extends UsersAdminHTML
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
        $datagrid->pageBy(1024);
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
        $action_column = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), false, null);
        $action_column->SetStyle('width:60px;');
        $datagrid->AddColumn($action_column);
        $datagrid->SetStyle('margin-top: 0px; width: 100%;');
        return $datagrid->Get();
    }

    /**
     * Prepares list of online users for datagrid
     *
     * @access  public
     * @return  array  Grid data
     */
    function GetOnlineUsers()
    {
        $sessions = $GLOBALS['app']->Session->GetSessions(false);
        if (Jaws_Error::IsError($sessions)) {
            return array();
        }

        $retData = array();
        $objDate = $GLOBALS['app']->loadDate();

        foreach ($sessions as $session) {
            $usrData = array();
            if (empty($session['username'])) {
                $usrData['username'] = _t('USERS_ONLINE_ANONY');
            } else {
                $uProfile =& Piwi::CreateWidget(
                    'Link',
                    $session['username'],
                    $this->GetURLFor('Profile',  array('user' => $session['username']))
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

            $link =& Piwi::CreateWidget(
                'Link',
                _t('GLOBAL_DELETE'),
                "javascript: deleteSession(this, '{$session['sid']}');",
                STOCK_DELETE);
            $actions = $link->Get() . '&nbsp;';

            if ($this->GetPermission('ManageIPs', 'Policy')) {
                $link =& Piwi::CreateWidget(
                    'Link',
                    _t('USERS_ONLINE_BLOCKING_IP'),
                    "javascript: ipBlock(this, '" . long2ip($session['ip']) . "');",
                    STOCK_STOP);
                $actions .= $link->Get() . '&nbsp;';
            }

            if ($this->GetPermission('ManageAgents', 'Policy')) {
                $link =& Piwi::CreateWidget(
                    'Link',
                    _t('USERS_ONLINE_BLOCKING_AGENT'),
                    "javascript: agentBlock(this, '{$session['agent']}');",
                    STOCK_STOP);
                $actions .= $link->Get();
            }

            $usrData['actions'] = $actions;
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
        $this->CheckPermission('ManageOnlineUsers');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('Admin/OnlineUsers.html');
        $tpl->SetBlock('OnlineUsers');

        $tpl->SetVariable('online_users_datagrid', $this->OnlineUsersDataGrid());
        $tpl->SetVariable('menubar', $this->MenuBar('OnlineUsers'));

        $tpl->SetVariable('confirmThrowout',   _t('USERS_ONLINE_CONFIRM_THROWOUT'));
        $tpl->SetVariable('confirmBlockIP',    _t('USERS_ONLINE_CONFIRM_BLOCKIP'));
        $tpl->SetVariable('confirmBlockAgent', _t('USERS_ONLINE_CONFIRM_BLOCKAGENT'));
        $tpl->ParseBlock('OnlineUsers');

        return $tpl->Get();
    }

}