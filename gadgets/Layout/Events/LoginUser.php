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

        
        if (Jaws_Gadget::getInstance('Users')->gadget->GetPermission('AccessUserLayout') &&
            (@is_file($theme['path']. 'Index.User.html') || @is_file($theme['path']. 'Layout.User.html'))
        ) {
            // user personal dashboard/layout
            $layout_type = 2;
        } elseif (Jaws_Gadget::getInstance('Users')->gadget->GetPermission('AccessUsersLayout') &&
            (@is_file($theme['path']. 'Index.Users.html') || @is_file($theme['path']. 'Layout.Users.html'))
        ) {
            // all logged users dashboard/layout
            $layout_type = 1;
        } else {
            $layout_type = 0;
        }

        $this->gadget->session->insert('layout.type', $layout_type);
        return true;
    }

}