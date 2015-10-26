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

        $data['gid']         = $gid;
        $data['title']       = $title;
        $data['description'] = $description;
        $data['fast_url']    = $fast_url;
        $data['order']       = (int) $order;
        $data['locked']      = (bool) $locked;
        $data['published']   = (bool) $published;

        $table = Jaws_ORM::getInstance()->table('forums');
        $res = $table->insert($data)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        $this->gadget->acl->insert('ForumPublic', $res, true);
        $this->gadget->acl->insert('ForumMember', $res, false);
        $this->gadget->acl->insert('ForumManage', $res, false);
        return $res;
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

        $data['gid']         = $gid;
        $data['title']       = $title;
        $data['description'] = $description;
        $data['fast_url']    = $fast_url;
        $data['order']       = (int) $order;
        $data['locked']      = (bool) $locked;
        $data['published']   = (bool) $published;

        $table = Jaws_ORM::getInstance()->table('forums');
        $res = $table->update($data)->where('id', $fid)->exec();
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
        $table = Jaws_ORM::getInstance()->table('forums');
        $subQuery = Jaws_ORM::getInstance()->table('forums_topics')->select('count(id)')->where('fid', $fid);
        $res = $table->delete()->where('id', $fid)->and()->where($subQuery, '0')->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
        }

        $this->gadget->acl->delete('ForumPublic', $fid);
        $this->gadget->acl->delete('ForumMember', $fid);
        $this->gadget->acl->delete('ForumManage', $fid);
        return (bool)$res;
    }

}