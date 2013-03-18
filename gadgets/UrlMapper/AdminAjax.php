<?php
/**
 * UrlMapper AJAX API
 *
 * @category   Ajax
 * @package    UrlMapper
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_AdminAjax extends Jaws_Gadget_HTML
{
    /**
     * Constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function UrlMapper_AdminAjax($gadget)
    {
        parent::Jaws_Gadget_HTML($gadget);
        $this->_Model = $this->gadget->load('Model')->loadModel('AdminModel');
    }

    /**
     * Returns mapped actions of a certain gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @return  mixed   Array of actions or false on error
     */
    function GetGadgetActions($gadget)
    {
        $actions = $this->_Model->GetGadgetActions($gadget);
        if (Jaws_Error::IsError($actions)) {
            return false;
        }

        return $actions;
    }

    /**
     * Returns total maps of a certain action in a certain gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget name so we get sure we don't return the same action
     *                           maps of another gadget
     * @param   string  $action  Action name
     * @return  array   The maps of the action
     */
    function GetActionMaps($gadget, $action)
    {
        //Now get the custom maps
        $gHTML = $GLOBALS['app']->LoadGadget('UrlMapper', 'AdminHTML');
        return $gHTML->GetMaps($gadget, $action);
    }

    /**
     * Updates a map
     *
     * @access  public
     * @param   int     $id         Map ID
     * @param   string  $map        Map string
     * @param   string  $extension  Map extension
     * @param   int     $order      Sequence number of the map
     * @return  array   Response array (notice or error)
     */
    function UpdateMap($id, $map, $extension, $order)
    {
        $res = $this->_Model->UpdateMap($id, $map, $extension, null, $order);
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
     * @param   int     $id Map ID
     * @return  string  Map route
     */
    function GetMap($id)
    {
        return $this->_Model->GetMap($id);
    }

    /**
     * Updates the map settings
     *
     * @access  public
     * @param   string  $enabled     Should maps be used? (true/false)
     * @param   bool    $use_aliases Should aliases be used?
     * @param   string  $precedence  custom map precedence over default map (true/false)
     * @param   string  $extension   Extension to use
     * @return  array   Response array (notice or error)
     */
    function UpdateSettings($enabled, $use_aliases, $precedence, $extension)
    {
        $this->_Model->SaveSettings($enabled == 'true',
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
        $aliases = $this->_Model->GetAliases();
        if (count($aliases) > 0) {
            return $aliases;
        }
        return false;
    }

    /**
     * Returns basic information of certain alias
     *
     * @access  public
     * @param   int     $id     Alias ID
     * @return  array   Alias information
     */
    function GetAlias($id)
    {
        return $this->_Model->GetAlias($id);
    }

    /**
     * Adds a new alias
     *
     * @access  public
     * @param   string  $alias  Alias value
     * @param   string  $url    Real URL
     * @return  array   Response array (notice or error)
     */
    function AddAlias($alias, $url)
    {
        $this->_Model->AddAlias($alias, $url);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates the alias
     *
     * @access  public
     * @param   int     $id     Alias ID
     * @param   string  $alias  Alias value
     * @param   string  $url    Real URL
     * @return  array   Response array (notice or error)
     */
    function UpdateAlias($id, $alias, $url)
    {
        $this->_Model->UpdateAlias($id, $alias, $url);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes the alias
     *
     * @access  public
     * @param   int     $id     Alias ID
     * @return  array   Response array (notice or error)
     */
    function DeleteAlias($id)
    {
        $this->_Model->DeleteAlias($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Gets all entries/records for error maps datagrid
     *
     * @access  public
     * @param   int     $limit  Data limit to fetch
     * @param   int     $offset
     * @return  array   List of ErrorMaps
     */
    function GetErrorMaps($limit, $offset = null)
    {
        if (!is_numeric($limit)) {
            $limit = 0;
        }

        $gadgetHTML = $GLOBALS['app']->LoadGadget('UrlMapper', 'AdminHTML');
        return $gadgetHTML->GetErrorMaps($limit, $offset);
    }

    /**
     * Gets records count for error maps datagrid
     *
     * @access  public
     * @return  int   ErrorMaps row counts
     */
    function GetErrorMapsCount()
    {
        $res = $this->_Model->GetErrorMapsCount();
        if (Jaws_Error::IsError($res)) {
            return false;
        }

        return $res;
    }

    /**
     * Adds a new error map
     *
     * @access  public
     * @param   string  $url        source url
     * @param   string  $code       code
     * @param   string  $new_url    destination url
     * @param   string  $new_code   new code
     * @return  array   Response array (notice or error)
     */
    function AddErrorMap($url, $code, $new_url, $new_code)
    {
        $this->_Model->AddErrorMap($url, $code, $new_url, $new_code);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Update the error map
     *
     * @access  public
     * @param   int     $id         error map id
     * @param   string  $url        source url
     * @param   string  $code       code
     * @param   string  $new_url    destination url
     * @param   string  $new_code   new code
     * @return  array   Response array (notice or error)
     */
    function UpdateErrorMap($id, $url, $code, $new_url, $new_code)
    {
        $this->_Model->UpdateErrorMap($id, $url, $code, $new_url, $new_code);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Returns the error map
     *
     * @access  public
     * @param   int     $id Error Map ID
     * @return  Array of Error Map
     */
    function GetErrorMap($id)
    {
        return $this->_Model->GetErrorMap($id);
    }

    /**
     * Deletes the error map
     *
     * @access  public
     * @param   int     $id     Error map ID
     * @return  array   Response array (notice or error)
     */
    function DeleteErrorMap($id)
    {
        $this->_Model->DeleteErrorMap($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}