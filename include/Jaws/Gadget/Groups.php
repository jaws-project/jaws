<?php
/**
 * Jaws Gadget Users
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Groups
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  protected
     */
    var $gadget = null;

    /**
     * constructor
     *
     * @access  public
     * @param   object  $gadget Jaws_Gadget object
     * @return  void
     */
    function __construct($gadget)
    {
        $this->gadget = $gadget;
        $this->app = Jaws::getInstance();
    }

    /**
     * Insert custom group's attributes of gadget
     *
     * @access  public
     * @param   int     $group  Group ID
     * @param   array   $attrs  Group's attributes
     * @return  bool    Returns True or Jaws_Error on Failure
     */
    function insertAttributes($group, $attrs = array())
    {
        $attrs['group'] = (int)$group;
        $tableName = strtolower('groups_'.$this->gadget->name);
        $objORM = Jaws_ORM::getInstance()->table($tableName);
        return $objORM->insert($attrs)->exec();
    }

    /**
     * Update custom group's attributes of gadget
     *
     * @access  public
     * @param   int     $group  Group ID
     * @param   array   $attrs  Group's attributes
     * @return  bool    Returns True or Jaws_Error on Failure
     */
    function updateAttributes($group, $attrs = array())
    {
        $tableName = strtolower('groups_'.$this->gadget->name);
        $objORM = Jaws_ORM::getInstance()->table($tableName);
        return $objORM->update($attrs)->where('group', (int)$group)->exec();
    }

    /**
     * delete custom group's attributes of gadget
     *
     * @access  public
     * @param   int     $group  Group ID
     * @return  bool    Returns True or Jaws_Error on Failure
     */
    function deleteAttributes($group)
    {
        $tableName = strtolower('groups_'.$this->gadget->name);
        $objORM = Jaws_ORM::getInstance()->table($tableName);
        return $objORM->delete()->where('group', (int)$group)->exec();
    }

    /**
     * Fetch a group include default/custom attributes
     *
     * @access  public
     * @param   int     $group          Group ID
     * @param   array   $attributes     Group's custom/default attributes
     * @return  mixed   Returns array of group's attributes or Jaws_Error on Failure
     */
    function fetch($group, $attributes)
    {
        $tableName = strtolower('groups_'.$this->gadget->name);
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
                $value = 'groups.'. $value;
            }
        );

        return Jaws_ORM::getInstance()->table('groups')
            ->select(array_merge($attributes['default'], $attributes['custom']))
            ->join($tableName, $tableName.'.group', 'groups.id', 'left')
            ->where('groups.id', (int)$group)
            ->fetchRow();
    }

    /**
     * Fetch groups include default/custom attributes
     *
     * @access  public
     * @param   array   $attributes     Group's custom/default attributes
     * @param   array   $filters        Filters
     * @param   int     $limit          Count of groups to be returned
     * @param   int     $offset         Offset of data array
     * @return  mixed   Returns array of groups or Jaws_Error on Failure
     */
    function fetchAll($attributes, $filters, $limit = false, $offset = null)
    {
        $tableName = strtolower('groups_'.$this->gadget->name);
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
                $value = 'groups.'. $value;
            }
        );

        $objORM = Jaws_ORM::getInstance()
            ->table($tableName)
            ->select(array_merge($attributes['default'], $attributes['custom']))
            ->join($tableName, $tableName.'.group', 'groups.id', 'left');

        // default attributes filters
        foreach ($filters['default'] as $filter) {
            $objORM->and()->where(
                'groups.'.$filter[0],
                $filter[1],
                array_key_exists(2, $filter)? $filter[2] : '=',
                array_key_exists(3, $filter)? (bool)$filter[3] : false
            );
        }
        // custom attributes filters
        foreach ($filters['custom'] as $filter) {
            $objORM->and()->where(
                $tableName. '.'. $filter[0],
                $filter[1],
                array_key_exists(2, $filter)? $filter[2] : '=',
                array_key_exists(3, $filter)? (bool)$filter[3] : false
            );
        }

        return $objORM->orderBy('groups.id')->limit((int)$limit, $offset)->fetchAll();
    }

    /**
     * Count of groups filtered by default/custom filters
     *
     * @access  public
     * @param   array   $filters    Filters
     * @return  mixed   Returns count of filtered groups array or Jaws_Error on Failure
     */
    function count($filters)
    {
        $tableName = strtolower('groups_'.$this->gadget->name);
        $objORM = Jaws_ORM::getInstance()
            ->table('groups')
            ->select('count(groups.id):integer')
            ->join($tableName, $tableName.'.group', 'groups.id', 'left');

        // default attributes filters
        foreach ($filters['default'] as $filter) {
            $objORM->and()->where(
                'groups.'.$filter[0],
                $filter[1],
                array_key_exists(2, $filter)? $filter[2] : '=',
                array_key_exists(3, $filter)? (bool)$filter[3] : false
            );
        }
        // custom attributes filters
        foreach ($filters['custom'] as $filter) {
            $objORM->and()->where(
                $tableName. '.'. $filter[0],
                $filter[1],
                array_key_exists(2, $filter)? $filter[2] : '=',
                array_key_exists(3, $filter)? (bool)$filter[3] : false
            );
        }

        return $objORM->fetchOne();
    }
}