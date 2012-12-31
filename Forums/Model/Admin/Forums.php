<?php
/**
 * Forums Gadget
 *
 * @category   GadgetModel
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Model_Admin_Forums extends Jaws_Gadget_Model
{
    /**
    * Insert a group
    * 
    * @access  public
    * @param    int     $gid            group ID
    * @param    string  $title          group title
    * @param    string  $description    group description
    * @param    string  $fast_url
    * @param    string  $order
    * @param    bool    $locked
    * @param    bool    $published
    * @return   bool    True on Success and False on Failure
    */
    function InsertForum($gid, $title, $description, $fast_url, $order, $locked, $published)
    {
        $fast_url = empty($fast_url)? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'forums');

        $sql = '
            INSERT INTO [[forums]]
                ([gid], [title], [description], [fast_url], [order], [locked], [published])
            VALUES
                ({gid}, {title}, {description}, {fast_url}, {order}, {locked}, {published})';

        $params = array();
        $params['gid']         = $gid;
        $params['title']       = $title;
        $params['description'] = $description;
        $params['fast_url']    = $fast_url;
        $params['order']       = (int) $order;
        $params['locked']      = (bool) $locked;
        $params['published']   = (bool) $published;

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        return $GLOBALS['db']->lastInsertID('forums', 'id');
    }

    /**
    * Update a forum
    * 
    * @access  public
    * @param    int     $fid            forum ID
    * @param    int     $gid            group ID
    * @param    string  $title          forum title
    * @param    string  $description    forum description
    * @param    string  $fast_url
    * @param    string  $order
    * @param    bool    $locked
    * @param    bool    $published
    * @return   mixed   True on Success and Jaws_Error on Failure
    */
    function UpdateForum($fid, $gid, $title, $description, $fast_url, $order, $locked, $published)
    {
        $fast_url = empty($fast_url)? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'forums', false);

        $sql = '
            UPDATE [[forums]]
            SET
                [gid]         = {gid},
                [title]       = {title},
                [description] = {description},
                [fast_url]    = {fast_url},
                [order]       = {order},
                [locked]      = {locked},
                [published]   = {published}
            WHERE [id] = {fid}';

        $params = array();
        $params['fid']         = $fid;
        $params['gid']         = $gid;
        $params['title']       = $title;
        $params['description'] = $description;
        $params['fast_url']    = $fast_url;
        $params['order']       = (int) $order;
        $params['locked']      = (bool) $locked;
        $params['published']   = (bool) $published;

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        return true;
    }

    /**
     * Delete a forum
     *
     * @access  public
     * @param   int     $fid    Forum ID
     * @return  mixed   True if query was successful or Jaws_Error on error
     */
    function DeleteForum($fid)
    {
        $params = array();
        $params['fid']  = (int)$fid;
        $params['zero'] = 0;

        $sql = '
            DELETE FROM [[forums]]
            WHERE
                [id] = {fid}
              AND
                (SELECT COUNT([id]) FROM [[forums_topics]] WHERE [fid] = {fid}) = {zero}';

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        return (bool)$res;
    }

}