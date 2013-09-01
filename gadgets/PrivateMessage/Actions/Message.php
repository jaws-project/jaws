<?php
/**
 * PrivateMessage Gadget
 *
 * @category    Gadget
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class PrivateMessage_Actions_Message extends Jaws_Gadget_HTML
{
    /**
     * Display a Message Info
     *
     * @access  public
     * @return  void
     */
    function ViewMessage()
    {
        $request =& Jaws_Request::getInstance();
        $id = $request->get('id', 'get');

        $tpl = $this->gadget->loadTemplate('Message.html');
        $tpl->SetBlock('message');

        $date = $GLOBALS['app']->loadDate();
        $model = $GLOBALS['app']->LoadGadget('PrivateMessage', 'Model', 'Message');
        $messages = $model->GetMessage($id);

        $tpl->ParseBlock('message');
        return $tpl->Get();
    }
}