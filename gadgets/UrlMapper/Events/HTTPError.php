<?php
/**
 * UrlMapper InstallGadget event
 *
 * @category   Gadget
 * @package    UrlMapper
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Events_HTTPError extends Jaws_Gadget
{
    /**
     * Event execute method
     *
     */
    function Execute($code)
    {
        $uModel = $GLOBALS['app']->loadGadget('UrlMapper', 'AdminModel');
        $siteURL = Jaws_Utils::getRequestURL(true);
        $res = $uModel->HandleHttpErrors($siteURL, $code);
        return $res;
    }

}