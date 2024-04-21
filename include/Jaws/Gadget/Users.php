<?php
/**
 * Jaws Gadget Users
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Users extends Jaws_Gadget_Class
{
    /**
     * Insert custom user's attributes of gadget
     *
     * @access  public
     * @param   int     $user   User ID
     * @param   array   $attrs  User's attributes
     * @return  bool    Returns True or Jaws_Error on Failure
     */
    function insertAttributes($user, $attrs = array())
    {
        $attrs['user'] = (int)$user;
        $tableName = strtolower('users_'.$this->gadget->name);
        $objORM = Jaws_ORM::getInstance()->table($tableName);
        return $objORM->insert($attrs)->exec();
    }

    /**
     * Update custom user's attributes of gadget
     *
     * @access  public
     * @param   int     $user   User ID
     * @param   array   $attrs  User's attributes
     * @return  bool    Returns True or Jaws_Error on Failure
     */
    function updateAttributes($user, $attrs = array())
    {
        $tableName = strtolower('users_'.$this->gadget->name);
        $objORM = Jaws_ORM::getInstance()->table($tableName);
        return $objORM->update($attrs)->where('user', (int)$user)->exec();
    }

    /**
     * Insert/Update custom user's attributes of gadget
     *
     * @access  public
     * @param   int     $user   User ID
     * @param   array   $attrs  User's attributes
     * @return  bool    Returns True or Jaws_Error on Failure
     */
    function upsertAttributes($user, $attrs = array())
    {
        $attrs['user'] = (int)$user;
        $tableName = strtolower('users_'.$this->gadget->name);
        $objORM = Jaws_ORM::getInstance()->table($tableName);
        return $objORM->upsert($attrs)->where('user', (int)$user)->exec();
    }

    /**
     * delete custom user's attributes of gadget
     *
     * @access  public
     * @param   int     $user   User ID
     * @return  bool    Returns True or Jaws_Error on Failure
     */
    function deleteAttributes($user)
    {
        $tableName = strtolower('users_'.$this->gadget->name);
        $objORM = Jaws_ORM::getInstance()->table($tableName);
        return $objORM->delete()->where('user', (int)$user)->exec();
    }

    /**
     * Fetch user's attributes
     *
     * @access  public
     * @param   int     $user           User ID
     * @param   array   $attributes     User's custom/default attributes
     * @param   string  $join           Join type(left, inner, right, ...)
     * @return  mixed   Returns array of user's attributes or Jaws_Error on Failure
     */
    function fetch($user, $attributes, $join = 'left')
    {
        $attributes = array(
            'default' => (array)@$attributes['default'],
            'custom'  => (array)@$attributes['custom']
        );

        $tableName = strtolower('users_'.$this->gadget->name);
        array_walk(
            $attributes['custom'],
            function(&$value, $key, $prefix) {
                $value = $prefix. '.'. $value;
            },
            $tableName
        );

        array_walk(
            $attributes['default'],
            function(&$value, $key) {
                $value = 'users.'. $value;
            }
        );

        return Jaws_ORM::getInstance()->table('users')
            ->select(array_merge($attributes['default'], $attributes['custom']))
            ->join($tableName, $tableName.'.user', 'users.id', $join)
            ->where('users.id', (int)$user)
            ->fetchRow();
    }

    /**
     * Build filter conditions query
     *
     * @access  public
     * @param   object  $objORM     Jaws_ORM object
     * @param   array   $filters    Filters array
     * @return  void
     */
    private function buildFilters(&$objORM, $tableName, &$filters, $op = 'and')
    {
        foreach ($filters as $filter) {
            if (is_array($filter[0])) {
                $objORM->openWhere();
                $this->buildFilters($objORM, $tableName, $filter, 'or');
                $objORM->closeWhere();
            } else {
                $objORM->where(
                    $tableName. '.'. $filter[0],
                    $filter[1],
                    array_key_exists(2, $filter)? $filter[2] : '=',
                    array_key_exists(3, $filter)? (bool)$filter[3] : false
                );
            }

            $op == 'and'? $objORM->and() : $objORM->or();
        }
    }

    /**
     * Fetch users include default/custom attributes
     *
     * @access  public
     * @param   array   $attributes     User's custom/default attributes
     * @param   array   $filters        Filters
     * @param   string  $join           Join type(left, inner, right, ...)
     * @param   int     $limit          Count of users to be returned
     * @param   int     $offset         Offset of data array
     * @return  mixed   Returns array of users or Jaws_Error on Failure
     */
    function fetchAll($attributes, $filters, $join = 'left', $limit = false, $offset = null)
    {
        $attributes = array(
            'default' => (array)@$attributes['default'],
            'custom'  => (array)@$attributes['custom']
        );

        $tableName = strtolower('users_'.$this->gadget->name);
        array_walk(
            $attributes['custom'],
            function(&$value, $key, $prefix) {
                $value = $prefix. '.'. $value;
            },
            $tableName
        );

        array_walk(
            $attributes['default'],
            function(&$value, $key) {
                $value = 'users.'. $value;
            }
        );

        $objORM = Jaws_ORM::getInstance()
            ->table('users')
            ->select(array_merge($attributes['default'], $attributes['custom']))
            ->join($tableName, $tableName.'.user', 'users.id', $join);

        // default attributes filters
        $this->buildFilters($objORM, 'users', $filters['default']);
        // custom attributes filters
        $this->buildFilters($objORM, $tableName, $filters['custom']);

        return $objORM->orderBy('users.id')->limit((int)$limit, $offset)->fetchAll();
    }

    /**
     * Count of users filtered by default/custom filters
     *
     * @access  public
     * @param   array   $filters    Filters
     * @param   string  $join       Join type(left, inner, right, ...)
     * @return  mixed   Returns count of filtered users array or Jaws_Error on Failure
     */
    function count($filters, $join = 'left')
    {
        $tableName = strtolower('users_'.$this->gadget->name);
        $objORM = Jaws_ORM::getInstance()
            ->table('users')
            ->select('count(users.id):integer')
            ->join($tableName, $tableName.'.user', 'users.id', $join);

        // default attributes filters
        $this->buildFilters($objORM, 'users', $filters['default']);
        // custom attributes filters
        $this->buildFilters($objORM, $tableName, $filters['custom']);

        return $objORM->fetchOne();
    }
}