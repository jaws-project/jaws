<?php
/**
 * Jaws Gadget Request
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Request extends Jaws_Gadget_Class
{
    /**
     * Fetches the data, filters it and then it returns it.
     *
     * @access  public
     * @param   mixed   $key            The key being fetched
     * @param   mixed   $method         Which super global is being fetched from, it can be an array
     * @param   string  $branchName     data branch/part name (empty means root of data)
     * @param   array   $options        Options(filters, xss_strip, json_decode, type_validate)
     * @return  mixed   Returns string or an array depending on the key, otherwise Null if key not exist
     */
    function fetch($key, $method = '', $branchName = '', array $options = array())
    {
        if ($this->gadget->name == $this->app->mainRequest['gadget']) {
            return $this->app->request->fetch($key, $method, $branchName, $options);
        } else {
            return is_scalar($key)? null : array();
        }
    }

    /**
     * Fetches the filtered data with out filter
     *
     * @access  public
     * @param   string  $method     Which super global is being fetched from
     * @param   array   $options    Options(filters, xss_strip, json_decode, type_validate)
     * @return  array   Filtered Data array
     */
    function fetchAll($method = '', array $options = array())
    {
        if ($this->gadget->name == $this->app->mainRequest['gadget']) {
            return $this->app->request->fetchAll($method, $options);
        } else {
            return array();
        }
    }


    /**
     * Gets request method type
     *
     * @access  public
     * @return  string  Request method type(get/post)
     */
    function method()
    {
        return $this->app->request->method();
    }

    /**
     * Creates a new key or updates an old one
     *
     * @access  public
     * @param   mixed   $key            The key being fetched
     * @param   mixed   $value          Key value
     * @param   mixed   $method         Which super global is being fetched from, it can be an array
     * @param   string  $branchName     data branch/part name (empty means root of data)
     * @return  bool    True
     */
    function update($key, $value, $method = '', $branchName = '')
    {
        if ($this->gadget->name == $this->app->mainRequest['gadget']) {
            return $this->app->request->update($key, $value, $method, $branchName);
        }

        return true;
    }

    /**
     * Delete input data by key
     *
     * @access  public
     * @param   mixed   $key            The key being fetched
     * @param   mixed   $method         Which super global is being fetched from, it can be an array
     * @param   string  $branchName     data branch/part name (empty means root of data)
     * @return  bool    True
     */
    function delete($key, $method = '', $branchName = '')
    {
        if ($this->gadget->name == $this->app->mainRequest['gadget']) {
            return $this->app->request->delete($key, $method, $branchName);
        }

        return true;
    }

}