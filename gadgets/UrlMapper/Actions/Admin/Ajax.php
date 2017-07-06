<?php
/**
 * UrlMapper AJAX API
 *
 * @category   Ajax
 * @package    UrlMapper
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Actions_Admin_Ajax extends Jaws_Gadget_Action
{
    /**
     * Returns mapped actions of a certain gadget
     *
     * @access  public
     * @return  mixed   Array of actions or false on error
     */
    function GetGadgetActions()
    {
        $this->gadget->CheckPermission('ManageMaps');
        @list($gadget) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Maps');
        $actions = $model->GetGadgetActions($gadget);
        if (Jaws_Error::IsError($actions)) {
            return false;
        }

        return $actions;
    }

    /**
     * Returns total maps of a certain action in a certain gadget
     *
     * @access  public
     * @return  array   The maps of the action
     */
    function GetActionMaps()
    {
        $this->gadget->CheckPermission('ManageMaps');
        @list($gadget, $action) = $this->gadget->request->fetchAll('post');
        //Now get the custom maps
        $gHTML = $this->gadget->action->loadAdmin('Maps');
        return $gHTML->GetMaps($gadget, $action);
    }

    /**
     * Updates a map
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateMap()
    {
        $this->gadget->CheckPermission('ManageMaps');
        @list($id, $map, $order) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Maps');
        $res = $model->UpdateMap($id, $map, null, $order);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_MAP_NOT_UPDATED'), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_MAP_UPDATED', $map), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Returns the map route (no additional information) of a certain map
     *
     * @access  public
     * @return  string  Map route
     */
    function GetMap()
    {
        $this->gadget->CheckPermission('ManageMaps');
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Maps');
        return $model->GetMap($id);
    }

    /**
     * Updates the map settings
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateSettings()
    {
        $this->gadget->CheckPermission('ManageProperties');
        @list($enabled, $use_aliases, $precedence, $extension) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Properties');
        $model->SaveSettings($enabled == 'true',
                                    $use_aliases == 'true',
                                    $precedence == 'true',
                                    $extension);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Returns all aliases
     *
     * @access  public
     * @return  mixed   List of aliases or false if no aliases found
     */
    function GetAliases()
    {
        $this->gadget->CheckPermission('ManageAliases');
        $model = $this->gadget->model->load('Aliases');
        $aliases = $model->GetAliases();
        if (count($aliases) > 0) {
            return $aliases;
        }
        return false;
    }

    /**
     * Returns basic information of certain alias
     *
     * @access  public
     * @return  array   Alias information
     */
    function GetAlias()
    {
        $this->gadget->CheckPermission('ManageAliases');
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->load('Aliases');
        return $model->GetAlias($id);
    }

    /**
     * Adds a new alias
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AddAlias()
    {
        $this->gadget->CheckPermission('ManageAliases');
        @list($alias, $url) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Aliases');
        $model->AddAlias($alias, $url);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates the alias
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateAlias()
    {
        $this->gadget->CheckPermission('ManageAliases');
        @list($id, $alias, $url) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Aliases');
        $model->UpdateAlias($id, $alias, $url);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the alias
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteAlias()
    {
        $this->gadget->CheckPermission('ManageAliases');
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('Aliases');
        $model->DeleteAlias($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets all entries/records for error maps datagrid
     *
     * @access  public
     * @return  array   List of ErrorMaps
     */
    function GetErrorMaps()
    {
        $this->gadget->CheckPermission('ManageErrorMaps');
        $post = $this->gadget->request->fetch(array('offset', 'order', 'filters:array'), 'post');
        $gadgetHTML = $this->gadget->action->loadAdmin('ErrorMaps');
        return $gadgetHTML->GetErrorMaps($post['filters'], 15, $post['offset'], $post['order']);
    }

    /**
     * Gets records count for error maps datagrid
     *
     * @access  public
     * @return  int   ErrorMaps row counts
     */
    function GetErrorMapsCount()
    {
        $this->gadget->CheckPermission('ManageErrorMaps');
        $filters = $this->gadget->request->fetch('filters:array', 'post');
        $model = $this->gadget->model->loadAdmin('ErrorMaps');
        $res = $model->GetErrorMapsCount($filters);
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        return $res;
    }

    /**
     * Adds a new error map
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function AddErrorMap()
    {
        $this->gadget->CheckPermission('ManageErrorMaps');
        @list($url, $code, $new_url, $new_code) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('ErrorMaps');
        $res = $model->AddErrorMap($url, $code, $new_url, $new_code);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->getMessage(), RESPONSE_ERROR);
        } else {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERRORMAP_ADDED'), RESPONSE_NOTICE);
        }

        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update the error map
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function UpdateErrorMap()
    {
        $this->gadget->CheckPermission('ManageErrorMaps');
        @list($id, $url, $code, $new_url, $new_code) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('ErrorMaps');
        $model->UpdateErrorMap($id, $url, $code, $new_url, $new_code);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Returns the error map
     *
     * @access  public
     * @return  Array of Error Map
     */
    function GetErrorMap()
    {
        $this->gadget->CheckPermission('ManageErrorMaps');
        @list($id) = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('ErrorMaps');
        $errorMaps = $model->GetErrorMap($id);
        $objDate = Jaws_Date::getInstance();
        $errorMaps['insert_time'] = $objDate->Format($errorMaps['insert_time'], 'Y-m-d H:i:s');
        return $errorMaps;
    }

    /**
     * Deletes the error map
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteErrorMaps()
    {
        $this->gadget->CheckPermission('ManageErrorMaps');
        $ids = $this->gadget->request->fetchAll('post');
        $model = $this->gadget->model->loadAdmin('ErrorMaps');
        $model->DeleteErrorMaps($ids);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the error map with filters
     *
     * @access  public
     * @return  array   Response array (notice or error)
     */
    function DeleteErrorMapsFilters()
    {
        $this->gadget->CheckPermission('ManageErrorMaps');
        $filters = $this->gadget->request->fetch('filters:array', 'post');
        $model = $this->gadget->model->loadAdmin('ErrorMaps');
        $model->DeleteErrorMapsFilters($filters);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}