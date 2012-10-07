<?php
/**
 * UrlMapper AJAX API
 *
 * @category   Ajax
 * @package    UrlMapper
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapperAdminAjax extends Jaws_Ajax
{
    /**
     * Constructor
     *
     * @access  public
     */
    function UrlMapperAdminAjax(&$model)
    {
        $this->_Model =& $model;
    }

    /**
     * Returns the mapped actions of a certain gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @return  array   Array with actions
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
     * Returns the total maps of a certain action in a certain gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget name so we get sure we don't return the same action
     *                           maps of another gadget
     * @param   string  $action  Action name
     * @return  array   Maps that an action has
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
     * @param   int      $id       Map's ID
     * @param   string   $map      New map
     * @return  boolean  Success/Failure
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
     * @param   string   $enabled     Should maps be used? (true/false)
     * @param   boolean  $use_aliases Should aliases be used?
     * @param   string   $precedence  custom map precedence over default map (true/false)
     * @param   string   $extension   Extension to use
     * @return  boolean  Success/Failure
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
     * @return  array    List of aliases
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
     * @param   int      $id      Alias ID
     * @return  array    Alias information
     */
    function GetAlias($id)
    {
        return $this->_Model->GetAlias($id);
    }

    /**
     * Adds a new alias
     *
     * @access  public
     * @param   string   $alias   Alias value
     * @param   string   $url     Real URL
     * @return  boolean  Success/Failure
     */
    function AddAlias($alias, $url)
    {
        $this->_Model->AddAlias($alias, $url);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Updates an alias by its ID
     *
     * @access  public
     * @param   int      $id      Alias ID
     * @param   string   $alias   Alias value
     * @param   string   $url     Real URL
     * @return  boolean  Success/Failure
     */
    function UpdateAlias($id, $alias, $url)
    {
        $this->_Model->UpdateAlias($id, $alias, $url);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

    /**
     * Deletes an alias by its ID
     *
     * @access  public
     * @param   int      $id      Alias ID
     * @return  boolean  Success/Failure
     */
    function DeleteAlias($id)
    {
        $this->_Model->DeleteAlias($id);
        return $GLOBALS['app']->Session->PopLastResponse();
    }

}