<?php
/**
 * Zones Model
 *
 * @category    GadgetModel
 * @package     Settings
 */
class Settings_Model_Zones extends Jaws_Gadget_Model
{
    /**
     * Get a list of the Provinces
     *
     * @access  public
     * @param   int     $country    Country code
     * @return  mixed   Array of Provinces or Jaws_Error on failure
     */
    function GetProvinces($country = 0)
    {
        return Jaws_ORM::getInstance()->table('zones')
            ->select('province:integer', 'title')
            ->where('country', (int)$country)
            ->and()
            ->where('city', 0)
            ->orderBy('province')
            ->fetchAll();
    }
    
    /**
     * Get a province
     *
     * @access  public
     * @param   int     $province   Province code
     * @param   int     $country    Country code
     * @return  mixed   Array of Provinces or Jaws_Error on failure
     */
    function GetProvince($province, $country = 0)
    {
        return Jaws_ORM::getInstance()->table('zones')
            ->select('province:integer', 'title')
            ->where('country', (int)$country)
            ->and()
            ->where('province', (int)$province)
            ->and()
            ->where('city', 0)
            ->fetchRow();
    }

    /**
     * Get a list of the Cities
     *
     * @access  public
     * @param   int|array   $provinces  Provinces Id
     * @param   int         $country    Country code
     * @return  mixed       Array of Cities or Jaws_Error on failure
     */
    function GetCities($provinces = array(), $country = 0)
    {
        if (!is_array($provinces)) {
            $provinces = array($provinces);
        }

        return Jaws_ORM::getInstance()
            ->table('zones')
            ->select('province:integer', 'city:integer', 'title')
            ->where('country', (int)$country)
            ->and()
            ->where('province', $provinces, 'in')
            ->and()
            ->where('city', 0, '<>')
            ->orderBy('city')
            ->fetchAll();
    }

    /**
     * Get a city info
     *
     * @access  public
     * @param   int     $city       City code
     * @param   int     $province   Province code
     * @param   int     $country    Country code
     * @return mixed Array of Cities or Jaws_Error on failure
     */
    function GetCity($city, $province, $country = 0)
    {
        return Jaws_ORM::getInstance()
            ->table('zones')
            ->select('province:integer', 'city:integer', 'title')
            ->where('country', (int)$country)
            ->and()
            ->where('province', (int)$province)
            ->and()
            ->where('city', (int)$city)
            ->fetchRow();
    }

}