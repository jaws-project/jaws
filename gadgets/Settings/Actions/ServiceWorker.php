<?php
/**
 * Settings Core Gadget
 *
 * @category    Gadget
 * @package     Settings
 */
class Settings_Actions_ServiceWorker extends Jaws_Gadget_Action
{
    /**
     * Prepares PWA Service Worker JavaScript file
     *
     * @access  public
     * @return  string  Service Worker JavaScript text
     */
    function ServiceWorker()
    {
        header('Content-Type: application/javascript');
        $tpl = $this->gadget->template->load('ServiceWorker.js');
        $tpl->SetBlock('ServiceWorker');
        $reqSettings = $GLOBALS['app']->Registry->fetchAll('Settings');
        $tpl->SetVariable('pwa_version', $reqSettings['pwa_version']);
        $tpl->ParseBlock('ServiceWorker');
        return $tpl->Get();
    }

    /**
     * Prepares PWA Manifest
     *
     * @access  public
     * @return  string  PWA Manifest text
     */
    function Manifest()
    {
        header('Content-Type: application/manifest+json; charset=utf-8');
        $tpl = $this->gadget->template->load('Manifest.json');
        $tpl->SetBlock('Manifest');
        $reqSettings = $GLOBALS['app']->Registry->fetchAll('Settings');
        $tpl->SetVariable('pwa_fullname', $reqSettings['pwa_fullname']);
        $tpl->SetVariable('pwa_shortname', $reqSettings['pwa_shortname']);
        $tpl->SetVariable('pwa_description', $reqSettings['pwa_description']);
        $tpl->ParseBlock('Manifest');
        return $tpl->Get();
    }

}