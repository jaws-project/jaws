<?php
/**
 * Poll Gadget
 *
 * @category   Gadget
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_HTML extends Jaws_Gadget_HTML
{
    /**
     * Default action
     *
     * @acces  public
     * @return  string  XHTML template result
     */
    function DefaultAction()
    {
        $this->SetTitle(_t('POLL_NAME'));
        $pollHTML = $GLOBALS['app']->LoadGadget('Poll', 'HTML', 'Poll');
        return $pollHTML->Polls();
    }


}