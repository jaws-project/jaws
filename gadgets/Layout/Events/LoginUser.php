<?php
/**
 * Layout LoginUser event
 *
 * @category    Gadget
 * @package     Layout
 */
class Layout_Events_LoginUser extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     * @access  public
     * @param   string  $shouter    Shouter name
     * @param   array   $params     [user, group, title, summary, description, priority, send]
     * @return  bool    Log identity or Jaws_Error on failure
     */
    function Execute($shouter, $params)
    {
        if ($shouter != 'Users') {
            return false;
        }

        $theme = $GLOBALS['app']->GetTheme();
        if (!$theme['exists']) {
            Jaws_Error::Fatal('Theme '. $theme['name']. ' doesn\'t exists.');
        }

        $layout_type = 0;
        if ($this->gadget->GetPermission('UsersLayoutAccess') &&
            (@is_file($theme['path']. 'Index.Users.html') || @is_file($theme['path']. 'Layout.Users.html'))
        ) {
            $layout_type = 1;
        } elseif ($this->gadget->GetPermission('UserLayoutAccess') &&
            (@is_file($theme['path']. 'Index.User.html') || @is_file($theme['path']. 'Layout.User.html'))
        ) {
            $layout_type = 2;
        }

        $this->gadget->session->insert('layout.type', $layout_type);
        return true;
    }

}