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
class ServerTimeLayoutHTML 
{
    /**
     * Returns the server time
     *
     * @access  public
     * @return  string  ServerTime
     */
    function Display()
    {
        $tpl = new Jaws_Template('gadgets/ServerTime/templates/');
        $tpl->Load('ServerTime.html');
        $tpl->SetBlock('servertime');

        $objDate = $GLOBALS['app']->loadDate();
        $strDate = $objDate->Format(time(),
                                     $GLOBALS['app']->Registry->Get('/gadgets/ServerTime/date_format'));
        $tpl->SetVariable('title', _t('SERVERTIME_ACTION_TITLE'));
        $tpl->SetVariable('ServerDateTime', Jaws_Gadget::ParseText($strDate, 'ServerTime'));

        $tpl->ParseBlock('servertime');
        return $tpl->Get();
    }

}