<?php
/**
 * AddressBook - URL List gadget hook
 *
 * @category    GadgetHook
 * @package     AddressBook
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class AddressBook_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Execute()
    {
        $urls   = array();
        $urls[] = array(
            'url'   => $this->gadget->urlMap('AddressBook'),
            'title' => _t('ADDRESSBOOK_NAME')
        );

        return $urls;
    }

}