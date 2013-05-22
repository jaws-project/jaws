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
class ControlPanel_Actions_Admin_ControlPanel extends Jaws_Gadget_HTML
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
        $cmpModel = $GLOBALS['app']->LoadGadget('Components', 'Model', 'Gadgets');
        $gadgets = $cmpModel->GetGadgetsList(null, true, true);
        unset($gadgets['ControlPanel']);

        foreach ($gadgets as $gadget => $gInfo) {
            if ($this->gadget->GetPermission('default_admin', '', $gadget)) {
                $section = $gInfo['section'];
                if (!isset($gadgetsections[$section])) {
                    $gadgetsections[$section] = array();
                }

                $gadgetsections[$section][] = array('name'  => $gadget,
                                                    'tname' => $gInfo['name'],
                                                    'desc'  => $gInfo['description']);
            }
        }

        if ($this->gadget->registry->fetch('show_viewsite', 'Settings') == 'true') {
            $gadgetsections['general'][] = array('name'  => 'Index',
                                                 'tname' => _t('CONTROLPANEL_GENERAL_VIEWSITE'),
                                                 'desc'  => _t('CONTROLPANEL_GENERAL_VIEWSITE'));
        }

        // Load the template
        $tpl = $this->gadget->loadTemplate('ControlPanel.html');
        $this->AjaxMe('script.js');
        foreach ($gadgetsections as $section  => $gadgets) {
            $tpl->SetBlock('main');
            $tpl->SetVariable('title', _t('GLOBAL_GI_' . strtoupper($section)));
            foreach ($gadgets as $gadget) {
                $tpl->SetBlock('main/item');
                $tpl->SetVariable('name', $gadget['tname']);
                $tpl->SetVariable('desc', $gadget['desc']);
                if ($gadget['name'] === 'Index') {
                    $tpl->SetVariable('icon', Jaws::CheckImage('gadgets/ControlPanel/images/view_site.png'));
                    $tpl->SetVariable('url', $GLOBALS['app']->getSiteURL('/'));
                } else {
                    $tpl->SetVariable('icon', Jaws::CheckImage('gadgets/'.$gadget['name'].'/images/logo.png'));
                    $tpl->SetVariable('url', BASE_SCRIPT . '?gadget='.$gadget['name']);
                }
                $tpl->ParseBlock('main/item');
            }
            $tpl->ParseBlock('main');
        }

        if ($this->gadget->GetPermission('default_admin', '', 'Components')) {
            //Count non-installed gadgets..
            $noninstalled = $cmpModel->GetGadgetsList(null, false);
            //Count out date gadgets..
            $nonupdated   = $cmpModel->GetGadgetsList(null, true, false);
            if ((count($noninstalled) + count($nonupdated)) > 0) {
                $tpl->SetBlock('sidebar');
                if (count($noninstalled) > 0) {
                    $tpl->SetBlock('sidebar/notifications');
                    $tpl->SetVariable('title', _t('COMPONENTS_GADGETS_NOTINSTALLED'));
                    foreach ($noninstalled as $key => $gadget) {
                        $tpl->SetBlock('sidebar/notifications/item');
                        $gadgetCompleteDesc = $gadget['name'] . ' - ' . $gadget['description'];
                        $icon = Jaws::CheckImage('gadgets/' . $key . '/images/logo.png');
                        $tpl->SetVariable('title', $gadgetCompleteDesc);
                        $tpl->SetVariable('name', $gadget['name']);
                        $tpl->SetVariable('icon', $icon);
                        $tpl->SetVariable('url', BASE_SCRIPT. '?gadget=Components&amp;action=InstallGadget&amp;comp='. $key);
                        $tpl->SetVariable('install', _t('COMPONENTS_INSTALL'));
                        $tpl->ParseBlock('sidebar/notifications/item');
                    }
                    $tpl->ParseBlock('sidebar/notifications');
                }

                if (count($nonupdated) > 0) {
                    $tpl->SetBlock('sidebar/notifications');
                    $tpl->SetVariable('notify-title', _t('COMPONENTS_GADGETS_OUTDATED'));
                    $tpl->SetVariable('notify_desc', _t('COMPONENTS_GADGETS_OUTDATED_DESC'));
                    foreach ($nonupdated as $key => $gadget) {
                        $tpl->SetBlock('sidebar/notifications/item');
                        $gadgetCompleteDesc = $gadget['name'] . ' - ' . $gadget['description'];
                        $icon = Jaws::CheckImage('gadgets/' . $key . '/images/logo.png');
                        $tpl->SetVariable('title', $gadgetCompleteDesc);
                        $tpl->SetVariable('name', $gadget['name']);
                        $tpl->SetVariable('icon', $icon);
                        $tpl->SetVariable('url', BASE_SCRIPT. '?gadget=Components&amp;action=UpgradeGadget&amp;comp='. $key);
                        $tpl->SetVariable('install', _t('COMPONENTS_UPDATE'));
                        $tpl->ParseBlock('sidebar/notifications/item');
                    }
                    $tpl->ParseBlock('sidebar/notifications');
                }
                $tpl->ParseBlock('sidebar');
            }
        }

        return $tpl->Get();
    }

}