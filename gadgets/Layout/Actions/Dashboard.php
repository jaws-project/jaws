<?php
/**
 * Layout Gadget
 *
 * @category    GadgetAdmin
 * @package     Layout
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2024 Jaws Development Group
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
        if (!$this->app->session->getPermission('Users', 'AccessDashboard')) {
            return Jaws_HTTPError::Get(403);
        }

        $layoutModel = $this->gadget->model->load('Layout');
        $layoutModel->InitialLayout('Index.Dashboard');
        return Jaws_Header::Location('');
    }

}