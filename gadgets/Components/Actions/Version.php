<?php
/**
 * Components Gadget
 *
 * @category    Gadget
 * @package     Components
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Components_Actions_Version extends Jaws_Gadget_Action
{
    /**
     * Returns the Jaws/component version
     *
     * @access  public
     * @return  string  Version as plain text 
     */
    function Version()
    {
        if ($this->gadget->registry->fetch('versions_remote_access') != 'true') {
            return Jaws_HTTPError::Get(403);
        }

        $get = jaws()->request->fetch(array('type', 'component'));
        $version = '0';
        switch ((int)$get['type']) {
            case 0:
                $version = JAWS_VERSION;
                break;

            case 1:
                $objGadget = Jaws_Gadget::getInstance($get['component']);
                $version = Jaws_Error::isError($objGadget)? Jaws_HTTPError::Get(404) : $objGadget->version;
                break;

            case 2:
                $objPlugin = $GLOBALS['app']->LoadPlugin($get['componente']);
                $version = Jaws_Error::isError($objPlugin)? Jaws_HTTPError::Get(404) : $objPlugin->version;
                break;
        }

        return $version;
    }

}