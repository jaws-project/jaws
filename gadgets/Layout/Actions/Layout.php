<?php
/**
 * Layout Gadget
 *
 * @category    GadgetAdmin
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Actions_Layout extends Jaws_Gadget_HTML
{
    /**
     *
     */
    function Layout()
    {
        $this->gadget->CheckPermission('UserLayout');
        $user = jaws()->request->fetch('user');

        Jaws_Header::Location('');
    }

}