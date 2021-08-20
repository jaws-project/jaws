<?php
/**
 * Policy Admin Gadget
 *
 * @category   Gadget
 * @package    Policy
 */
class Policy_Actions_Admin_Zone extends Policy_Actions_Admin_Default
{
    /**
     * Zones action for the Policy gadget
     *
     * @access  public
     * @return  XHTML content
     */
    function Zones()
    {
        $this->gadget->CheckPermission('ManageZones');
        $this->AjaxMe('script.js');
        $this->gadget->define('confirmDelete', Jaws::t('CONFIRM_DELETE'));
        $this->gadget->define('datagridNoItems', Jaws::t('NOTFOUND'));

        $this->gadget->define('LANGUAGE', array(
            'title'=> Jaws::t('TITLE'),
            'from'=> Jaws::t('FROM'),
            'to'=> Jaws::t('TO'),
            'edit'=> Jaws::t('EDIT'),
            'editZoneRange'=> _t('POLICY_ZONE_RANGE_EDIT'),
            'delete'=> Jaws::t('DELETE'),
            'addNewZone'=> _t('POLICY_ZONE_ADD'),
            'editZone'=> _t('POLICY_ZONE_EDIT')
        ));

        $assigns = array();
        $assigns['sidebar'] = $this->SideBar('Zones');
        return $this->gadget->template->xLoadAdmin('Zones.html')->render($assigns);
    }

    /**
     * Return list of Zones data for use in datagrid
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetZones()
    {
        $this->gadget->CheckPermission('ManageZones');
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );

        $model = $this->gadget->model->loadAdmin('Zone');
        $zones = $model->GetZones($post['limit'], $post['offset']);
        if (Jaws_Error::IsError($zones)) {
            return $this->gadget->session->response($zones->GetMessage(), RESPONSE_ERROR);
        }

        $zonesCount = $model->GetZonesCount($post['filters']);
        if (Jaws_Error::IsError($zonesCount)) {
            return $this->gadget->session->response($zonesCount->GetMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => $zonesCount,
                'records' => $zones
            )
        );
    }

    /**
     * Return list of ZoneRanges data for use in datagrid
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetZoneRanges()
    {
        $this->gadget->CheckPermission('ManageZones');
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );

        $model = $this->gadget->model->loadAdmin('Zone');
        $zones = $model->GetZoneRanges($post['limit'], $post['offset']);
        if (Jaws_Error::IsError($zones)) {
            return $this->gadget->session->response($zones->GetMessage(), RESPONSE_ERROR);
        }

        $zonesCount = $model->GetZoneRangesCount($post['filters']);
        if (Jaws_Error::IsError($zonesCount)) {
            return $this->gadget->session->response($zonesCount->GetMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => $zonesCount,
                'records' => $zones
            )
        );
    }

    /**
     * Get a zone info
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetZone()
    {
        $this->gadget->CheckPermission('ManageZones');
        $id = (int)$this->gadget->request->fetch('id', 'post');
        $zoneInfo = $this->gadget->model->loadAdmin('Zone')->GetZone($id);
        if (Jaws_Error::IsError($zoneInfo)) {
            return $this->gadget->session->response($zoneInfo->GetMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            $zoneInfo
        );
    }

    /**
     * Insert a new zone
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function InsertZone()
    {
        $this->gadget->CheckPermission('ManageZones');
        $data = $this->gadget->request->fetch('data:array', 'post');
        $res = $this->gadget->model->loadAdmin('Zone')->InsertZone($data);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response(_t('POLICY_RESPONSE_ZONE_NOT_INSERTED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            _t('POLICY_RESPONSE_ZONE_INSERTED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Get a zone range info
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetZoneRange()
    {
        $this->gadget->CheckPermission('ManageZones');
        $id = (int)$this->gadget->request->fetch('id', 'post');
        $zoneRangeInfo = $this->gadget->model->loadAdmin('Zone')->GetZoneRange($id);
        if (Jaws_Error::IsError($zoneRangeInfo)) {
            return $this->gadget->session->response($zoneRangeInfo->GetMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            $zoneRangeInfo
        );
    }

    /**
     * Insert a new zone range
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function InsertZoneRange()
    {
        $this->gadget->CheckPermission('ManageZones');
        $data = $this->gadget->request->fetch('data:array', 'post');

        $data['from'] = @inet_pton($data['from']);
        $data['from'] = rtrim(base64_encode($data['from']), '=');
        $data['to'] = @inet_pton($data['to']);
        $data['to'] = rtrim(base64_encode($data['to']), '=');

        $res = $this->gadget->model->loadAdmin('Zone')->InsertZoneRange($data);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response(_t('POLICY_RESPONSE_ZONERANGE_NOT_INSERTED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            _t('POLICY_RESPONSE_ZONERANGE_INSERTED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Update a zone info
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function UpdateZone()
    {
        $this->gadget->CheckPermission('ManageZones');
        $post = $this->gadget->request->fetch(array('id:integer', 'data:array'), 'post');
        $res = $this->gadget->model->loadAdmin('Zone')->UpdateZone($post['id'], $post['data']);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response(_t('POLICY_RESPONSE_ZONE_NOT_UPDATED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            _t('POLICY_RESPONSE_ZONE_UPDATED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Update a zone range info
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function UpdateZoneRange()
    {
        $this->gadget->CheckPermission('ManageZones');
        $post = $this->gadget->request->fetch(array('id:integer', 'data:array'), 'post');

        $post['data']['from'] = @inet_pton($post['data']['from']);
        $post['data']['from'] = rtrim(base64_encode($post['data']['from']), '=');
        $post['data']['to'] = @inet_pton($post['data']['to']);
        $post['data']['to'] = rtrim(base64_encode($post['data']['to']), '=');

        $res = $this->gadget->model->loadAdmin('Zone')->UpdateZoneRange($post['id'], $post['data']);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response(_t('POLICY_RESPONSE_ZONERANGE_NOT_UPDATED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            _t('POLICY_RESPONSE_ZONERANGE_UPDATED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Delete a zone
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DeleteZone()
    {
        $this->gadget->CheckPermission('ManageZones');
        $id = (int)$this->gadget->request->fetch('id', 'post');
        $res = $this->gadget->model->loadAdmin('Zone')->DeleteZone($id);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response(_t('POLICY_RESPONSE_ZONE_NOT_DELETED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            _t('POLICY_RESPONSE_ZONE_DELETED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Delete a zone range
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DeleteZoneRange()
    {
        $this->gadget->CheckPermission('ManageZones');
        $id = (int)$this->gadget->request->fetch('id', 'post');
        $res = $this->gadget->model->loadAdmin('Zone')->DeleteZoneRange($id);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response(_t('POLICY_RESPONSE_ZONERANGE_NOT_DELETED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            _t('POLICY_RESPONSE_ZONERANGE_DELETED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * ZoneActions action for the Policy gadget
     *
     * @access  public
     * @return  XHTML content
     */
    function ZoneActions()
    {
        $this->gadget->CheckPermission('ManageZoneActions');
        $this->AjaxMe('script.js');
        $this->gadget->define('confirmDelete', Jaws::t('CONFIRM_DELETE'));
        $this->gadget->define('datagridNoItems', Jaws::t('NOTFOUND'));

        $this->gadget->define('LANGUAGE', array(
            'zone' => _t('POLICY_ZONE'),
            'gadget' => Jaws::t('GADGET'),
            'action' => Jaws::t('ACTION'),
            'order' => _t('POLICY_ORDER'),
            'access' => _t('POLICY_ACCESS'),
            'yes' => Jaws::t('YES'),
            'no' => Jaws::t('NO'),
            'edit' => Jaws::t('EDIT'),
            'delete' => Jaws::t('DELETE'),
            'addNewZoneAction' => _t('POLICY_ZONE_ACTION_ADD'),
            'editZoneAction' => _t('POLICY_ZONE_ACTION_EDIT')
        ));

        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgets = $cmpModel->GetGadgetsList(null, true, true);

        $this->gadget->define('gadgetList', array_column($gadgets, 'title', 'name'));

        $assigns = array();
        $assigns['sidebar'] = $this->SideBar('ZoneActions');
        $assigns['zones'] = $this->gadget->model->loadAdmin('Zone')->GetZones();
        $assigns['gadgets'] = $gadgets;
        return $this->gadget->template->xLoadAdmin('ZoneActions.html')->render($assigns);
    }

    /**
     * Return list of ZonesActions data for use in datagrid
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetZoneActions()
    {
        $this->gadget->CheckPermission('ManageZoneActions');
        $post = $this->gadget->request->fetch(
            array('offset', 'limit', 'sortDirection', 'sortBy', 'filters:array'),
            'post'
        );

        $model = $this->gadget->model->loadAdmin('Zone');
        $zoneActions = $model->GetZoneActions($post['filters'], $post['limit'], $post['offset']);
        if (Jaws_Error::IsError($zoneActions)) {
            return $this->gadget->session->response($zoneActions->GetMessage(), RESPONSE_ERROR);
        }

        $zoneActionsCount = $model->GetZonesCount($post['filters']);
        if (Jaws_Error::IsError($zoneActionsCount)) {
            return $this->gadget->session->response($zoneActionsCount->GetMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            array(
                'total' => $zoneActionsCount,
                'records' => $zoneActions
            )
        );
    }

    /**
     * Get a zone action info
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function GetZoneAction()
    {
        $this->gadget->CheckPermission('ManageZoneActions');
        $id = (int)$this->gadget->request->fetch('id', 'post');
        $zoneActionInfo = $this->gadget->model->loadAdmin('Zone')->GetZoneAction($id);
        if (Jaws_Error::IsError($zoneActionInfo)) {
            return $this->gadget->session->response($zoneActionInfo->GetMessage(), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            $zoneActionInfo
        );
    }

    /**
     * Insert a new zone action
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function InsertZoneAction()
    {
        $this->gadget->CheckPermission('ManageZoneActions');
        $data = $this->gadget->request->fetch('data:array', 'post');
        $res = $this->gadget->model->loadAdmin('Zone')->InsertZoneAction($data);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response(_t('POLICY_RESPONSE_ZONE_ACTION_NOT_INSERTED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            _t('POLICY_RESPONSE_ZONE_ACTION_INSERTED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Update a zone action info
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function UpdateZoneAction()
    {
        $this->gadget->CheckPermission('ManageZoneActions');
        $post = $this->gadget->request->fetch(array('id:integer', 'data:array'), 'post');
        $res = $this->gadget->model->loadAdmin('Zone')->UpdateZoneAction($post['id'], $post['data']);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response(_t('POLICY_RESPONSE_ZONE_ACTION_NOT_UPDATED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            _t('POLICY_RESPONSE_ZONE_ACTION_UPDATED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Delete a zone action
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DeleteZoneAction()
    {
        $this->gadget->CheckPermission('ManageZoneActions');
        $id = (int)$this->gadget->request->fetch('id', 'post');
        $res = $this->gadget->model->loadAdmin('Zone')->DeleteZoneAction($id);
        if (Jaws_Error::IsError($res) || $res === false) {
            return $this->gadget->session->response(_t('POLICY_RESPONSE_ZONE_ACTION_NOT_DELETED'), RESPONSE_ERROR);
        }

        return $this->gadget->session->response(
            _t('POLICY_RESPONSE_ZONE_ACTION_DELETED'),
            RESPONSE_NOTICE
        );
    }

    /**
     * Get gadget's action
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function GetGadgetActions()
    {
        $gadget = $this->gadget->request->fetch('gadget', 'post');
        if (empty($gadget)) {
            return $this->gadget->session->response(
                '',
                RESPONSE_NOTICE,
                array()
            );
        }

        $actions = Jaws_Gadget::getInstance($gadget)->action->fetchAll();
        if (Jaws_Error::IsError($actions)) {
            return $this->gadget->session->response(
                $actions->getMessage(),
                RESPONSE_ERROR
            );
        }

        return $this->gadget->session->response(
            '',
            RESPONSE_NOTICE,
            $actions
        );
    }
}