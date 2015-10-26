<?php
/**
 * Quotes Gadget
 *
 * @category   Gadget
 * @package    Quotes
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Quotes_Actions_Groups extends Jaws_Gadget_Action
{
    /**
     * Displays quotes by group
     *
     * @access  public
     * @return  XHTML template content
     */
    function ViewGroupQuotes()
    {
        $gid = jaws()->request->fetch('id', 'get');
        $layoutGadget = $this->gadget->action->load('Quotes');
        return $layoutGadget->Display($gid);
    }

}