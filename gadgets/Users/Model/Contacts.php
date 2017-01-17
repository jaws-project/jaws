<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 */
class Users_Model_Contacts extends Jaws_Gadget_Model
{
    /**
     * Updates contacts information of the user
     *
     * @access  public
     * @param   int     $uid         User ID
     * @param   array   $data        Contact's data
     * @return  array   Response array (notice or error)
     */
    function UpdateContact($uid, $data)
    {
        $contactData = array();
        $contactData['title'] = $data['title'];
        $contactData['name'] = $data['name'];
        $contactData['note'] = $data['note'];
        $contactData['tel'] = json_encode(
            array('home' => $data['tel_home'], 'work' => $data['tel_work'], 'other' => $data['tel_other'])
        );
        $contactData['fax'] = json_encode(
            array('home' => $data['fax_home'], 'work' => $data['fax_work'], 'other' => $data['fax_other'])
        );
        $contactData['mobile'] = json_encode(
            array('home' => $data['mobile_home'], 'work' => $data['mobile_work'], 'other' => $data['mobile_other'])
        );
        $contactData['url'] = json_encode(
            array('home' => $data['url_home'], 'work' => $data['url_work'], 'other' => $data['url_other'])
        );
        $contactData['email'] = json_encode(
            array('home' => $data['email_home'], 'work' => $data['email_work'], 'other' => $data['email_other'])
        );
        $contactData['address'] = json_encode(
            array(
                'home' =>
                    array(
                        'province' => isset($data['province_home']) ? $data['province_home'] : 0,
                        'city' => isset($data['city_home']) ? $data['city_home'] : 0,
                        'address' => $data['address_home'],
                        'postal_code' => $data['postal_code_home']),
                'work' =>
                    array(
                        'province' => isset($data['province_work']) ? $data['province_work'] : 0,
                        'city' => isset($data['city_work']) ? $data['city_work'] : 0,
                        'address' => $data['address_work'],
                        'postal_code' => $data['postal_code_work']),
                'other' =>
                    array(
                        'province' => isset($data['province_other']) ? $data['province_other'] : 0,
                        'city' => isset($data['city_other']) ? $data['city_other'] : 0,
                        'address' => $data['address_other'],
                        'postal_code' => $data['postal_code_other']),
            )
        );

        $jUser = new Jaws_User;
        $result = $jUser->UpdateContact(
            $uid,
            $contactData
        );

        //TODO: catch error
        return $result;
    }

    /**
     * Updates contacts information of the user
     *
     * @access  public
     * @param   int     $uid         User ID
     * @param   int     $cid         Contact ID
     * @param   array   $data        Contact's data
     * @return  array   Response array (notice or error)
     */
    function UpdateContacts($uid, $cid , $data)
    {
        $contactData = array();
        $contactData['title'] = $data['title'];
        $contactData['name'] = $data['name'];
        $contactData['note'] = isset($data['note']) ? $data['note'] : '';
        $contactData['tel'] = json_encode(
            array(
                'home' => isset($data['tel_home']) ? $data['tel_home'] : '',
                'work' => isset($data['tel_work']) ? $data['tel_work'] : '',
                'other' => isset($data['tel_other']) ? $data['tel_other'] : '',
            )
        );
        $contactData['fax'] = json_encode(
            array(
                'home' => isset($data['fax_home']) ? $data['fax_home'] : '',
                'work' => isset($data['fax_work']) ? $data['fax_work'] : '',
                'other' => isset($data['fax_other']) ? $data['fax_other'] : '',
            )
        );
        $contactData['mobile'] = json_encode(
            array(
                'home' => isset($data['mobile_home']) ? $data['mobile_home'] : '',
                'work' => isset($data['mobile_work']) ? $data['mobile_work'] : '',
                'other' => isset($data['mobile_other']) ? $data['mobile_other'] : '',
            )
        );
        $contactData['url'] = json_encode(
            array(
                'home' => isset($data['url_home']) ? $data['url_home'] : '',
                'work' => isset($data['url_work']) ? $data['url_work'] : '',
                'other' => isset($data['url_other']) ? $data['url_other'] : '',
            )
        );
        $contactData['email'] = json_encode(
            array(
                'home' => isset($data['email_home']) ? $data['email_home'] : '',
                'work' => isset($data['email_work']) ? $data['email_work'] : '',
                'other' => isset($data['email_other']) ? $data['email_other'] : '',
            )
        );
        $contactData['address'] = json_encode(
            array(
                'home' =>
                    array(
                        'province' => isset($data['province_home']) ? $data['province_home'] : 0,
                        'city' => isset($data['city_home']) ? $data['city_home'] : 0,
                        'address' => isset($data['address_home']) ? $data['address_home'] : '',
                        'postal_code' => isset($data['postal_code_home']) ? $data['postal_code_home'] : ''),
                'work' =>
                    array(
                        'province' => isset($data['province_work']) ? $data['province_work'] : 0,
                        'city' => isset($data['city_work']) ? $data['city_work'] : 0,
                        'address' => isset($data['address_work']) ? $data['address_work'] : '',
                        'postal_code' => isset($data['postal_code_work']) ? $data['postal_code_work'] : ''),
                'other' =>
                    array(
                        'province' => isset($data['province_other']) ? $data['province_other'] : 0,
                        'city' => isset($data['city_other']) ? $data['city_other'] : 0,
                        'address' => isset($data['address_other']) ? $data['address_other'] : '',
                        'postal_code' => isset($data['postal_code_other']) ? $data['postal_code_other'] : ''),
            )
        );

        $jUser = new Jaws_User;
        $result = $jUser->UpdateContacts(
            $uid,
            $cid,
            $contactData
        );

        //TODO: catch error
        return $result;
    }

    /**
     * Get provinces list
     *
     * @access  public
     * @param   int     $country    Country id
     * @return  array   Response array (notice or error)
     */
    function GetProvinces($country = null)
    {
        $pTable = Jaws_ORM::getInstance()->table('provinces')
            ->select('id:integer', 'title', 'country')
            ->orderBy('order');
        if (!empty($country)) {
            $pTable->where('country', $country);
        }
        return $pTable->fetchAll();
    }

    /**
     * Get cities list
     *
     * @access  public
     * @param   int     $province       Province id
     * @return  array   Response array (notice or error)
     */
    function GetCities($province = null)
    {
        $cTable = Jaws_ORM::getInstance()->table('cities')
            ->select('id:integer', 'title', 'province')
            ->orderBy('order');
        if (!empty($province)) {
            $cTable->where('province', $province);
        }
        return $cTable->fetchAll();
    }
}