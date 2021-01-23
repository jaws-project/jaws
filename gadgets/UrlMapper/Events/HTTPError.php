<?php
/**
 * UrlMapper InstallGadget event
 *
 * @category   Gadget
 * @package    UrlMapper
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2008-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Events_HTTPError extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($shouter, $code)
    {
        $reqURL = Jaws_Utils::getRequestURL(true);
        $uModel = $this->gadget->model->loadAdmin('ErrorMaps');
        $res = $uModel->GetHTTPError($reqURL, $code);
        if (!Jaws_Error::IsError($res) && !empty($res) && ($res['code'] == 301 || $res['code'] == 302)) {
            return Jaws_Header::Location($res['url'], '', $res['code']);
        }

        return $res;
    }

}