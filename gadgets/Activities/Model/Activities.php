<?php
/**
 * Activities Model
 *
 * @category    GadgetModel
 * @package     Activities
 */
class Activities_Model_Activities extends Jaws_Gadget_Model
{
    /**
     * Get activities
     *
     * @access  public
     * @param   array   $filters
     * @param   bool    $limit
     * @param   int     $offset
     * @param   string  $order
     * @return bool True or error
     */
    function GetActivities($filters = null, $limit = false, $offset = null, $order = 'gadget,action asc')
    {
        $saTable = Jaws_ORM::getInstance()->table('activities')
            ->select('id:integer', 'domain', 'gadget', 'action', 'date:integer', 'hits:integer');

        if (!empty($filters) && count($filters) > 0) {
            // from_date
            if (isset($filters['from_date']) && !empty($filters['from_date'])) {
                if (!is_numeric($filters['from_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['from_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['from_date']), 'U')
                    );
                }
                $saTable->and()->where('date', $filters['from_date'], '>=');
            }
            // to_date
            if (isset($filters['to_date']) && !empty($filters['to_date'])) {
                if (!is_numeric($filters['to_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['to_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['to_date']), 'U')
                    );
                }
                $saTable->and()->where('date', $filters['to_date'], '<=');
            }
            // gadget
            if (isset($filters['gadget']) && !empty($filters['gadget'])) {
                $saTable->and()->where('gadget', $filters['gadget']);
            }
            // domain
            if ($filters['domain'] != '-1') {
                $saTable->and()->where('domain', $filters['domain']);
            }
            // sync
            if (isset($filters['sync'])) {
                $saTable->and()->where('sync', (bool)$filters['sync']);
            }
        }

        return $saTable->limit((int)$limit, $offset)->orderBy($order)->fetchAll();
    }

    /**
     * Get activities count
     *
     * @access  public
     * @param   array   $filters
     * @return bool True or error
     */
    function GetActivitiesCount($filters = null)
    {
        $saTable = Jaws_ORM::getInstance()->table('activities')
            ->select('count(id):integer');

        if (!empty($filters) && count($filters) > 0) {
            // from_date
            if (isset($filters['from_date']) && !empty($filters['from_date'])) {
                if (!is_numeric($filters['from_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['from_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['from_date']), 'U')
                    );
                }
                $saTable->and()->where('date', $filters['from_date'], '>=');
            }
            // to_date
            if (isset($filters['to_date']) && !empty($filters['to_date'])) {
                if (!is_numeric($filters['to_date'])) {
                    $objDate = Jaws_Date::getInstance();
                    $filters['to_date'] = $GLOBALS['app']->UserTime2UTC(
                        (int)$objDate->ToBaseDate(preg_split('/[- :]/', $filters['to_date']), 'U')
                    );
                }
                $saTable->and()->where('date', $filters['to_date'], '<=');
            }
            // gadget
            if (isset($filters['gadget']) && !empty($filters['gadget'])) {
                $saTable->and()->where('gadget', $filters['gadget']);
            }
            // domain
            if ($filters['domain'] != '-1') {
                $saTable->and()->where('domain', $filters['domain']);
            }
            // sync
            if (isset($filters['sync'])) {
                $saTable->and()->where('sync', (bool)$filters['sync']);
            }
        }

        return $saTable->fetchOne();
    }

    /**
     * Get all domain list
     *
     * @access  public
     * @return bool True or error
     */
    function GetAllDomains()
    {
        return Jaws_ORM::getInstance()->table('activities')
            ->select('domain')->groupBy('domain')->fetchColumn();
    }

    /**
     * Update activity sync status
     *
     * @access  public
     * @param   array   $ids    Activity Ids
     * @param   bool    $sync   Sync status
     * @return  mixed   Integer or Jaws_Error on failure
     */
    function UpdateActivitiesSync($ids, $sync)
    {
        return Jaws_ORM::getInstance()->table('activities')
            ->update(array('sync'=> (bool)$sync))
            ->where('id', $ids, 'in')->exec();
    }

    /**
     * Inserts an activity to db
     *
     * @access  public
     * @param   array   $data   Activity data (gadget, action , hits, ...)
     * @return  mixed   True or Jaws_Error on failure
     */
    function InsertActivity($data)
    {
        if (empty($data)) {
            return false;
        }

        $now = getdate();
        $todayTime = mktime(0, 0, 0, $now['mon'], $now['mday'], $now['year']);

        $objORM = Jaws_ORM::getInstance()->table('activities');
        $data['domain'] = '';
        $data['date'] = $todayTime;
        $data['hits'] = $data['hits'];
        $data['update_time'] = $now[0];
        return $objORM->upsert($data, array('hits' => $objORM->expr('hits + ?', $data['hits'])))
            ->where('domain', $data['domain'])
            ->and()->where('gadget', $data['gadget'])
            ->and()->where('action', $data['action'])
            ->and()->where('date', $data['date'])
            ->exec();
    }

    /**
     * Inserts mass activities to db
     *
     * @access  public
     * @param   array   $activities     Array of activities data (gadget, action , hits, ...)
     * @return  bool    True or Jaws_Error on failure
     */
    function InsertActivities($activities)
    {
        if (empty($activities)) {
            return false;
        }

        // FIXME : increase performance by adding upsertAll method in core
        $objORM = Jaws_ORM::getInstance()->beginTransaction();
        $objORM->table('activities');
        foreach($activities as $activity) {
            $res = $saTable->upsert($activity)
                ->where('domain', $activity['domain'])
                ->and()->where('gadget', $activity['gadget'])
                ->and()->where('action', $activity['action'])
                ->and()->where('date', $activity['date'])
                ->exec();
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
        }

        //Commit Transaction
        $objORM->commit();
        return true;
    }

    /**
     * Gets list of hooked gadgets by Activities
     *
     * @access  public
     * @return  array   List of gadgets
     */
    function GetHookedGadgets()
    {
        $result = array();
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgets = $cmpModel->GetGadgetsList(null, true, true);
        foreach ($gadgets as $gadget => $info) {
            if (file_exists(JAWS_PATH . "gadgets/$gadget/Hooks/Activities.php")) {
                $result[$gadget] = $info['title'];
            }
            
        }

        return $result;
    }

}