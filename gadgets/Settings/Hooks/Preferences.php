<?php
/**
 * Settings - Preferences hook
 *
 * @category    GadgetHook
 * @package     Settings
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Settings_Hooks_Preferences extends Jaws_Gadget_Hook
{
    /**
     * Get user's preferences of this gadget
     *
     * @access  public
     * @return  array   Formatted array for using in Users Preferences action
     */
    function Execute()
    {
        $result = array();
        $languages = Jaws_Utils::GetLanguagesList();
        $keys = $this->gadget->registry->fetchAllByUser();
        foreach ($keys as $index => $key) {
            $result[$key['key_name']] = array('key_value' => $key['key_value']);
            switch ($key['key_name']) {
                case 'admin_language':
                    $result[$key['key_name']]['key_values'] = $languages;
                    break;
                default:
            }
        }

        return $result;

    }

}