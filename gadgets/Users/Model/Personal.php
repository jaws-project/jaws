<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Users_Model_Personal extends Jaws_Gadget_Model
{
    /**
     * Updates user profile
     *
     * @access  public
     * @param   int      $uid       User ID
     * @param   string   $fname     First name
     * @param   string   $lname     Last name
     * @param   string   $gender    User gender
     * @param   string   $ssn       Social Security number
     * @param   string   $dob       User birth date
     * @param   string   $url       User URL
     * @param   string   $avatar    User avatar filename
     * @param   string   $signature
     * @param   string   $about     About user
     * @param   string   $experiences
     * @param   string   $occupations
     * @param   string   $interests
     * @return  mixed    True on success or Jaws_Error on failure
     */
    function UpdatePersonal($uid, $fname, $lname, $gender, $ssn, $dob, $url, $avatar, $signature, $about,
                            $experiences, $occupations, $interests)
    {
        require_once JAWS_PATH . 'include/Jaws/User.php';
        $jUser = new Jaws_User;
        $pInfo = array(
            'fname'        => $fname,
            'lname'        => $lname,
            'gender'       => $gender,
            'ssn'          => $ssn,
            'dob'          => $dob,
            'url'          => $url,
            'avatar'       => $avatar,
            'signature'    => $signature,
            'about'        => $about,
            'experiences'  => $experiences,
            'occupations'  => $occupations,
            'interests'    => $interests
        );

        $result = $jUser->UpdatePersonal($uid, $pInfo);
        return $result;
    }

}