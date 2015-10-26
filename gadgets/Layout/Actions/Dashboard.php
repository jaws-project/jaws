<?php
/**
 * Layout Gadget
 *
 * @category    GadgetAdmin
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Actions_Dashboard extends Jaws_Gadget_Action
{
    /**
     * Switch between dashboards
     *
     * @access  public
     * @return  mixed   Redirect if switched successfully otherwise content of 403 html status code
     */
    function Dashboard()
    {
        if (!$GLOBALS['app']->Session->GetPermission('Users', 'AccessDashboard')) {
            return Jaws_HTTPError::Get(403);
        }

        //$user = jaws()->request->fetch('user');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');

        $layoutModel = $this->gadget->model->load('Layout');
        $layoutModel->DashboardSwitch($user);
        Jaws_Header::Location('');
    }

}