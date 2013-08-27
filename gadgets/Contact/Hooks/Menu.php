<?php
/**
 * Contact - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Contact
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Contact_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls[] = array('url'    => $GLOBALS['app']->Map->GetURLFor('Contact', 'DefaultAction'),
                        'title'  => _t('CONTACT_ACTION_DISPLAY'),
                        'title2' => _t('CONTACT_US'));
        $urls[] = array('url'    => $GLOBALS['app']->Map->GetURLFor('Contact', 'ContactMini'),
                        'title'  => _t('CONTACT_ACTION_DISPLAY_MINI'),
                        'title2' => _t('CONTACT_US'));
        $urls[] = array('url'    => $GLOBALS['app']->Map->GetURLFor('Contact', 'ContactSimple'),
                        'title'  => _t('CONTACT_ACTION_DISPLAY_SIMPLE'),
                        'title2' => _t('CONTACT_US'));
        $urls[] = array('url'    => $GLOBALS['app']->Map->GetURLFor('Contact', 'ContactFull'),
                        'title'  => _t('CONTACT_ACTION_DISPLAY_FULL'),
                        'title2' => _t('CONTACT_US'));
        return $urls;
    }
}
