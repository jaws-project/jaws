<?php
/**
 * Zone Model
 *
 * @category    GadgetModel
 * @package     Settings
 */
class Settings_Model_Zone extends Jaws_Gadget_Model
{
    /**
     * Get a list of the Provinces
     *
     * @access  public
     * @return  mixed   Array of Provinces or Jaws_Error on failure
     */
    function GetProvinces()
    {
        return Jaws_ORM::getInstance()->table('provinces')
            ->select('id:integer', 'title')
            ->orderBy('title')
            ->fetchAll();
    }
    
    /**
     * Get a list of the Provinces
     *
     * @access  public
     * @param   int     $id
     * @return  mixed   Array of Provinces or Jaws_Error on failure
     */
    function GetProvince($id)
    {
        return Jaws_ORM::getInstance()->table('provinces')
            ->select('id:integer', 'title')
            ->where('id', $id)
            ->fetchRow();
    }

    /**
     * Get a list of the Cities
     *
     * @access  public
     * @param   array     $provinces   Provinces Id
     * @return mixed Array of Cities or Jaws_Error on failure
     */
    function GetCities($provinces = array())
    {
        $table = Jaws_ORM::getInstance()->table('cities')
            ->select('id:integer', 'province:integer', 'title')
            ->orderBy('title');
        if (!empty($provinces) && count($provinces) > 0) {
            $table->where('province', $provinces, 'in');
        }
        return $table->fetchAll();
    }

    /**
     * Get a city info
     *
     * @access  public
     * @param   int     $id
     * @return mixed Array of Cities or Jaws_Error on failure
     */
    function GetCity($id)
    {
        $table = Jaws_ORM::getInstance()->table('cities')
            ->select('cities.id:integer', 'cities.title', 'cities.province:integer',
                'provinces.title as province_title')
            ->join('provinces', 'cities.province', 'provinces.id')
            ->where('cities.id', $id);
        return $table->fetchRow();
    }

}