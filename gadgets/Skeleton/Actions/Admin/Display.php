<?php
/**
 * Skeleton Gadget
 *
 * @category    Gadget
 * @package     Skeleton
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Skeleton_Actions_Admin_Display extends Jaws_Gadget_Action
{
    var $objServer = null;

    function getData($keySock, $buffer) {
        $this->objServer->sendAll($buffer);
    }

    /**
     * Displays no admin action
     *
     * @access  public
     * @return  string  Jaws version
     */
    function Display()
    {
        $objServer = Jaws_WebSocket_Server::getInstance('0.0.0.0', 3434);
        $objServer->open(array($this, 'getData'));
        return _t('SKELETON_ADMIN_MESSAGE');
    }

}