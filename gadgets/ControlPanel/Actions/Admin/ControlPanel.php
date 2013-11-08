<?php
/**
 * ControlPanel Core Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     ControlPanel
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2013 Jaws Development Group
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

        $last_checking = unserialize($this->gadget->registry->fetch('update_last_checking'));
        $do_checking = (time() - $last_checking['time']) > 86400;
        $tpl->SetBlock('versionbox');
        $tpl->SetVariable('do_checking', (int)$do_checking);
        $tpl->SetVariable('jaws_version', JAWS_VERSION);
        $tpl->SetVariable('latest_jaws_version', $last_checking['version']);
        $tpl->SetVariable('lbl_latest_jaws_version', _t('CONTROLPANEL_LATEST_JAWS_VERSION'));
        $tpl->ParseBlock('versionbox');

        return $tpl->Get();
    }

}