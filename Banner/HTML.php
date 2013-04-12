<?php
/**
 * Banner Gadget
 *
 * @category   Gadget
 * @package    Banner
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Banner_HTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default action(display)
     *
     * @access    public
     * @return    string    XTHML template content
     */
    function DefaultAction()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Banner', 'LayoutHTML');
        return $layoutGadget->Display();
    }

    /**
     * Redirects request to banner's target
     *
     * @access  public
     * @return  mixed    Void if Success, 404  XHTML template content on Failure
     */
    function Click()
    {
        $model = $GLOBALS['app']->LoadGadget('Banner', 'Model');
        $request =& Jaws_Request::getInstance();
        $id = (int)$request->get('id', 'get');
        $banner = $model->GetBanners($id);
        if (!Jaws_Error::IsError($banner) && !empty($banner)) {
            $click = $model->ClickBanner($banner[0]['id']);
            if (!Jaws_Error::IsError($click)) {
                $link = $banner[0]['url'];
                if (preg_match('/^(http|https|ftp):\/\/[a-z0-9-\.]*/i', $link)) {
                    Jaws_Header::Location($link);
                } else {
                    Jaws_Header::Location($GLOBALS['app']->getSiteURL('/' . $link));
                }
            }
        } else {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(404);
        }
    }

    /**
     * Displays banners in a group via standalone action
     *
     * @access    public
     * @return    string   XHTML template content
     */
    function BannerGroup()
    {
        $request =& Jaws_Request::getInstance();
        $gid = (int)$request->get('id', 'get');
        $layoutGadget = $GLOBALS['app']->LoadGadget('Banner', 'LayoutHTML');
        header(Jaws_XSS::filter($_SERVER['SERVER_PROTOCOL'])." 200 OK");
        return $layoutGadget->Display($gid, true);
    }
}