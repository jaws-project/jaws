<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
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
//        $datagrid->TotalRows($total);
        $datagrid->SetID('onlineusers_datagrid');

        $column1 = Piwi::CreateWidget('Column', _t('USERS_USERS_NICKNAME'), null, false);
        $datagrid->AddColumn($column1);

        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_USERNAME'), null, false);
        $datagrid->AddColumn($column2);

        $column3 = Piwi::CreateWidget('Column', _t('USERS_USERS_TYPE_SUPERADMIN'), null, false);
        $datagrid->AddColumn($column3);

        $column4 = Piwi::CreateWidget('Column', _t('GLOBAL_STATUS'), null, false);
        $datagrid->AddColumn($column4);

        $column5 = Piwi::CreateWidget('Column', _t('USERS_USERS_IP'), null, false);
        $datagrid->AddColumn($column5);

        $column6 = Piwi::CreateWidget('Column', _t('USERS_USERS_AGENT'), null, false);
        $datagrid->AddColumn($column6);

        $column7 = Piwi::CreateWidget('Column', _t('USERS_USERS_LOGIN_TIME'), null, false);
        $datagrid->AddColumn($column7);


        $action_column = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $action_column->SetStyle('width: 120px;');
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
        $onlineUsers = $GLOBALS['app']->Session->GetSessions();
        if (Jaws_Error::IsError($onlineUsers)) {
            return array();
        }

        $retData = array();
        $objDate = $GLOBALS['app']->loadDate();

        foreach ($onlineUsers as $onlineUser) {
            $usrData = array();
            $usrData['nickname'] = $onlineUser['nickname'];
            $usrData['username'] = $onlineUser['username'];
            $usrData['type'] = $onlineUser['type'] == true ? _t('GLOBAL_YES') : _t('GLOBAL_NO');
            $usrData['status'] = $onlineUser['updatetime'] < (time() - ($GLOBALS['app']->Registry->Get('/policy/session_idle_timeout') * 60)) ?
                _t('USERS_USER_STATUS_INACTIVE') : _t('USERS_USER_STATUS_ACTIVE');
            $usrData['ip'] = long2ip($onlineUser['ip']);
            $usrData['agent'] = $onlineUser['agent'];
            $usrData['logintime'] = $objDate->Format($onlineUser['updatetime'], 'Y-m-d H:i:s');

            $actions = '';
            if ($this->GetPermission('ManageUsers')) {

                $link =& Piwi::CreateWidget('Link',
                    _t('GLOBAL_DISABLE'),
                    "javascript: disableUser(this, " . $onlineUser['sid'] . "," . $onlineUser['user'] . ");",
                    STOCK_DELETE);
                $actions .= $link->Get() . '&nbsp;';

                $link =& Piwi::CreateWidget('Link',
                    _t('GLOBAL_LOGOUT'),
                    "javascript: logoutUser(this, '" . $onlineUser['sid'] . "," . $onlineUser['user'] . "');",
                    STOCK_EXIT);
                $actions .= $link->Get() . '&nbsp;';
            }

            if ($this->GetPermission('ManageIPs', 'Policy')) {
                $link =& Piwi::CreateWidget('Link',
                    _t('USERS_USERS_IP_BLOCKING'),
                    "javascript: userIPBlock(this, '" . long2ip($onlineUser['ip']) . "');",
                    STOCK_STOP);
                $actions .= $link->Get() . '&nbsp;';
            }

            if ($this->GetPermission('ManageAgents', 'Policy')) {
                $link =& Piwi::CreateWidget('Link',
                    _t('USERS_USERS_AGENT_BLOCKING'),
                    "javascript: userAgentBlock(this, '" . $onlineUser['agent'] . "');",
                    STOCK_STOP);
                $actions .= $link->Get() . '&nbsp;';
            }


            $usrData['actions'] = $actions;
            $retData[] = $usrData;
        }

        return $retData;
    }


    /**
     * Builds admin online users UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function OnlineUsers()
    {
        $this->CheckPermission('ManageOnlineUsers');
        $this->AjaxMe('script.js');

        $tpl = new Jaws_Template('gadgets/Users/templates/');
        $tpl->Load('AdminOnlineUsers.html');
        $tpl->SetBlock('OnlineUsers');

        $tpl->SetVariable('online_users_datagrid', $this->OnlineUsersDataGrid());
        $tpl->SetVariable('menubar', $this->MenuBar('OnlineUsers'));

        $tpl->SetVariable('confirmUserDisable', _t('USERS_USER_CONFIRM_DISABLE'));
        $tpl->ParseBlock('OnlineUsers');

        return $tpl->Get();
    }

}