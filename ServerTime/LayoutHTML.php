<?php
/**
 * ServerTime Gadget (layout actions for client side)
 *
 * @category   Gadget
 * @package    ServerTime
 * @author     Jonathan Hernandez <ion@suavizado.com>
  * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ServerTimeLayoutHTML extends Jaws_Gadget_HTML
{
    /**
     * Displays the server time
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Display()
    {
        $tpl = new Jaws_Template('gadgets/ServerTime/templates/');
        $tpl->Load('ServerTime.html');
        $tpl->SetBlock('servertime');

        $objDate = $GLOBALS['app']->loadDate();
        $strDate = $objDate->Format(time(),
                                     $this->gadget->GetRegistry('date_format'));
        $tpl->SetVariable('title', _t('SERVERTIME_ACTION_TITLE'));
        $tpl->SetVariable('ServerDateTime', $this->gadget->ParseText($strDate, 'ServerTime'));

        $tpl->ParseBlock('servertime');
        return $tpl->Get();
    }

}