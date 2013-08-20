<?php
/**
 * Friend Gadget
 *
 * @category   Gadget
 * @package    Friend
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Friends_HTML extends Jaws_Gadget_HTML
{
    /**
     * Default action
     *
     * @acces  public
     * @return  string  XHTML result
     */
    function DefaultAction()
    {
        $this->SetTitle(_t('FRIENDS_NAME'));
        $HTML = $GLOBALS['app']->LoadGadget('Friends', 'HTML', 'Friends');
        return $HTML->Display();
    }

}