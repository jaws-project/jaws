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
        $tpl = $this->gadget->template->load('ServiceWorker.js');
        $tpl->SetBlock('ServiceWorker');
        $tpl->SetVariable('pwa_version', $this->gadget->registry->fetch('pwa_version'));
        $tpl->SetVariable('notification_icon', $this->gadget->registry->fetch('site_favicon'));
        $tpl->SetVariable(
            'bodyText503',
            preg_replace(
                "$\r\n|\n$",
                '\n',
                addslashes(Jaws_HTTPError::Get(
                    503,
                    _t('SETTINGS_PWA_ERROR_OFFLINE_TITLE'),
                    _t('SETTINGS_PWA_ERROR_OFFLINE_CONTENT')
                ))
            )
        );
        $tpl->ParseBlock('ServiceWorker');

        header('Content-Type: application/javascript');
        http_response_code(200);

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
        $reqSettings = $this->app->registry->fetchAll('Settings');
        $tpl->SetVariable('pwa_fullname', $reqSettings['pwa_fullname']);
        $tpl->SetVariable('pwa_shortname', $reqSettings['pwa_shortname']);
        $tpl->SetVariable('pwa_description', $reqSettings['pwa_description']);
        $tpl->ParseBlock('Manifest');
        return $tpl->Get();
    }

    /**
     * Prepares PWA Service Worker offline page
     *
     * @access  public
     * @return  string  Offline page
     */
    function Offline()
    {
        $tpl = $this->gadget->template->load('Offline.html');
        $tpl->SetBlock('Offline');
        $tpl->SetVariable('offline_message', _t('SETTINGS_PWA_ERROR_REQUEST_DOES_NOT_EXIST'));
        $tpl->ParseBlock('Offline');
        return $tpl->Get();
    }

}