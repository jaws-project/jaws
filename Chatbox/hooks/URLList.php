<?php
/**
 * Chatbox - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Chatbox
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ChatboxURLListHook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   urls array
     */
    function Hook()
    {
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('Chatbox', 'DefaultAction'),
                        'title' => _t('CHATBOX_NAME'));
        return $urls;
    }
}
