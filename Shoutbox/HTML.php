<?php
/**
 * Shoutbox Gadget
 *
 * @category   Gadget
 * @package    Shoutbox
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Shoutbox_HTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default action(display)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DefaultAction()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('Shoutbox', 'LayoutHTML');
        return $layoutGadget->Display();
    }

    /**
     * Adds a new entry to the shoutbox, sets cookie with user data and redirects to main page
     *
     * @access  public
     * @return  void
     */
    function Post()
    {
        $request =& Jaws_Request::getInstance();
        $post  = $request->get(array('message', 'name', 'email', 'url'), 'post');
        $model = $GLOBALS['app']->LoadGadget('Shoutbox', 'Model');

        if ($GLOBALS['app']->Session->Logged()) {
            $post['name']  = $GLOBALS['app']->Session->GetAttribute('nickname');
            $post['email'] = $GLOBALS['app']->Session->GetAttribute('email');
            $post['url']   = $GLOBALS['app']->Session->GetAttribute('url');
        }

        if (trim($post['message']) == ''|| trim($post['name']) == '') {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('SHOUTBOX_DONT_SEND_EMPTY_MESSAGES'), 'Shoutbox');
            Jaws_Header::Referrer();
        }

        $mPolicy = $GLOBALS['app']->LoadGadget('Policy', 'Model');
        $resCheck = $mPolicy->CheckCaptcha();
        if (Jaws_Error::IsError($resCheck)) {
            $GLOBALS['app']->Session->PushSimpleResponse($resCheck->getMessage(), 'Shoutbox');
            Jaws_Header::Referrer();
        }

        $res = $model->NewEntry($post['name'], $post['message'],
                                $post['email'], $post['url'], $_SERVER['REMOTE_ADDR']);
        if (Jaws_Error::isError($res)) {
            $GLOBALS['app']->Session->PushSimpleResponse($res->getMessage(), 'Shoutbox');
        } else {
            $GLOBALS['app']->Session->PushSimpleResponse(_t('GLOBAL_MESSAGE_SENT'), 'Shoutbox');
        }

        Jaws_Header::Referrer();
    }
}
