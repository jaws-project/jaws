<?php
/**
 * Shoutbox - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Shoutbox
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ShoutboxURLListHook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Hook()
    {
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('Shoutbox', 'DefaultAction'),
                        'title' => _t('SHOUTBOX_NAME'));
        return $urls;
    }
}
