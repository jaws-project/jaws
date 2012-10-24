<?php
/**
 * Friend Gadget
 *
 * @category   Gadget
 * @package    Friend
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FriendsHTML extends Jaws_Gadget_HTML
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
        $layoutGadget = $GLOBALS['app']->LoadGadget('Friends', 'LayoutHTML');
        return $layoutGadget->Display();
    }

}