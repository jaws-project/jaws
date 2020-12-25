<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 */
class Users_Model_Users extends Jaws_Gadget_Model
{
    /**
     * Get list of users
     *
     * @access  public
     * @param   int     $domain     Domain ID // 0: all domains
     * @param   int     $group      Group ID  // 0: all groups
     * @param   array   $filters    Users filters // status, superadmin, term
     *                  ex: array(
     *                      'status'  => 1,
     *                      'term' => 'smith'
     *                  )
     * @param   array   $fieldsets  Users fields sets // default, account, personal, password
     *                  ex: array('account'  => true)
     * @param   array   $orderBy    Field to order by
     *                  ex: array(
     *                      'id'       => true  // ascending,
     *                      'username' => false // descending
     *                  )
     * @param   int     $limit
     * @param   int     $offset
     * @return  array|Jaws_Error    Returns an array of the available users or Jaws_Error on error
     */
    function getUsers(
        $domain = 0, $group = 0,
        $filters = array(), $fieldsets = array(),
        $orderBy = array(), $limit = 0, $offset = null
    ) {
        $columns = array(
            'default'  => array(
                'users.domain:integer', 'users.id:integer', 'username', 'users.email', 'users.mobile',
                'nickname', 'contact:integer', 'avatar', 'status:integer'
            ),
            'account'  => array(
                'superadmin:boolean', 'concurrents:integer', 'logon_hours',
                'expiry_date:integer', 'registered_date:integer', 'bad_password_count:integer', 
                'last_update:integer', 'last_password_update:integer',
                'last_access:integer', 'verify_key'
            ),
            'personal' => array(
                'fname', 'lname', 'gender', 'ssn', 'dob:integer', 'extra', 'public:boolean', 'privacy:boolean',
                'pgpkey', 'signature', 'about', 'experiences', 'occupations', 'interests'
            ),
            'password' => array('password'),
        );
        $fieldsets['default'] = true;

        $selectedColumns = array();
        foreach ($fieldsets as $key => $keyValue) {
            if ($keyValue) {
                $selectedColumns = array_merge($selectedColumns, $columns[$key]);
            }
        }

        $objORM = Jaws_ORM::getInstance()
            ->table('users')
            ->select($selectedColumns)
            ->where('domain', (int)$domain, '=', empty($domain));
        // group
        if (!empty($group)) {
            $objORM->join('users_groups', 'users_groups.user', 'users.id');
            $objORM->where('group', (int)$group);
        }

        // filters
        $baseFilters = array(
            'term'       => '',
            'status'     => 0,
            'superadmin' => null,
        );
        // remove invalid filters keys
        $filters = array_intersect_key($filters, $baseFilters);
        // set undefined keys by default values
        $filters = array_merge($baseFilters, $filters);
        // status
        $objORM->and()->where('status', (int)$filters['status'], '=', empty($filters['status']));
        // superadmin
        $objORM->and()->where('superadmin', (bool)$filters['superadmin'], '=', is_null($filters['superadmin']));
        // term
        if (!empty($filters['term'])) {
            $term = Jaws_UTF8::strtolower($filters['term']);
            $objORM->and()
                ->openWhere('lower(username)', $term, 'like')
                ->or()
                ->where('lower(nickname)', $term, 'like')
                ->or()
                ->where('mobile', $term, 'like')
                ->or()
                ->closeWhere('lower(email)', $term, 'like');
        }

        // Order by
        $orders = array();
        if (empty($orderBy)) {
            $orderBy = array('id' => true);
        }
        foreach ($orderBy as $field => $ascending) {
            $orders[] = 'users.'. $field . ' '. ($ascending? 'asc' : 'desc');
        }
        call_user_func_array(array($objORM, 'orderBy'), $orders);

        return $objORM->limit($limit, $offset)->fetchAll();
    }

    /**
     * Get list of users
     *
     * @access  public
     * @param   int     $domain     Domain ID // 0: all domains
     * @param   int     $group      Group ID  // 0: all groups
     * @param   array   $filters    Users filters // status, superadmin, term
     *                  ex: array(
     *                      'status'  => 1,
     *                      'term' => 'smith'
     *                  )
     * @return  array|Jaws_Error    Returns an array of the available users or Jaws_Error on error
     */
    function getUsersCount($domain = 0, $group = 0, $filters = array())
    {
        $objORM = Jaws_ORM::getInstance()
            ->table('users')
            ->select('count(users.id):integer')
            ->where('domain', (int)$domain, '=', empty($domain));
        // group
        if (!empty($group)) {
            $objORM->join('users_groups', 'users_groups.user', 'users.id');
            $objORM->where('group', (int)$group);
        }

        // filters
        $baseFilters = array(
            'term'       => '',
            'status'     => 0,
            'superadmin' => null,
        );
        // remove invalid filters keys
        $filters = array_intersect_key($filters, $baseFilters);
        // set undefined keys by default values
        $filters = array_merge($baseFilters, $filters);
        // status
        $objORM->and()->where('status', (int)$filters['status'], '=', empty($filters['status']));
        // superadmin
        $objORM->and()->where('superadmin', (bool)$filters['superadmin'], '=', is_null($filters['superadmin']));
        // term
        if (!empty($filters['term'])) {
            $term = Jaws_UTF8::strtolower($filters['term']);
            $objORM->and()
                ->openWhere('lower(username)', $term, 'like')
                ->or()
                ->where('lower(nickname)', $term, 'like')
                ->or()
                ->where('mobile', $term, 'like')
                ->or()
                ->closeWhere('lower(email)', $term, 'like');
        }

        return $objORM->fetchOne();
    }

}