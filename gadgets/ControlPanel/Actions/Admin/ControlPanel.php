<?php
/**
 * ControlPanel Core Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     ControlPanel
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class ControlPanel_Actions_Admin_ControlPanel extends Jaws_Gadget_Action
{
    /**
     * Calls default action
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function Layout($ReqGadget, $ReqResult, $ReqGadgetVersion)
    {
        $GLOBALS['app']->Layout->Load('gadgets/ControlPanel/Templates', 'Layout.html');
        // Load ControlPanel header
        $GLOBALS['app']->Layout->Populate($ReqResult);

        $tpl = $GLOBALS['app']->Layout->_Template;
        $tpl->SetVariable('gadget', $ReqGadget);
        $tpl->SetVariable('gadget_version', $ReqGadgetVersion);
        $tpl->SetVariable('requested_gadget', strtolower($ReqGadget));
        $tpl->SetBlock('layout/login-info', false);
        $tpl->SetVariable('logged-in-as', _t('CONTROLPANEL_LOGGED_IN_AS'));
        $uInfo = $GLOBALS['app']->Session->GetAttributes('username', 'nickname', 'avatar', 'email');
        $tpl->SetVariable('username', $uInfo['username']);
        $tpl->SetVariable('nickname', $uInfo['nickname']);
        $tpl->SetVariable('email',    $uInfo['email']);
        $tpl->SetVariable('avatar',   $uInfo['avatar']);
        $tpl->SetVariable('site-url', $GLOBALS['app']->GetSiteURL());
        $tpl->SetVariable('view-site', _t('GLOBAL_VIEW_SITE'));

        if ($GLOBALS['app']->Session->GetPermission('Users', 'default_admin, EditAccountInformation')) {
            $uAccoun =& Piwi::CreateWidget('Link',
                                           $uInfo['nickname'],
                                           BASE_SCRIPT . '?gadget=Users&amp;action=MyAccount');
        } else {
            $uAccoun =& Piwi::CreateWidget('Label', $uInfo['nickname']);
        }

        $tpl->SetVariable('my-account', $uAccoun->Get());
        $tpl->SetVariable('logout', _t('GLOBAL_LOGOUT'));
        $tpl->SetVariable('logout-url', BASE_SCRIPT . '?gadget=Users&amp;action=Logout');
        $tpl->ParseBlock('layout/login-info');

        // Set the header items for each gadget and the response box
        if (isset($ReqGadget) && ($ReqGadget != 'ControlPanel')){
            $gInfo  = Jaws_Gadget::getInstance($ReqGadget);
            $docurl = null;
            if (!Jaws_Error::isError($gInfo)) {
                $docurl = $gInfo->GetDoc();
            }
            $gname = _t(strtoupper($ReqGadget) . '_TITLE');
            $tpl->SetBlock('layout/cptitle');
            $tpl->SetVariable('admin-script', BASE_SCRIPT);
            $tpl->SetVariable('cp-title', _t('GLOBAL_CONTROLPANEL'));
            $tpl->SetVariable('cp-title-separator', _t('GLOBAL_CONTROLPANEL_TITLE_SEPARATOR'));
            $tpl->SetVariable('title-name', $gname);
            $tpl->SetVariable('icon-gadget', 'gadgets/'.$ReqGadget.'/Resources/images/logo.png');
            $tpl->SetVariable('title-gadget', $ReqGadget);
            
            // help icon
            if (!empty($docurl) && !is_null($docurl)) {
                $tpl->SetBlock('layout/cptitle/documentation');
                $tpl->SetVariable('src', 'gadgets/ControlPanel/Resources/images/help.png');
                $tpl->SetVariable('alt', _t('GLOBAL_HELP'));
                $tpl->SetVariable('url', $docurl);
                $tpl->ParseBlock('layout/cptitle/documentation');
            }

            $tpl->ParseBlock('layout/cptitle');
        }

        $site_status = $this->gadget->registry->fetch('site_status', 'Settings');
        if ($site_status == 'disabled') {
            $tpl->SetBlock('layout/warning');
            $tpl->SetVariable('warning', _t('GLOBAL_WARNING_OFFLINE'));
            $tpl->ParseBlock('layout/warning');
        }

        $responses = $GLOBALS['app']->Session->PopLastResponse();
        if ($responses) {
            $tpl->SetVariable('response_text', $responses[0]['text']);
            $tpl->SetVariable('response_type', $responses[0]['type']);
        }

        return $GLOBALS['app']->Layout->Get();
    }

    /**
     * Calls default action
     *
     * @access  public
     * @return  string   XHTML template content
     */
    function DefaultAction()
    {
        $gadgetsections = array();
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgets = $cmpModel->GetGadgetsList(null, true, true);
        unset($gadgets['ControlPanel']);

        foreach ($gadgets as $gadget => $gInfo) {
            if ($this->gadget->GetPermission('default_admin', '', $gadget)) {
                $section = $gInfo['section'];
                if (!isset($gadgetsections[$section])) {
                    $gadgetsections[$section] = array();
                }

                $gadgetsections[$section][] = array('name'  => $gadget,
                                                    'tname' => $gInfo['title'],
                                                    'desc'  => $gInfo['description']);
            }
        }

        if ($this->gadget->registry->fetch('show_viewsite', 'Settings') == 'true') {
            $gadgetsections['general'][] = array('name'  => 'Index',
                                                 'tname' => _t('GLOBAL_VIEW_SITE'),
                                                 'desc'  => _t('GLOBAL_VIEW_SITE'));
        }

        // Load the template
        $tpl = $this->gadget->template->loadAdmin('ControlPanel.html');
        $this->AjaxMe('script.js');
        foreach ($gadgetsections as $section  => $gadgets) {
            $tpl->SetBlock('main');
            $tpl->SetVariable('title', _t('GLOBAL_GI_' . strtoupper($section)));
            foreach ($gadgets as $gadget) {
                $tpl->SetBlock('main/item');
                $tpl->SetVariable('name', $gadget['tname']);
                $tpl->SetVariable('desc', $gadget['desc']);
                if ($gadget['name'] === 'Index') {
                    $tpl->SetVariable('icon', Jaws::CheckImage('gadgets/ControlPanel/Resources/images/view_site.png'));
                    $tpl->SetVariable('url', $GLOBALS['app']->getSiteURL('/'));
                } else {
                    $tpl->SetVariable('icon', Jaws::CheckImage('gadgets/'.$gadget['name'].'/Resources/images/logo.png'));
                    $tpl->SetVariable('url', BASE_SCRIPT . '?gadget='.$gadget['name']);
                }
                $tpl->ParseBlock('main/item');
            }
            $tpl->ParseBlock('main');
        }

        if ($this->gadget->GetPermission('default_admin', '', 'Components')) {
            //Count non-installed gadgets
            $noninstalled = $cmpModel->GetGadgetsList(null, false);
            if (count($noninstalled) > 0) {
                $tpl->SetBlock('notifybox');
                $tpl->SetVariable('title', _t('COMPONENTS_GADGETS_NOTINSTALLED'));
                foreach ($noninstalled as $key => $gadget) {
                    $tpl->SetBlock('notifybox/item');
                    $gadgetCompleteDesc = $gadget['title'] . ' - ' . $gadget['description'];
                    $icon = Jaws::CheckImage('gadgets/' . $key . '/Resources/images/logo.png');
                    $tpl->SetVariable('title', $gadgetCompleteDesc);
                    $tpl->SetVariable('name', $gadget['title']);
                    $tpl->SetVariable('icon', $icon);
                    $tpl->SetVariable('url', BASE_SCRIPT. '?gadget=Components&amp;action=InstallGadget&amp;comp='. $key);
                    $tpl->SetVariable('install', _t('COMPONENTS_INSTALL'));
                    $tpl->ParseBlock('notifybox/item');
                }
                $tpl->ParseBlock('notifybox');
            }

            //Count out date gadgets
            $nonupdated = $cmpModel->GetGadgetsList(null, true, false);
            if (count($nonupdated) > 0) {
                $tpl->SetBlock('notifybox');
                $tpl->SetVariable('title', _t('COMPONENTS_GADGETS_OUTDATED'));
                foreach ($nonupdated as $key => $gadget) {
                    $tpl->SetBlock('notifybox/item');
                    $gadgetCompleteDesc = $gadget['title'] . ' - ' . $gadget['description'];
                    $icon = Jaws::CheckImage('gadgets/' . $key . '/Resources/images/logo.png');
                    $tpl->SetVariable('title', $gadgetCompleteDesc);
                    $tpl->SetVariable('name', $gadget['title']);
                    $tpl->SetVariable('icon', $icon);
                    $tpl->SetVariable('url', BASE_SCRIPT. '?gadget=Components&amp;action=UpgradeGadget&amp;comp='. $key);
                    $tpl->SetVariable('install', _t('COMPONENTS_UPDATE'));
                    $tpl->ParseBlock('notifybox/item');
                }
                $tpl->ParseBlock('notifybox');
            }
        }

        // login history
        if (Jaws_Gadget::IsGadgetInstalled('Logs')) {
            $logModel = Jaws_Gadget::getInstance('Logs')->model->load('Logs');
            $logs = $logModel->GetLogs(
                array(
                    'gadget' => 'Users',
                    'action' => 'Login',
                    'user'   => $GLOBALS['app']->Session->GetAttribute('user'),
                ),
                10
            );
            if (!Jaws_Error::IsError($logs) && !empty($logs)) {
                $tpl->SetBlock('login_history');
                $date = Jaws_Date::getInstance();
                $tpl->SetVariable('title', _t('LOGS_LOGIN_HISTORY'));
                foreach ($logs as $log) {
                    $tpl->SetBlock('login_history/item');
                    $tpl->SetVariable('ip', long2ip($log['ip']));
                    $tpl->SetVariable('agent', $log['agent']);
                    $tpl->SetVariable('status_code', $log['status']);
                    $tpl->SetVariable('status_title', _t('GLOBAL_HTTP_ERROR_TITLE_'. $log['status']));
                    $tpl->SetVariable('icon', 'images/stock/'. ($log['status'] == 200 ?  'info.png' : 'stop.png'));
                    $tpl->SetVariable('date', $date->Format($log['insert_time'], 'd MN Y H:i'));
                    $tpl->ParseBlock('login_history/item');
                }
                $tpl->ParseBlock('login_history');
            }
        }

        $last_checking = unserialize($this->gadget->registry->fetch('update_last_checking'));
        $do_checking = (time() - $last_checking['time']) > 86400;
        // lesser do checking if need check
        $do_checking = $do_checking && (mt_rand(1, 10) == mt_rand(1, 10));
        $tpl->SetBlock('versionbox');
        $tpl->SetVariable('do_checking', (int)$do_checking);
        $tpl->SetVariable('jaws_version', JAWS_VERSION);
        $tpl->SetVariable('latest_jaws_version', $last_checking['version']);
        $tpl->SetVariable('lbl_latest_jaws_version', _t('CONTROLPANEL_LATEST_JAWS_VERSION'));
        $tpl->ParseBlock('versionbox');

        return $tpl->Get();
    }

}