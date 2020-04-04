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
     * @param   array   $user       Logged user attributes
     * @return  bool    Log identity or Jaws_Error on failure
     */
    function Execute($shouter, $user)
    {
        if ($shouter != 'Users') {
            return false;
        }

        $theme = $this->app->GetTheme();
        if (!$theme['exists']) {
            Jaws_Error::Fatal('Theme '. $theme['name']. ' doesn\'t exists.');
        }

        $layout_type = 0;
        if ((int)$this->gadget->registry->fetchByUser('default_layout_type', '', $user['id'])) {
            // load users gadget
            $usersGadget = Jaws_Gadget::getInstance('Users');
            if (($usersGadget->gadget->GetPermission('AccessUserLayout') &&
                @is_file($theme['path']. 'Index.0.html')) ||
                ($usersGadget->gadget->GetPermission('AccessUsersLayout') &&
                @is_file($theme['path']. 'Index.1.html'))
            ) {
                $layout_type = 1;
            }
        }

        $this->gadget->session->layout_type = $layout_type;
        return true;
    }

}