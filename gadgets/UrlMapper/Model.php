<?php
/**
 * UrlMapper Core Gadget
 *
 * @category   GadgetModel
 * @package    UrlMapper
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Model extends Jaws_Gadget_Model
{

    /**
     * Checks if map already exists or not
     *0
     * @access   public
     * @param    string    $gadget      Gadget name (FS name)
     * @param    string    $action      Gadget action to use
     * @param    string    $map         Map to use (foo/bar/{param}/{param2}...)
     * @param    string    $extension   Extension of map
     * @return   bool      Exists/Doesn't exists
     */
    function MapExists($gadget, $action, $map, $extension = '')
    {
        $urlmapTable = Jaws_ORM::getInstance()->table('url_maps');
        $urlmapTable->select('count([id]):integer');
        $urlmapTable->where('gadget', $gadget)->and()->where('action', $action)->and()->where('map', $map);
        $result = $urlmapTable->and()->where('extension', $extension)->getOne();

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
        $aliasesTable = Jaws_ORM::getInstance()->table('url_aliases');
        $result = $aliasesTable->select(array('id:integer', 'alias_url'))->getAll();
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
        $aliasesTable = Jaws_ORM::getInstance()->table('url_aliases');
        $result = $aliasesTable->select(array('id:integer', 'alias_url', 'real_url'))->where('id', $id)->getRow();
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
        $aliasesTable = Jaws_ORM::getInstance()->table('url_aliases');
        $result = $aliasesTable->select('count([id]):integer')->where('alias_hash', $hash)->getOne();
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
        $urlerrorsTable = Jaws_ORM::getInstance()->table('url_errors');
        $result = $urlerrorsTable->select('count([id]):integer')->where('url_hash', $url_hash)->getOne();
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
        $mapsTable = Jaws_ORM::getInstance()->table('url_maps');
        $mapsTable->select(
            'gadget', 'action', 'map', 'regexp', 'extension', 'custom_map', 'custom_regexp',
            'custom_regexp', 'vars_regexps'
        );

        return $mapsTable->orderBy('gadget', 'order')->getAll();
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
        $aliasesTable = Jaws_ORM::getInstance()->table('url_aliases');
        $result = $aliasesTable->select('real_url')->where('alias_hash', md5($alias))->getOne();
        if (Jaws_Error::IsError($result) || empty($result)) {
            return false;
        }

        return $result;
    }

}