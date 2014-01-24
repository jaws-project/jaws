<?php
/**
 * Layout Gadget
 *
 * @category    GadgetAdmin
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2014 Jaws Development Group
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
        $layoutModel->dashboardSwitch($user);
        Jaws_Header::Location('');
    }

    /**
     * Switch between layouts
     *
     * @access  public
     * @return  void
     */
    function DashboardSwitch()
    {
        if (!$GLOBALS['app']->Session->GetPermission('Users', 'ManageDashboard')) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_ACCESS_DENIED'), RESPONSE_ERROR);
            Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout');
        }

        //$user = jaws()->request->fetch('user');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');

        $layoutModel = $this->gadget->model->load('Layout');
        $result = $layoutModel->dashboardSwitch($user);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse($result->getMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('LAYOUT_LAYOUTS_SWITCHED'), RESPONSE_NOTICE);
        }

        Jaws_Header::Location(BASE_SCRIPT . '?gadget=Layout');
    }

}