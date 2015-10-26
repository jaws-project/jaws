<?php
/**
 * Friend Gadget
 *
 * @category   Gadget
 * @package    Friend
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Friends_Actions_Friends extends Jaws_Gadget_Action
{
    /**
     * Creates and prints the template of Friends
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Display()
    {
        $tpl = $this->gadget->template->load('Friends.html');
        $model = $this->gadget->model->load('Friends');
        $friends = $model->GetRandomFriends();
        if (!Jaws_Error::IsError($friends)) {
            $tpl->SetBlock('friends');
            $tpl->SetVariable('title', $this->gadget->title);
            foreach ($friends as $friend) {
                $tpl->SetBlock('friends/friend');
                $tpl->SetVariable('name', Jaws_XSS::filter($friend['friend']));
                $tpl->SetVariable('url',  Jaws_XSS::filter($friend['url']));
                $tpl->ParseBlock('friends/friend');
            }
        }
        $tpl->ParseBlock('friends');
        return $tpl->Get();
    }

}