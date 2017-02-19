<?php
/**
 * Forums Gadget
 *
 * @category   GadgetModel
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2015 Jaws Development Group
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

        $data['title']       = $title;
        $data['description'] = $description;
        $data['fast_url']    = $fast_url;
        $data['order']       = (int) $order;
        $data['locked']      = (bool) $locked;
        $data['published']   = (bool) $published;

        $table = Jaws_ORM::getInstance()->table('forums_groups');
        $res = $table->insert($data)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        $this->gadget->acl->insert('GroupAccess', $res, true);
        return $res;
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

        $data['title']       = $title;
        $data['description'] = $description;
        $data['fast_url']    = $fast_url;
        $data['order']       = (int) $order;
        $data['locked']      = (bool) $locked;
        $data['published']   = (bool) $published;

        $table = Jaws_ORM::getInstance()->table('forums_groups');
        $res = $table->update($data)->where('id', $gid)->exec();
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
        $table = Jaws_ORM::getInstance()->table('forums_groups');
        $subQuery = Jaws_ORM::getInstance()->table('forums')->select('count(id)')->where('gid', $gid);
        $res = $table->delete()->where('id', $gid)->and()->where($subQuery, '0')->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        $this->gadget->acl->delete('GroupAccess', $gid);
        return (bool)$res;
    }

}