<?php
/**
 * Weather gadget
 *
 * @category   GadgetModel
 * @package    Weather
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Weather_Model_Regions extends Jaws_Gadget_Model
{
    /**
     * Gets associated data for a region
     *
     * @access  public
     * @param   int     $id  region ID
     * @return  mixed   Array of associated data of region or Jaws_Error on failure
     */
    function GetRegion($id)
    {
        $weatherTable = Jaws_ORM::getInstance()->table('weather');
        $weatherTable->select('id:integer', 'user:integer', 'title', 'fast_url', 'latitude:float', 'longitude:float',
            'published:boolean');

        if (is_numeric($id)) {
            $weatherTable->where('id', $id);
        } else {
            $weatherTable->where('fast_url', $id);
        }
        $row = $weatherTable->fetchRow();

        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage());
        }

        return $row;
    }

    /**
     * Gets list of regions
     *
     * @access  public
     * @param   bool    $published  Published status
     * @param   int     $user       User id
     * @param   int     $limit      Data limit
     * @param   int     $offset     Data offset
     * @return  mixed   Array of regions or Jaws_Error on failure
     */
    function GetRegions($published = null, $user = null, $limit = false, $offset = null)
    {
        $weatherTable = Jaws_ORM::getInstance()->table('weather');
        $weatherTable->select('id:integer', 'title', 'fast_url', 'latitude:float', 'longitude:float',
            'published:boolean');

        if (!is_null($published)) {
            $weatherTable->where('published', $published);
        }

        if (!is_null($user)) {
            $weatherTable->and()->where('user', $user);
        }

        $result = $weatherTable->limit($limit, $offset)->orderBy('id asc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }
}