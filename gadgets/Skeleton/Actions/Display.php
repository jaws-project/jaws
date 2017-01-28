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
class Skeleton_Actions_Display extends Jaws_Gadget_Action
{
    var $objServer = null;

    function getData($keySock, $buffer) {
        $this->objServer->sendAll($buffer);
    }

    /**
     * Displays version of Jaws
     *
     * @access  public
     * @return  string  Jaws version
     */
    function Display()
    {
        /*
        $this->objServer = Jaws_WebSocket_Server::getInstance('127.0.0.1', 3434);
        $this->objServer->open(array($this, 'getData'));
        $model   = $this->gadget->model->load();
        $version = $model->GetJawsVersion();
        */
        return _t('SKELETON_DISPLAY_MESSAGE', $version);
    }

}