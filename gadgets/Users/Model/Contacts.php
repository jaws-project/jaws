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
        $contactData['address'] = json_encode(
            array('province' => $data['province'], 'city' => $data['city'],
                'address' => $data['address'], 'postal_code' => $data['postal_code'])
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
        $contactData['address'] = json_encode(
            array('province' => $data['province'], 'city' => $data['city'],
                'address' => $data['address'], 'postal_code' => $data['postal_code'])
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