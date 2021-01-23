<?php
/**
 * LinkDump Gadget
 *
 * @category   Gadget
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Actions_Link extends Jaws_Gadget_Action
{
    /**
     * Default action
     * 
     * @access  public
     */
    function Links()
    {
        return $this->gadget->action->load('Groups')->Categories();
    }

    /**
     * Redirect to the URL and increase the clicks by one
     * 
     * @access  public
     */
    function Link()
    {
        $lid = $this->gadget->request->fetch('id', 'get');
        $lid = Jaws_XSS::defilter($lid);

        $model = $this->gadget->model->load('Links');
        $link = $model->GetLink($lid);
        if (!Jaws_Error::IsError($link) && !empty($link)) {
            $click = $model->Click($link['id']);
            if (!Jaws_Error::IsError($click)) {
                return Jaws_Header::Location($link['url'], null, 301);
            }
        }

        // By default, on the errors stay in the main page
        Jaws_Header::Referrer();
    }
}