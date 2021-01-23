<?php
/**
 * ControlPanel Gadget Admin
 *
 * @category    GadgetAdmin
 * @package     ControlPanel
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class ControlPanel_Actions_Admin_JawsVersion extends Jaws_Gadget_Action
{
    /**
     * Returns latest jaws version
     *
     * @access  public
     * @return  string  Json encoded string
     */
    function JawsVersion()
    {
        $jaws_version = '';
        $httpRequest = new Jaws_HTTPRequest();
        $httpRequest->default_error_level = JAWS_ERROR_NOTICE;
        $result = $httpRequest->get('http://jaws-project.com/version/0');
        if (!Jaws_Error::IsError($result) && $result['status'] == 200) {
            if (preg_match('/^\d+(\.\d+)+.*/i', $result['body'])) {
                $jaws_version = $result['body'];
                $this->gadget->registry->update(
                    'update_last_checking',
                    array('version' => $jaws_version, 'time' => time())
                );
            }
        }

        return $jaws_version;
    }

}