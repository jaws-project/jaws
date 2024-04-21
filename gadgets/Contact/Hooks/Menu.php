<?php
/**
 * Contact - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Contact
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2007-2024 Jaws Development Group
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
        $urls[] = array('url'    => $this->gadget->urlMap('Contact'),
                        'title'  => $this::t('ACTIONS_CONTACT'),
                        'title2' => $this::t('US'));
        $urls[] = array('url'    => $this->gadget->urlMap('ContactMini'),
                        'title'  => $this::t('ACTIONS_CONTACTMINI'),
                        'title2' => $this::t('US'));
        $urls[] = array('url'    => $this->gadget->urlMap('ContactSimple'),
                        'title'  => $this::t('ACTIONS_CONTACTSIMPLE'),
                        'title2' => $this::t('US'));
        $urls[] = array('url'    => $this->gadget->urlMap('ContactFull'),
                        'title'  => $this::t('ACTIONS_CONTACTFULL'),
                        'title2' => $this::t('US'));
        return $urls;
    }
}
