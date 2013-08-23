<?php
/**
 * LinkDump Gadget
 *
 * @category   Gadget
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Actions_Link extends Jaws_Gadget_HTML
{
    /**
     * Redirect to the URL and increase the clicks by one
     * 
     * @access  public
     */
    function Link()
    {
        $request =& Jaws_Request::getInstance();
        $lid = $request->get('id', 'get');
        $lid = Jaws_XSS::defilter($lid, true);

        $model = $GLOBALS['app']->LoadGadget('LinkDump', 'Model', 'Links');
        $link = $model->GetLink($lid);
        if (!Jaws_Error::IsError($link) && !empty($link)) {
            $click = $model->Click($link['id']);
            if (!Jaws_Error::IsError($click)) {
                header(Jaws_XSS::filter($_SERVER['SERVER_PROTOCOL'])." 301 Moved Permanently");
                Jaws_Header::Location($link['url']);
            }
        }

        // By default, on the errors stay in the main page
        Jaws_Header::Referrer();
    }
}