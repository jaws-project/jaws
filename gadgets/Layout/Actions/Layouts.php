<?php
/**
 * Layout Gadget
 *
 * @category    GadgetAdmin
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Actions_Layouts extends Jaws_Gadget_Action
{
    /**
     * Switch between dashboards
     *
     * @access  public
     * @return  mixed   Redirect if switched successfully otherwise content of 403 html status code
     */
    function LayoutType()
    {
        $layouts = array();
        $type = (int)$this->gadget->request->fetch('type');
        switch ($type) {
            case 1:
                // load users gadget
                $usersGadget = Jaws_Gadget::getInstance('Users');
                if ($usersGadget->gadget->GetPermission('AccessUserLayout')) {
                    $layouts[] = array('Index.0' => (int)$this->app->session->user->id);
                }
                if ($usersGadget->gadget->GetPermission('AccessUsersLayout')) {
                    $layouts[] = array('Index.1'  => 0);
                    $layouts[] = array('Layout.1' => 0);
                }
                break;

            default:
                $layouts[] = array('Index'  => 0);
                $layouts[] = array('Layout' => 0);
        }

        if (empty($layouts)) {
            return Jaws_HTTPError::Get(403);
        }

        $theme = $this->app->GetTheme();
        if (!$theme['exists']) {
            Jaws_Error::Fatal('Theme '. $theme['name']. ' doesn\'t exists.');
        }

        $layoutModel = $this->gadget->model->load('Layout');
        foreach ($layouts as $layout => $user) {
            if (@is_file($theme['path']. $layout . '.html')) {
                $layoutModel->InitialLayout($layout, $user);
            }
        }

        $this->gadget->session->layout_type = $type;
        return Jaws_Header::Location('');
    }

}