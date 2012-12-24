<?php
/**
 * Friends Layout HTML file (for layout purposes)
 *
 * @category   GadgetLayout
 * @package    Friends
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Friends_LayoutHTML extends Jaws_Gadget_HTML
{
    /**
     * Creates and prints the template of Friends
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Display()
    {
        $tpl = new Jaws_Template('gadgets/Friends/templates/');
        $tpl->Load('Friends.html');
        $model = $GLOBALS['app']->LoadGadget('Friends', 'Model');
        $friends = $model->GetRandomFriends();
        if (!Jaws_Error::IsError($friends)) {
            $tpl->SetBlock('friends');
            $tpl->SetVariable('title', _t('FRIENDS_NAME'));
            $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
            foreach ($friends as $friend) {
                $tpl->SetBlock('friends/friend');
                $tpl->SetVariable('name', $xss->filter($friend['friend'], true));
                $tpl->SetVariable('url',  $xss->filter($friend['url'],    true));
                $tpl->ParseBlock('friends/friend');
            }
        }
        $tpl->ParseBlock('friends');
        return $tpl->Get();
    }

}