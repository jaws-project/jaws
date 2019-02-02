<?php
/**
 * Layout Gadget
 *
 * @category    GadgetAdmin
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2019 Jaws Development Group
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
        if (!$GLOBALS['app']->Session->GetPermission('Users', 'AccessDashboard')) {
            return Jaws_HTTPError::Get(403);
        }

        $type = (int)$this->gadget->request->fetch('type');
        switch ($type) {
            case 2:
                $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
                $layouts = array('Index.User', 'Layout.User');
                break;

            case 1:
                $user = 0;
                $layouts = array('Index.Users', 'Layout.Users');
                break;

            default:
                $user = 0;
                $layouts = array('Index', 'Layout');
        }

        $theme = $GLOBALS['app']->GetTheme();
        if (!$theme['exists']) {
            Jaws_Error::Fatal('Theme '. $theme['name']. ' doesn\'t exists.');
        }

        $layoutModel = $this->gadget->model->load('Layout');
        foreach ($layouts as $layout) {
            if (@is_file($theme['path']. $layout . '.html')) {
                $layoutModel->InitialLayout($layout, $user);
            }
        }

        $this->gadget->session->update('layout.type', $type);
        return Jaws_Header::Location('');
    }

}