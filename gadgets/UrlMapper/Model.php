<?php
/**
 * UrlMapper Core Gadget
 *
 * @category   GadgetModel
 * @package    UrlMapper
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapperModel extends Jaws_Gadget_Model
{

    /**
     * Checks if map already exists or not
     *
     * @access   public
     * @param    string    $gadget      Gadget name (FS name)
     * @param    string    $action      Gadget action to use
     * @param    string    $map         Map to use (foo/bar/{param}/{param2}...)
     * @param    string    $extension   Extension of map
     * @return   bool      Exists/Doesn't exists
     */
    function MapExists($gadget, $action, $map, $extension = '')
    {
        $params = array();
        $params['gadget']    = $gadget;
        $params['action']    = $action;
        $params['extension'] = $extension;
        $params['map']       = $map;

        $sql = '
            SELECT
                COUNT([id])
            FROM [[url_maps]]
            WHERE
                [gadget] = {gadget}
              AND
                [action] = {action}
              AND
                [map] = {map}
              AND
                [extension] = {extension}';

        $result = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return ($result == '0') ? false : true;
    }

    /**
     * Returns all aliases stored in DB
     *
     * @access  public
     * @return  array   List of URL aliases
     */
    function GetAliases()
    {
        $sql = '
            SELECT
                [id], [alias_url]
            FROM [[url_aliases]]';

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        return $result;
    }

    /**
     * Returns basic information of certain alias
     *
     * @access   public
     * @param    int      $id      Alias ID
     * @return   array    Alias information
     */
    function GetAlias($id)
    {
        $params       = array();
        $params['id'] = $id;

        $sql = '
            SELECT
                [id], [alias_url], [real_url]
            FROM [[url_aliases]]
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return $result;
    }

    /**
     * Checks if hash already exists or not
     *
     * @access   public
     * @param    string $hash   Alias HASH value
     * @return   bool   Exists/Doesn't exists
     */
    function AliasExists($hash)
    {
        $params         = array();
        $params['hash'] = $hash;

        $sql = '
            SELECT
                COUNT([id])
            FROM [[url_aliases]]
            WHERE [alias_hash] = {hash}';

        $result = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return ($result == '0') ? false : true;
    }

    /**
     * Checks if hash already exists or not
     *
     * @access   public
     * @param    string  $url_hash   URL HASH value
     * @return   bool   Exists/Doesn't exists
     */
    function ErrorMapExists($url_hash)
    {
        $params         = array();
        $params['url_hash'] = $url_hash;

        $sql = '
            SELECT
                COUNT([id])
            FROM [[url_errors]]
            WHERE [url_hash] = {url_hash}';

        $result = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return ($result == '0') ? false : true;
    }

    /**
     * Returns maps stored in DB
     *
     * @access  public
     * @return  array   List of maps
     */
    function GetMaps()
    {
        $sql = '
            SELECT
                [gadget], [action], [map], [regexp], [extension],
                [custom_map], [custom_regexp], [custom_extension]
            FROM [[url_maps]]
            ORDER BY [gadget], [order] ASC';

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        return $result;
    }

    /**
     * Returns the real path of an alias(given path), if no alias is found
     * it returns false
     *
     * @access  public
     * @param   string  $alias  Alias
     * @return  mixed   Real path(URL) or false
     */
    function GetAliasPath($alias)
    {
        $sql = '
            SELECT
               [real_url]
            FROM [[url_aliases]]
            WHERE [alias_hash] = {hash}';

        $result = $GLOBALS['db']->queryOne($sql, array('hash' => md5($alias)));
        if (Jaws_Error::IsError($result) || empty($result)) {
            return false;
        }

        return $result;
    }

}