<?php
/**
 * Layout Gadget
 *
 * @category    GadgetAdmin
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Actions_Layout extends Jaws_Gadget_HTML
{
    /**
     * Switch between layouts/dashboards
     *
     * @access  public
     * @return  mixed   Redirect if switched successfully otherwise content of 403 html status code
     */
    function LayoutSwitch()
    {
        $dashboard_building = $this->gadget->registry->fetch('dashboard_building', 'Users') == 'true';
        if (!$dashboard_building ||
            !$GLOBALS['app']->Session->GetPermission('Users', 'EditUserDashboard')
        ) {
            return Jaws_HTTPError::Get(403);
        }

        $user = jaws()->request->fetch('user');
        if (!$GLOBALS['app']->Session->GetPermission('Users', 'ManageUsersDashboard') || empty($user)) {
            $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        }

        $layoutModel = $this->gadget->load('Model')->load('Model', 'Layout');
        $layoutModel->layoutSwitch($user);
        Jaws_Header::Location('');
    }

}