<?php
/**
 * Policy Gadget Admin
 *
 * @category   GadgetModel
 * @package    Policy
 */
class Policy_Model_Admin_Zone extends Jaws_Gadget_Model
{
    /**
     * Insert a new zone
     *
     * @access  public
     * @param   array   $data       Zone data
     * @return  bool|Jaws_Error     True or Jaws_Error on failure
     */
    function InsertZone($data)
    {
        return Jaws_ORM::getInstance()->table('policy_zone')->insert($data)->exec();
    }

    /**
     * Update a zone info
     *
     * @access  public
     * @param   int     $id         Zone id
     * @param   array   $data       Zone data
     * @return  bool|Jaws_Error     True or Jaws_Error on failure
     */
    function UpdateZone($id, $data)
    {
        return Jaws_ORM::getInstance()->table('policy_zone')->update($data)->where('id', (int)$id)->exec();
    }

    /**
     * Delete a zone
     *
     * @access  public
     * @param   int                 $id ID of the zone
     * @return  bool|Jaws_Error     True on successful attempts and Jaws_Error otherwise
     */
    function DeleteZone($id)
    {
        return Jaws_ORM::getInstance()->table('policy_zone')->delete()->where('id', (int)$id)->exec();
    }

    /**
     * Get a zone info
     *
     * @access  public
     * @param   int     $id    Zone id
     * @return  array   zone info or Jaws_Error on failure
     */
    function GetZone($id)
    {
        return Jaws_ORM::getInstance()->table('policy_zone')
            ->select('id:integer', 'title')
            ->where('id', (int)$id)
            ->fetchRow();
    }

    /**
     * Get Zones list
     *
     * @access  public
     * @param   bool|int $limit     Count of logs to be returned
     * @param   int      $offset    Offset of data array
     * @return  array zones info or Jaws_Error on failure
     */
    function GetZones($limit = false, $offset = null)
    {
        return Jaws_ORM::getInstance()->table('policy_zone')
            ->select('id:integer', 'title')
            ->limit((int)$limit, $offset)
            ->fetchAll();
    }

    /**
     * Get Zones count
     *
     * @access  public
     * @return  array zones info or Jaws_Error on failure
     */
    function GetZonesCount()
    {
        return Jaws_ORM::getInstance()->table('policy_zone')
            ->select('count(id):integer')
            ->fetchOne();
    }

    /**
     * Insert a new zone range
     *
     * @access  public
     * @param   array   $data       Zone range data
     * @return  bool|Jaws_Error     True or Jaws_Error on failure
     */
    function InsertZoneRange($data)
    {
        return Jaws_ORM::getInstance()->table('policy_zone_range')->insert($data)->exec();
    }

    /**
     * Update a zone range info
     *
     * @access  public
     * @param   int     $id         Zone range id
     * @param   array   $data       Zone range data
     * @return  bool|Jaws_Error     True or Jaws_Error on failure
     */
    function UpdateZoneRange($id, $data)
    {
        return Jaws_ORM::getInstance()->table('policy_zone_range')->update($data)->where('id', (int)$id)->exec();
    }

    /**
     * Delete a zone range
     *
     * @access  public
     * @param   int                 $id ID of the zone range
     * @return  bool|Jaws_Error     True on successful attempts and Jaws_Error otherwise
     */
    function DeleteZoneRange($id)
    {
        return Jaws_ORM::getInstance()->table('policy_zone_range')->delete()->where('id', (int)$id)->exec();
    }

    /**
     * Get a zone range info
     *
     * @access  public
     * @param   int     $id    Zone range id
     * @return  array   zone range info or Jaws_Error on failure
     */
    function GetZoneRange($id)
    {
        return Jaws_ORM::getInstance()->table('policy_zone_range')
            ->select('id:integer', 'zone:integer', 'from', 'to')
            ->where('id', (int)$id)
            ->fetchRow();
    }

    /**
     * Get Zone ranges list
     *
     * @access  public
     * @param   array    $filters   Filters
     * @param   bool|int $limit     Count of logs to be returned
     * @param   int      $offset    Offset of data array
     * @return  array   zone ranges info or Jaws_Error on failure
     */
    function GetZoneRanges($filters = array(), $limit = false, $offset = null)
    {
        return Jaws_ORM::getInstance()->table('policy_zone_range')
            ->select('id:integer', 'zone:integer', 'from', 'to')
            ->where(
                'zone',
                @(int)$filters['zone'],
                '=',
                empty($filters['zone'])
            )->limit((int)$limit, $offset)
            ->fetchAll();
    }

    /**
     * Get Zone ranges count
     *
     * @access  public
     * @param   array    $filters   Filters
     * @return  int      zones ranges count or Jaws_Error on failure
     */
    function GetZoneRangesCount($filters = array())
    {
        return Jaws_ORM::getInstance()->table('policy_zone_range')
            ->select('count(id):integer')
            ->where(
                'zone',
                @(int)$filters['zone'],
                '=',
                empty($filters['zone'])
            )->fetchOne();
    }

    /**
     * Get Zone actions list
     *
     * @access  public
     * @param   array    $filters
     * @param   bool|int $limit     Count of logs to be returned
     * @param   int      $offset    Offset of data array
     * @return  array zones info or Jaws_Error on failure
     */
    function GetZoneActions($filters = null, $limit = false, $offset = null)
    {
        return Jaws_ORM::getInstance()->table('policy_zone_action')
            ->select(
                'policy_zone_action.id:integer', 'zone:integer', 'script:integer', 'gadget', 'action',
                'order:integer', 'access:boolean', 'policy_zone.title as zone_title'
            )->join('policy_zone', 'policy_zone.id', 'policy_zone_action.zone')
            ->and()->where(
                'zone',
                @$filters['zone'],
                '=',
                @empty($filters['zone'])
            )->and()->where(
                'script',
                @(int)$filters['script'],
                '=',
                @empty($filters['script'])
            )->and()->where(
                'gadget',
                @$filters['gadget'],
                '=',
                @empty($filters['gadget'])
            )->and()->where(
                'action',
                @$filters['action'],
                '=',
                @empty($filters['action'])
            )->and()->where(
                'access',
                @(bool)$filters['access'],
                '=',
                @!isset($filters['access'])
            )->limit((int)$limit, $offset)
            ->orderBy('order')
            ->fetchAll();
    }

    /**
     * Get Zone actions count
     *
     * @param   array    $filters
     * @access  public
     * @return  array zones info or Jaws_Error on failure
     */
    function GetZoneActionsCount($filters = null)
    {
        return Jaws_ORM::getInstance()->table('policy_zone_action')
            ->select('count(id):integer')
            ->and()->where(
                'zone',
                @$filters['zone'],
                '=',
                @empty($filters['zone'])
            )->and()->where(
                'script',
                @(int)$filters['script'],
                '=',
                @empty($filters['script'])
            )->and()->where(
                'gadget',
                @$filters['gadget'],
                '=',
                @empty($filters['gadget'])
            )->and()->where(
                'action',
                @$filters['action'],
                '=',
                @empty($filters['action'])
            )->and()->where(
                'access',
                @(bool)$filters['access'],
                '=',
                @!isset($filters['access'])
            )->fetchOne();
    }

    /**
     * Get a zone action info
     *
     * @access  public
     * @param   int     $id    Zone id
     * @return  array   zone action info or Jaws_Error on failure
     */
    function GetZoneAction($id)
    {
        return Jaws_ORM::getInstance()->table('policy_zone_action')
            ->select(
                'id:integer', 'zone:integer', 'script:integer', 'gadget', 'action', 'order:integer', 'access:boolean'
            )->where('id', (int)$id)
            ->fetchRow();
    }

    /**
     * Insert a new zone action
     *
     * @access  public
     * @param   array   $data       Zone action data
     * @return  bool|Jaws_Error     True or Jaws_Error on failure
     */
    function InsertZoneAction($data)
    {
        return Jaws_ORM::getInstance()->table('policy_zone_action')->insert($data)->exec();
    }

    /**
     * Update a zone action info
     *
     * @access  public
     * @param   int     $id         Zone action id
     * @param   array   $data       Zone action data
     * @return  bool|Jaws_Error     True or Jaws_Error on failure
     */
    function UpdateZoneAction($id, $data)
    {
        return Jaws_ORM::getInstance()->table('policy_zone_action')->update($data)->where('id', (int)$id)->exec();
    }

    /**
     * Delete a zone action
     *
     * @access  public
     * @param   int                 $id ID of the zone action
     * @return  bool|Jaws_Error     True on successful attempts and Jaws_Error otherwise
     */
    function DeleteZoneAction($id)
    {
        return Jaws_ORM::getInstance()->table('policy_zone_action')->delete()->where('id', (int)$id)->exec();
    }

}