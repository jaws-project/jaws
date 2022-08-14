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
            $tel = json_decode($contact['tel'], true);
            $contact['tel_home'] = isset($tel['home']) ? $tel['home'] : '';
            $contact['tel_work'] = isset($tel['work']) ? $tel['work'] : '';
            $contact['tel_other'] = isset($tel['other']) ? $tel['other'] : '';
            unset($contact['tel']);

            $fax = json_decode($contact['fax'], true);
            $contact['fax_home'] = isset($fax['home']) ? $fax['home'] : '';
            $contact['fax_work'] = isset($fax['work']) ? $fax['work'] : '';
            $contact['fax_other'] = isset($fax['other']) ? $fax['other'] : '';
            unset($contact['fax']);

            $mobile = json_decode($contact['mobile'], true);
            $contact['mobile_home'] = isset($mobile['home']) ? $mobile['home'] : '';
            $contact['mobile_work'] = isset($mobile['work']) ? $mobile['work'] : '';
            $contact['mobile_other'] = isset($mobile['other']) ? $mobile['other'] : '';
            unset($contact['mobile']);

            $url = json_decode($contact['url'], true);
            $contact['url_home'] = isset($url['home']) ? $url['home'] : '';
            $contact['url_work'] = isset($url['work']) ? $url['work'] : '';
            $contact['url_other'] = isset($url['other']) ? $url['other'] : '';
            unset($contact['url']);

            $email = json_decode($contact['email'], true);
            $contact['email_home'] = isset($email['home']) ? $email['home'] : '';
            $contact['email_work'] = isset($email['work']) ? $email['work'] : '';
            $contact['email_other'] = isset($email['other']) ? $email['other'] : '';
            unset($contact['email']);

            $address = json_decode($contact['address'], true);
            $contact['country_home'] = isset($address['home']['country']) ? $address['home']['country'] : '';
            $contact['province_home'] = isset($address['home']['province']) ? $address['home']['province'] : '';
            $contact['city_home'] = isset($address['home']['city']) ? $address['home']['city'] : '';
            $contact['address_home'] = isset($address['home']['address']) ? $address['home']['address'] : '';
            $contact['postal_code_home'] = isset($address['home']['postal_code']) ? $address['home']['postal_code'] : '';

            $contact['country_work'] = isset($address['work']['country']) ? $address['work']['country'] : '';
            $contact['province_work'] = isset($address['work']['province']) ? $address['work']['province'] : '';
            $contact['city_work'] = isset($address['work']['city']) ? $address['work']['city'] : '';
            $contact['address_work'] = isset($address['work']['address']) ? $address['work']['address'] : '';
            $contact['postal_code_work'] = isset($address['work']['postal_code']) ? $address['work']['postal_code'] : '';

            $contact['country_other'] = isset($address['other']['country']) ? $address['other']['country'] : '';
            $contact['province_other'] = isset($address['other']['province']) ? $address['other']['province'] : '';
            $contact['city_other'] = isset($address['other']['city']) ? $address['other']['city'] : '';
            $contact['address_other'] = isset($address['other']['address']) ? $address['other']['address'] : '';
            $contact['postal_code_other'] = isset($address['other']['postal_code']) ? $address['other']['postal_code'] : '';
            unset($contact['address']);
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

}