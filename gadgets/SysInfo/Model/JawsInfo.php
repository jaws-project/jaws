<?php
/**
 * SysInfo Gadget
 *
 * @category   GadgetModel
 * @package    SysInfo
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class SysInfo_Model_JawsInfo extends Jaws_Gadget_Model
{
    /**
     * Gets some info around your Jaws
     *
     * @access  public
     * @return  array   Jaws information
     */
    function GetJawsInfo()
    {
        $cmpGModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $cmpPModel = Jaws_Gadget::getInstance('Components')->model->load('Plugins');
        $theme          = $GLOBALS['app']->GetTheme();
        $coreGadgets    = $cmpGModel->GetGadgetsList(true, true, true);
        $gadgets        = $cmpGModel->GetGadgetsList(false, true, true);
        $outdateGadgets = $cmpGModel->GetGadgetsList(null, true, false);
        $plugins        = $cmpPModel->GetPluginsList(true);

        return array(
            array('title' => "Core gadgets",
                'value' => implode(", ", array_keys($coreGadgets))),
            array('title' => "Gadgets",
                'value' => implode(", ", array_keys($gadgets))),
            array('title' => "Outdated gadgets",
                'value' => implode(", ", array_keys($outdateGadgets))),
            array('title' => "Plugins",
                'value' => implode(", ", array_keys($plugins))),
            array('title' => "Default gadget",
                'value' => $this->gadget->registry->fetch('main_gadget', 'Settings')),
            array('title' => "Authentication method",
                'value' => $this->gadget->registry->fetch('auth_method', 'Users')),
            array('title' => "Mailer",
                'value' => $this->gadget->registry->fetch('mailer', 'Settings')),
            array('title' => "FTP",
                'value' => $this->gadget->registry->fetch('ftp_enabled', 'Settings')),
            array('title' => "Proxy",
                'value' => $this->gadget->registry->fetch('proxy_enabled', 'Settings')),
            array('title' => "Default theme",
                'value' => $theme['name']),
            array('title' => "Encryption",
                'value' => $this->gadget->registry->fetch('crypt_enabled', 'Policy')),
            array('title' => "GZip compression",
                'value' => $this->gadget->registry->fetch('gzip_compression', 'Settings')),
            array('title' => "WWW-Authentication",
                'value' => $this->gadget->registry->fetch('http_auth', 'Settings')),
            array('title' => "URL mapping",
                'value' => $GLOBALS['app']->Registry->fetch('map_enabled', 'UrlMapper')),
            array('title' => "Use rewrite",
                'value' => $GLOBALS['app']->Registry->fetch('map_use_rewrite', 'UrlMapper')),
        );
    }
}