<?php
/**
 * SiteActivity Model
 *
 * @category    GadgetModel
 * @package     SiteActivity
 */
class SiteActivity_Model_SiteActivity extends Jaws_Gadget_Model
{
    /**
     * Get site activities
     *
     * @access  public
     * @param   string      $domain     Domain name ( if pass empty use currnet domain)
     * @param   integer     $date       Statistics day (UNIX timestamp)
     * @return bool True or error
     */
    function GetSiteActivities($domain = '0', $date = null)
    {
        $today = getdate();
        $date = empty($date) ? mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']) : $date;

        $table = Jaws_ORM::getInstance()->table('sa_activity')
            ->select(array('id:integer', 'domain', 'gadget', 'action', 'date:integer', 'hits:integer'))
            ->where('sync', false)
            ->and()->where('date', $date);
        if ($domain === '0') {
            $table->and()->where('domain', '', 'is null');
        } else {
            $table->and()->where('domain', $domain);
        }
        return $table->orderBy('gadget,action asc')->fetchAll();
    }


    /**
     * Update site activity sync status
     *
     * @access  public
     * @param   array   $ids    Activity Ids
     * @param   bool    $sync   Sync status
     * @return bool True or error
     */
    function UpdateSiteActivitySync($ids, $sync)
    {
        return Jaws_ORM::getInstance()->table('sa_activity')
            ->update(array('sync'=> $sync))
            ->where('id', $ids, 'in')->exec();
    }


    /**
     * Insert SiteActivity to db
     *
     * @access  public
     * @param   array       $data      Site activity data (gadget, action , hits, ...)
     * @return  bool        True or error
     */
    function InsertSiteActivity($data)
    {
        if (empty($data)) {
            return false;
        }

        $today = getdate();
        $time = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);

        $saTable = Jaws_ORM::getInstance()->table('sa_activity');
        $data['sync'] = false;
        $data['update_time'] = time();
        $data['date'] = $time;
        $data['hits'] = $saTable->expr('hits + ?', $data['hits']);
        $data['update_time'] = time();
        $res = $saTable->upsert($data)
            ->where('date', $time)
            ->and()->where('gadget', $data['gadget'])
            ->and()->where('action', $data['action'])
            ->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        return true;
    }
}