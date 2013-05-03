<?php
/**
 * Blocks Gadget
 *
 * @category   Gadget
 * @package    Blocks
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blocks_HTML extends Jaws_Gadget_HTML
{
    /**
     * Default text
     *
     * @access  public
     * @return  public   Site's name
     */
    function DefaultAction()
    {
        return $this->gadget->registry->fetch('site_name', 'Settings');
    }

}