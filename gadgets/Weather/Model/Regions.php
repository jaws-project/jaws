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

    /**
     * Gets total count of regions
     *
     * @access  public
     * @param   bool    $published  Published status
     * @param   int     $user       User id
     * @return  mixed   Total of regions or Jaws_Error on failure
     */
    function GetRegionsCount($published = null, $user = null)
    {
        $weatherTable = Jaws_ORM::getInstance()->table('weather');
        $weatherTable->select('count(id):integer');

        if (!is_null($published)) {
            $weatherTable->where('published', $published);
        }

        if (!is_null($user)) {
            $weatherTable->and()->where('user', $user);
        }

        return $weatherTable->fetchOne();
    }

    /**
     * Update user's region
     *
     * @access  public
     * @param   array   $data        Region's ids
     * @return  mixed   True or Jaws_Error on failure
     */
    function InsertUserRegion($data)
    {
        $fast_url = empty($data['fast_url']) ? $data['title'] : $data['fast_url'];
        $fast_url = $this->GetRealFastUrl($fast_url, 'weather');
        $data['fast_url'] = $fast_url;
        $data['latitude']  = (float) $data['latitude'];
        $data['longitude'] = (float) $data['longitude'];

        $weatherTable = Jaws_ORM::getInstance()->table('weather');
        return $weatherTable->insert($data)->exec();
    }

    /**
     * Update user's region
     *
     * @access  public
     * @param   int     $id          Region id
     * @param   array   $data        Region's ids
     * @param   int     $user        User id
     * @return  mixed   True or Jaws_Error on failure
     */
    function UpdateUserRegion($id, $data, $user)
    {
        $weatherTable = Jaws_ORM::getInstance()->table('weather');
        return $weatherTable->update($data)->where('user', $user)->and()->where('id', $id)->exec();
    }

    /**
     * Delete user's regions
     *
     * @access  public
     * @param   int     $user       User id
     * @param   array   $ids        Region's ids
     * @return  mixed   True or Jaws_Error on failure
     */
    function DeleteUserRegions($user, $ids)
    {
        $weatherTable = Jaws_ORM::getInstance()->table('weather');
        return $weatherTable->delete()->where('user', $user)->and()->where('id', $ids, 'in')->exec();
    }
}