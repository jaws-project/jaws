<?php
/**
 * Policy Core Gadget
 *
 * @category   Gadget
 * @package    Policy
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Policy_HTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default action
     *
     * @access  public
     * @return  string template content
     */
    function DefaultAction()
    {
        header('Location: '. BASE_SCRIPT);
    }

    /**
     * Tricky way to get the captcha image...
     * @access  public
     * @return PNG image
     */
    function Captcha()
    {
        $request =& Jaws_Request::getInstance();
        $get = $request->Get(array('field', 'key'), 'get');

        $dCaptcha = $this->gadget->registry->fetch($get['field']. '_captcha_driver');
        $objCaptcha =& Jaws_Captcha::getInstance($dCaptcha, $get['field']);
        $objCaptcha->image($get['key']);
    }

}