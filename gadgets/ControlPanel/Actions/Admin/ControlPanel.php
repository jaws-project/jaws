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
        $do_checking = $do_checking && (mt_rand(1, 5) == mt_rand(1, 5));
        $tpl->SetBlock('versionbox');
        $tpl->SetVariable('do_checking', (int)$do_checking);
        $tpl->SetVariable('jaws_version', JAWS_VERSION);
        $tpl->SetVariable('latest_jaws_version', $last_checking['version']);
        $tpl->SetVariable('lbl_latest_jaws_version', _t('CONTROLPANEL_LATEST_JAWS_VERSION'));
        $tpl->ParseBlock('versionbox');

        return $tpl->Get();
    }

}