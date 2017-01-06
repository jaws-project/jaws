<?php
/**
 * Settings - URL List gadget hook
 *
 * @category    GadgetHook
 * @package     Settings
 */
class Settings_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls = array();

        $urls[] = array(
            'url'        => $this->gadget->urlMap('Settings'),
            'title'      => _t('SETTINGS_TITLE'),
            'permission' => array(
                'key'    => 'BasicSettings',
                'subkey' => ''
            )
        );

        return $urls;
    }

}