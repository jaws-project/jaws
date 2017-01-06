<?php
/**
 * ControlPanel - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    ControlPanel
 */
class ControlPanel_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Execute()
    {
        $urls = array();
        $admin_script = $this->gadget->registry->fetch('admin_script', 'Settings');
        $urls[] = array(
            'url'   => empty($admin_script)? 'admin.php' : $admin_script,
            'title' => _t('USERS_CONTROLPANEL'),
            'permission' => array(
                'key'    => 'default_admin',
                'subkey' => ''
            )
        );

        return $urls;
    }

}