<?php
/**
 * Users Core Gadget
 *
 * @category   GadgetModel
 * @package    Users
 */
class Users_Model_Groups extends Jaws_Gadget_Model
{
    /**
     * Get list of groups
     *
     * @access  public
     * @param   int     $domain     Domain ID // 0: all domains
     * @param   int     $owner      The owner of group
     * @param   int     $user       User ID   // 0: all users
     * @param   array   $filters    Groups filters // enabled, term
     *                  ex: array(
     *                      'enabled'  => true,
     *                      'term' => 'operators'
     *                  )
     * @param   array   $fieldsets  Users fields sets // default
     *                  ex: array('enabled'  => true)
     * @param   array   $orderBy    Field to order by
     *                  ex: array(
     *                      'id'   => true  // ascending,
     *                      'name' => false // descending
     *                  )
     * @param   int     $limit
     * @param   int     $offset
     * @return  array|Jaws_Error    Returns an array of the available groups or Jaws_Error on error
     */
    //$owner = 0, $enabled = null, $orderBy = 'name', $limit = 0, $offset = null
    function getGroups(
        $domain = 0, $owner = 0, $user = 0,
        $filters = array(), $fieldsets = array(),
        $orderBy = array(), $limit = 0, $offset = null
    ) {
        $columns = array(
            'default'  => array(
                /*'groups.domain:integer', */
                'groups.id:integer', 'groups.owner:integer', 'groups.name', 'groups.title',
                'enabled:boolean'
            ),
        );
        $fieldsets['default'] = true;

        $selectedColumns = array();
        foreach ($fieldsets as $key => $keyValue) {
            if ($keyValue) {
                $selectedColumns = array_merge($selectedColumns, $columns[$key]);
            }
        }

        $objORM = Jaws_ORM::getInstance()
            ->table('groups')
            ->select($selectedColumns)
            //->where('domain', (int)$domain, '=', empty($domain))
            //->and()
            ->where('owner', (int)$owner);
        // user
        if (!empty($user)) {
            $objORM->join('users_groups', 'users_groups.group', 'groups.id');
            $objORM->and()->where('users_groups.user', (int)$user);
        }

        // filters
        $baseFilters = array(
            'term'       => '',
            'enabled'    => null,
        );
        // remove invalid filters keys
        $filters = array_intersect_key($filters, $baseFilters);
        // set undefined keys by default values
        $filters = array_merge($baseFilters, $filters);
        // enabled
        $objORM->and()->where('enabled', (bool)$filters['enabled'], '=', is_null($filters['enabled']));
        // term
        if (!empty($filters['term'])) {
            $term = Jaws_UTF8::strtolower($filters['term']);
            $objORM->and()
                ->openWhere('lower(name)', $term, 'like')
                ->or()
                ->closeWhere('lower(title)', $term, 'like');
        }

        // Order by
        $orders = array();
        if (empty($orderBy)) {
            $orderBy = array('id' => true);
        }
        foreach ($orderBy as $field => $ascending) {
            $orders[] = 'groups.'. $field . ' '. ($ascending? 'asc' : 'desc');
        }
        call_user_func_array(array($objORM, 'orderBy'), $orders);

        return $objORM->limit($limit, $offset)->fetchAll();
    }

    /**
     * Get count of groups
     *
     * @access  public
     * @param   int     $domain     Domain ID // 0: all domains
     * @param   int     $owner      The owner of group
     * @param   int     $user       User ID   // 0: all users
     * @param   array   $filters    Groups filters // enabled, term
     *                  ex: array(
     *                      'enabled'  => true,
     *                      'term' => 'operators'
     *                  )
     * @return  array|Jaws_Error    Returns an count of the available groups or Jaws_Error on error
     */
    function getGroupsCount($domain = 0, $owner = 0, $user = 0, $filters = array())
    {
        $objORM = Jaws_ORM::getInstance()
            ->table('groups')
            ->select('count(groups.id):integer')
            //->where('domain', (int)$domain, '=', empty($domain))
            //->and()
            ->where('owner', (int)$owner);
        // user
        if (!empty($user)) {
            $objORM->join('users_groups', 'users_groups.group', 'groups.id');
            $objORM->and()->where('users_groups.user', (int)$user);
        }

        // filters
        $baseFilters = array(
            'term'       => '',
            'enabled'    => null,
        );
        // remove invalid filters keys
        $filters = array_intersect_key($filters, $baseFilters);
        // set undefined keys by default values
        $filters = array_merge($baseFilters, $filters);
        // enabled
        $objORM->and()->where('enabled', (bool)$filters['enabled'], '=', is_null($filters['enabled']));
        // term
        if (!empty($filters['term'])) {
            $term = Jaws_UTF8::strtolower($filters['term']);
            $objORM->and()
                ->openWhere('lower(name)', $term, 'like')
                ->or()
                ->closeWhere('lower(title)', $term, 'like');
        }

        return $objORM->fetchOne();
    }

}