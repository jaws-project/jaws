<?php
/**
 * ControlPanel Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     ControlPanel
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class ControlPanel_Actions_Admin_JawsVersion extends Jaws_Gadget_HTML
{
    /**
     * Returns latest jaws version
     *
     * @access  public
     * @return  string  Json encoded string
     */
    function JawsVersion()
    {
        $options = array();
        $timeout = (int)$this->gadget->registry->fetch('connection_timeout', 'Settings');
        $options['timeout'] = $timeout;
        if ($this->gadget->registry->fetch('proxy_enabled', 'Settings') == 'true') {
            if ($this->gadget->registry->fetch('proxy_auth', 'Settings') == 'true') {
                $options['proxy_user'] = $this->gadget->registry->fetch('proxy_user', 'Settings');
                $options['proxy_pass'] = $this->gadget->registry->fetch('proxy_pass', 'Settings');
            }
            $options['proxy_host'] = $this->gadget->registry->fetch('proxy_host', 'Settings');
            $options['proxy_port'] = $this->gadget->registry->fetch('proxy_port', 'Settings');
        }

        $jaws_version = '-';
        require_once PEAR_PATH. 'HTTP/Request.php';
        $httpRequest = new HTTP_Request('http://localhost/jaws/?gadget=Components&action=Version&type=0', $options);
        $httpRequest->setMethod(HTTP_REQUEST_METHOD_GET);
        $resRequest  = $httpRequest->sendRequest();
        if (!PEAR::isError($resRequest) && $httpRequest->getResponseCode() == 200) {
            $jaws_version = trim($httpRequest->getResponseBody());
        }

        return $jaws_version;
    }

}