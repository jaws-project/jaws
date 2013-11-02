<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Model_Contacts extends Jaws_Gadget_Model
{
    /**
     * Updates contacts information of the user
     *
     * @access  public
     * @param   int     $uid            User ID
     * @param   string  $country        User country
     * @param   string  $city           User city
     * @param   string  $address        User address
     * @param   string  $postalCode     User postal code
     * @param   string  $phoneNumber    User phone number
     * @param   string  $mobileNumber   User mobile number
     * @param   string  $faxNumber      User fax number
     * @return  array   Response array (notice or error)
     */
    function UpdateContacts($uid, $country, $city, $address, $postalCode, $phoneNumber, $mobileNumber, $faxNumber)
    {
        $jUser  = new Jaws_User;
        $result = $jUser->UpdateContacts(
            $uid,
            array(
                'country' => $country,
                'city'    => $city,
                'address'   => $address,
                'postal_code' => $postalCode,
                'phone_number' => $phoneNumber,
                'mobile_number' => $mobileNumber,
                'fax_number' => $faxNumber
            )
        );
        //TODO: catch error
        return $result;
    }

}