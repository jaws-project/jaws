<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 */
class Users_Model_Contact extends Jaws_Gadget_Model
{
    /**
     * Get the contact information of an user by the username or ID
     *
     * @access  public
     * @param   mixed   $user   The username or ID
     * @param   mixed   $cid    The contact or ID
     * @return  mixed   Returns an array with the contact information of the user or Jaws_Error
     */
    function get($user, $cid = 0)
    {
        $objORM = Jaws_ORM::getInstance()
            ->table('users_contacts', 'uc')
            ->select(
                'uc.id:integer', 'uc.owner:integer', 'uc.title', 'uc.name', 'uc.tel', 'uc.mobile', 'uc.fax',
                'uc.url', 'uc.email', 'uc.address', 'uc.note'
            );

        if (empty($cid)) {
            $objORM->join('users', 'users.contact', 'uc.id');
            $objORM->where('uc.owner', (int)$user);
        } else {
            $objORM->where('uc.owner', (int)$user);
            $objORM->and()->where('uc.id', (int)$cid);
        }

        $contact = $objORM->fetchRow();
        if (!empty($contact) && !Jaws_Error::IsError($contact)) {
            $rawContact = $this->decodeContact($contact);
            $contact = array_merge($contact, $rawContact);
            unset(
                $contact['tel'], $contact['fax'], $contact['mobile'],
                $contact['url'], $contact['email'], $contact['address']
            );
        }

        return $contact;
    }

    /**
     * Get user's contact list
     *
     * @access  public
     * @param   int     $user   The User ID
     * @param   int     $limit  Count of posts to be returned
     * @param   int     $offset Offset of data array
     * @return  mixed   Returns an array with the contact information of the user or Jaws_Error
     */
    function list($user, $limit = 0, $offset = null)
    {
        return Jaws_ORM::getInstance()
            ->table('users_contacts', 'uc')
            ->select(
                'uc.id:integer', 'uc.owner:integer', 'uc.title', 'uc.name', 'uc.tel', 'uc.mobile', 'uc.fax',
                'uc.url', 'uc.email', 'uc.address', 'uc.note'
            )
            ->join('users', 'users.id', 'uc.owner')
            ->where('users.id', $user)
            ->limit($limit, $offset)
            ->fetchAll();
    }

    /**
     * Get user's contacts count
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  mixed   Returns an array with the contact information of the user or Jaws_Error
     */
    function listCount($user)
    {
        return Jaws_ORM::getInstance()->table('users_contacts')
            ->select('count(id):integer')
            ->where('owner', $user)
            ->fetchOne();
    }

    /**
     * Updates contacts information of the user
     *
     * @access  public
     * @param   int     $uid    User ID
     * @param   array   $data   Contact's data
     * @param   bool    $main   Main contact?
     * @param   int     $cid    Contact's ID
     * @return  array   Response array (notice or error)
     */
    function update($uid, $data, $main = true, $cid = 0)
    {
        // json encode contact data
        $data = $this->encodeContact($data);
        // unset invalid keys
        $invalids = array_diff(
            array_keys($data),
            array('title', 'name', 'image', 'note', 'tel', 'mobile', 'fax', 'url', 'email', 'address')
        );
        foreach ($invalids as $invalid) {
            unset($data[$invalid]);
        }

        $user = $this->gadget->model->load('User')->get($uid);
        if (Jaws_Error::IsError($user) || empty($user)) {
            return false;
        }

        if (JAWS_GODUSER == $user['id']) {
            if (!isset($this->app) ||
                !property_exists($this->app, 'session') ||
                $this->app->session->user->id != $user['id']
            ) {
                return Jaws_Error::raiseError(
                    Jaws::t('ERROR_ACCESS_DENIED'),
                    __FUNCTION__,
                    JAWS_ERROR_NOTICE
                );
            }
        }

        $data['owner'] = $user['id'];
        $data['checksum'] = hash64(json_encode($data));

        $objORM = Jaws_ORM::getInstance();
        if (!$main) {
            $howmany = $objORM->table('users_contacts')
                ->select('count(id):integer')
                ->where('owner', $uid)
                ->and()
                ->where('checksum', $data['checksum'])
                ->fetchOne();
            if (!empty($howmany)) {
                return false;
            }
        }
        // begin transaction
        $objORM->beginTransaction();

        $contactId = $objORM->table('users_contacts')
            ->upsert($data)
            ->where('owner', $uid)
            ->and()
            ->where('id', $main? $user['contact'] : $cid)
            ->exec();
        if (Jaws_Error::IsError($contactId)) {
            return $contactId;
        }

        if ($main) {
            // set user's contact id
            $res = $objORM->table('users')->update(
                    array('contact' => $contactId)
                )->where('id', (int)$uid)
                ->exec();
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
        }

        // commit transaction
        $objORM->commit();
        return $contactId;
    }

    /**
     * Get user's contact list
     *
     * @access  public
     * @param   int     $user   The User ID
     * @param   array   $ids    Contacts id
     * @return  mixed   Returns an array with the contact information of the user or Jaws_Error
     */
    function delete($user, $ids)
    {
        return Jaws_ORM::getInstance()->table('users_contacts')
            ->delete()
            ->where('owner', $user)
            ->and()->where('id', $ids, 'in')
            ->exec();
    }

    /**
     * Encode raw contact data 
     *
     * @access  private
     * @param   array   $contact    Raw contact data
     * @return  string  Encoded contact data
     */
    private function encodeContact($contact)
    {
        $encoded = array();
        $encoded['title'] = $contact['title'];
        $encoded['name']  = $contact['name'];
        $encoded['note']  = isset($contact['note'])? $contact['note'] : '';
        if (!empty($contact['image'])) {
            $encoded['image'] = json_encode($contact['image']);
        }
        $encoded['tel'] = json_encode(
            array(
                'home' => isset($contact['tel_home'])? $contact['tel_home'] : '',
                'work' => isset($contact['tel_work'])? $contact['tel_work'] : '',
                'other' => isset($contact['tel_other'])? $contact['tel_other'] : ''
            )
        );
        $encoded['fax'] = json_encode(
            array(
                'home' => isset($contact['fax_home'])? $contact['fax_home'] : '',
                'work' => isset($contact['fax_work'])? $contact['fax_work'] : '',
                'other' => isset($contact['fax_other'])? $contact['fax_other'] : ''
            )
        );
        $encoded['mobile'] = json_encode(
            array(
                'home' => isset($contact['mobile_home'])? $contact['mobile_home'] : '',
                'work' => isset($contact['mobile_work'])? $contact['mobile_work'] : '',
                'other' => isset($contact['mobile_other'])? $contact['mobile_other'] : ''
            )
        );
        $encoded['url'] = json_encode(
            array(
                'home' => isset($contact['url_home'])? $contact['url_home'] : '',
                'work' => isset($contact['url_work'])? $contact['url_work'] : '',
                'other' => isset($contact['url_other'])? $contact['url_other'] : ''
            )
        );
        $encoded['email'] = json_encode(
            array(
                'home' => isset($contact['email_home'])? $contact['email_home'] : '',
                'work' => isset($contact['email_work'])? $contact['email_work'] : '',
                'other' => isset($contact['email_other'])? $contact['email_other'] : ''
            )
        );
        $encoded['address'] = json_encode(
            array(
                'home' =>
                    array(
                        'country' => isset($contact['country_home']) ? $contact['country_home'] : '',
                        'province' => isset($contact['province_home']) ? $contact['province_home'] : '',
                        'city' => isset($contact['city_home']) ? $contact['city_home'] : '',
                        'address' => isset($contact['address_home'])? $contact['address_home'] : '',
                        'postal_code' => isset($contact['postal_code_home'])? $contact['postal_code_home'] : ''
                    ),
                'work' =>
                    array(
                        'country' => isset($contact['country_work']) ? $contact['country_work'] : '',
                        'province' => isset($contact['province_work']) ? $contact['province_work'] : '',
                        'city' => isset($contact['city_work']) ? $contact['city_work'] : '',
                        'address' => isset($contact['address_work'])? $contact['address_work'] : '',
                        'postal_code' => isset($contact['postal_code_work'])? $contact['postal_code_work'] : ''
                    ),
                'other' =>
                    array(
                        'country' => isset($contact['country_other']) ? $contact['country_other'] : '',
                        'province' => isset($contact['province_other']) ? $contact['province_other'] : '',
                        'city' => isset($contact['city_other']) ? $contact['city_other'] : '',
                        'address' => isset($contact['address_other'])? $contact['address_other'] : '',
                        'postal_code' => isset($contact['postal_code_other'])? $contact['postal_code_other'] : ''
                    ),
            )
        );

        return $encoded;
    }

    /**
     * Decode contact data 
     *
     * @access  private
     * @param   string  $contact    encoded contact data
     * @return  array   Decoded contact data
     */
    private function decodeContact($contact)
    {
        $decoded = array();
        $tel = json_decode((string)$contact['tel'], true);
        $decoded['tel_home'] = isset($tel['home']) ? $tel['home'] : '';
        $decoded['tel_work'] = isset($tel['work']) ? $tel['work'] : '';
        $decoded['tel_other'] = isset($tel['other']) ? $tel['other'] : '';

        $fax = json_decode((string)$contact['fax'], true);
        $decoded['fax_home'] = isset($fax['home']) ? $fax['home'] : '';
        $decoded['fax_work'] = isset($fax['work']) ? $fax['work'] : '';
        $decoded['fax_other'] = isset($fax['other']) ? $fax['other'] : '';

        $mobile = json_decode((string)$contact['mobile'], true);
        $decoded['mobile_home'] = isset($mobile['home']) ? $mobile['home'] : '';
        $decoded['mobile_work'] = isset($mobile['work']) ? $mobile['work'] : '';
        $decoded['mobile_other'] = isset($mobile['other']) ? $mobile['other'] : '';

        $url = json_decode((string)$contact['url'], true);
        $decoded['url_home'] = isset($url['home']) ? $url['home'] : '';
        $decoded['url_work'] = isset($url['work']) ? $url['work'] : '';
        $decoded['url_other'] = isset($url['other']) ? $url['other'] : '';

        $email = json_decode((string)$contact['email'], true);
        $decoded['email_home'] = isset($email['home']) ? $email['home'] : '';
        $decoded['email_work'] = isset($email['work']) ? $email['work'] : '';
        $decoded['email_other'] = isset($email['other']) ? $email['other'] : '';

        $address = json_decode((string)$contact['address'], true);
        $decoded['country_home'] = isset($address['home']['country']) ? $address['home']['country'] : '';
        $decoded['province_home'] = isset($address['home']['province']) ? $address['home']['province'] : '';
        $decoded['city_home'] = isset($address['home']['city']) ? $address['home']['city'] : '';
        $decoded['address_home'] = isset($address['home']['address']) ? $address['home']['address'] : '';
        $decoded['postal_code_home'] = isset($address['home']['postal_code']) ? $address['home']['postal_code'] : '';

        $decoded['country_work'] = isset($address['work']['country']) ? $address['work']['country'] : '';
        $decoded['province_work'] = isset($address['work']['province']) ? $address['work']['province'] : '';
        $decoded['city_work'] = isset($address['work']['city']) ? $address['work']['city'] : '';
        $decoded['address_work'] = isset($address['work']['address']) ? $address['work']['address'] : '';
        $decoded['postal_code_work'] = isset($address['work']['postal_code']) ? $address['work']['postal_code'] : '';

        $decoded['country_other'] = isset($address['other']['country']) ? $address['other']['country'] : '';
        $decoded['province_other'] = isset($address['other']['province']) ? $address['other']['province'] : '';
        $decoded['city_other'] = isset($address['other']['city']) ? $address['other']['city'] : '';
        $decoded['address_other'] = isset($address['other']['address']) ? $address['other']['address'] : '';
        $decoded['postal_code_other'] = isset($address['other']['postal_code']) ? $address['other']['postal_code'] : '';

        return $decoded;
    }

}