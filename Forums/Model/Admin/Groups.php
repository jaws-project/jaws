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
class Forums_Model_Admin_Groups extends Jaws_Gadget_Model
{
    /**
    * Insert a group
    * 
    * @access  public
    * @param   string  $title          group title
    * @param   string  $description    group description
    * @param   string  $fast_url
    * @param   string  $order
    * @param   bool    $locked
    * @param   bool    $published
    * @return  bool    True on Success and False on Failure
    */
    function InsertGroup($title, $description, $fast_url, $order, $locked, $published)
    {
        $fast_url = empty($fast_url)? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'forums_groups');

        $sql = '
            INSERT INTO [[forums_groups]]
                ([title], [description], [fast_url], [order], [locked], [published])
            VALUES
                ({title}, {description}, {fast_url}, {order}, {locked}, {published})';

        $params = array();
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

        return $GLOBALS['db']->lastInsertID('forums', 'id');;
    }

    /**
    * Update a group
    * 
    * @access  public
    * @param   int     $gid            group ID
    * @param   string  $title          group title
    * @param   string  $description    group description
    * @param   string  $fast_url
    * @param   string  $order
    * @param   bool    $locked
    * @param   bool    $published
    * @return  bool    True on Success and False on Failure
    */
    function UpdateGroup($gid, $title, $description, $fast_url, $order, $locked, $published)
    {
        $fast_url = empty($fast_url)? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'forums_groups', false);

        $sql = '
            UPDATE [[forums_groups]]
            SET
                [title]       = {title},
                [description] = {description},
                [fast_url]    = {fast_url},
                [order]       = {order},
                [locked]      = {locked},
                [published]   = {published}
            WHERE [id] = {gid}';

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

        return true;
    }

    /**
     * Delete a group
     *
     * @access  public
     * @param   int     $gid    Group ID
     * @return  mixed   True if query was successful or Jaws_Error on error
     */
    function DeleteGroup($gid)
    {
        $params = array();
        $params['gid']  = (int)$gid;
        $params['zero'] = 0;

        $sql = '
            DELETE FROM [[forums_groups]]
            WHERE
                [id] = {gid}
              AND
                (SELECT COUNT([id]) FROM [[forums]] WHERE [gid] = {gid}) = {zero}';

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        return (bool)$res;
    }

}