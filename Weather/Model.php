<?php
/**
 * Weather gadget
 *
 * @category   GadgetModel
 * @package    Weather
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mohsen@khahani.com>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class WeatherModel extends Jaws_Model
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
        $sql = '
            SELECT
                [id], [title], [fast_url], [latitude], [longitude], [published]
            FROM [[weather]]';

        if (is_numeric($id)) {
            $sql .= '
                WHERE [id] = {id}';
        } else {
            $sql .= '
                WHERE [fast_url] = {id}';
        }

        $types = array('integer', 'text',  'text', 'float', 'float', 'boolean');
        $row = $GLOBALS['db']->queryRow($sql, array('id' => $id), $types);
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error($row->getMessage(), 'SQL');
        }

        return $row;
    }

    /**
     * Gets list of regions
     *
     * @access  public
     * @param   bool    $published  Published status
     * @param   int     $limit      Data limit
     * @param   int     $offset     Data offset
     * @return  mixed   Array of regions or Jaws_Error on failure
     */
    function GetRegions($published = null, $limit = false, $offset = null)
    {
        if (is_numeric($limit)) {
            $res = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage(), 'SQL');
            }
        }

        
        if (is_null($published)) {
            $sql = '
                SELECT [id], [title], [fast_url], [latitude], [longitude], [published]
                FROM [[weather]]
                ORDER BY [id] ASC';
        } else {
            $sql = '
                SELECT [id], [title], [fast_url], [latitude], [longitude], [published]
                FROM [[weather]]
                WHERE [published] = {published}
                ORDER BY [id] ASC';
        }

        $types = array('integer', 'text', 'text', 'float', 'float', 'boolean');
        $result = $GLOBALS['db']->queryAll($sql, array('published' => $published), $types);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage(), 'SQL');
        }

        return $result;
    }

}