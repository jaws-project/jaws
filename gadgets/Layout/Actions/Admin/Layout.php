<?php
/**
 * Layout Gadget
 *
 * @category    GadgetAdmin
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Actions_Admin_Layout extends Jaws_Gadget_Action
{
    /**
     * Redirect to layout manager
     *
     * @access  public
     * @return  void
     */
    function Layout()
    {
        return Jaws_Header::Location($this->gadget->urlMap('Layout', array()));
    }

}