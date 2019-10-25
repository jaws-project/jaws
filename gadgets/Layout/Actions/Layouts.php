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
        $type = (int)$this->gadget->request->fetch('type');
        switch ($type) {
            case 2:
                Jaws_Gadget::getInstance('Users')->gadget->CheckPermission('AccessUserLayout');
                $user = (int)$this->app->session->user->id;
                $layouts = array('Index.User', 'Layout.User');
                break;

            case 1:
                Jaws_Gadget::getInstance('Users')->gadget->CheckPermission('AccessUsersLayout');
                $user = 0;
                $layouts = array('Index.Users', 'Layout.Users');
                break;

            default:
                $user = 0;
                $layouts = array('Index', 'Layout');
        }

        $theme = $this->app->GetTheme();
        if (!$theme['exists']) {
            Jaws_Error::Fatal('Theme '. $theme['name']. ' doesn\'t exists.');
        }

        $layoutModel = $this->gadget->model->load('Layout');
        foreach ($layouts as $layout) {
            if (@is_file($theme['path']. $layout . '.html')) {
                $layoutModel->InitialLayout($layout, $user);
            }
        }

        $this->gadget->session->layout_type = $type;
        return Jaws_Header::Location('');
    }

}