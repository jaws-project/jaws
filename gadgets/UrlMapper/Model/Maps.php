<?php
/**
 * UrlMapper Core Gadget
 *
 * @category   GadgetModel
 * @package    UrlMapper
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Model_Maps extends Jaws_Gadget_Model
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
        $urlmapTable = Jaws_ORM::getInstance()->table('url_maps');
        $urlmapTable->select('count([id]):integer');
        $urlmapTable->where('gadget', $gadget)->and()->where('action', $action)->and()->where('map', $map);
        $result = $urlmapTable->and()->where('extension', $extension)->fetchOne();

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

        return $mapsTable->orderBy('gadget', 'order')->fetchAll();
    }
}