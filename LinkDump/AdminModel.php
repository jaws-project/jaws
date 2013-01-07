<?php
require_once JAWS_PATH . 'gadgets/LinkDump/Model.php';
/**
 * LinkDump Gadget Admin
 *
 * @category   GadgetModel
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_AdminModel extends LinkDump_Model
{
    /**
    * Insert a link
    * 
    * @access  public
    * @param    int     $gid        group ID
    * @param    string  $title      link title
    * @param    string  $url        url address
    * @param    string  $fast_url
    * @param    string  $desc       description
    * @param    string  $tags
    * @param    string  $rank
    * @return   mixed   True on Success and Jaws_Error on Failure
    */
    function InsertLink($gid, $title, $url, $fast_url, $desc, $tags, $rank)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'linkdump_links');

        $lData['title']       = $title;
        $lData['description'] = $desc;
        $lData['url']         = $url;
        $lData['fast_url']    = $fast_url;
        $lData['gid']         = $gid;
        $lData['rank']        = $rank;
        $lData['createtime']  = $GLOBALS['db']->Date();
        $lData['updatetime']  = $GLOBALS['db']->Date();

        $linksTable = Jaws_ORM::getInstance()->table('linkdump_links');
        $lid = $linksTable->insert($lData)->exec();

        if (Jaws_Error::IsError($lid)) {
            $GLOBALS['app']->Session->PushLastResponse($lid->GetMessage(), RESPONSE_ERROR);
            return new Jaws_Error(_t('LINKDUMP_LINKS_ADD_ERROR', 'AddLink'), _t('LINKDUMP_NAME'));
        }

        $this->MoveLink($lid, $gid, $gid, $rank, null);

        $tags = preg_replace('#/|\\\#', '-', $tags);
        $tags = preg_replace('#&|"|\'|<|>#', '', $tags);

        $tags = array_filter(explode(',', $tags));
        $tags = array_map( array('Jaws_UTF8', 'trim') , $tags);

        foreach ($tags as $tag) {
            $res = $this->AddTagToLink($lid, $tag);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_ADD_TAG_ERROR'), RESPONSE_ERROR);
                break;
            }
        }

        $this->PopulateFeed($gid);
        $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_ADDED'), RESPONSE_NOTICE, $lid);
        return true;
    }

    /**
     * Update link's information
     *
     * @access  public
     * @param   int     $id             The id of link
     * @param   int     $gid            group ID
     * @param   string  $title          Title of the link
     * @param   string  $url            Url address
     * @param   string  $fast_url       
     * @param   string  $desc           Link's description
     * @param   string  $tags
     * @param   string  $rank
     * @return  mixed   True on success and Jaws_Error in otherwise
     */
    function UpdateLink($id, $gid, $title, $url, $fast_url, $desc, $tags, $rank)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'linkdump_links', false);

        $oldLink = $this->GetLink($id);
        if (Jaws_Error::IsError($oldLink)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_UPDATE_ERROR'), RESPONSE_ERROR);
            return new Jaws_Error($oldLink->getMessage(), 'SQL');
        }

        $lData['gid']         = (int)$gid;
        $lData['title']       = $title;
        $lData['description'] = $desc;
        $lData['url']         = $url;
        $lData['fast_url']    = $fast_url;
        $lData['updatetime']  = $GLOBALS['db']->Date();
        $lData['rank']        = $rank;

        $linksTable = Jaws_ORM::getInstance()->table('linkdump_links');
        $res = $linksTable->update($lData)->where('id', $id)->exec();

        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_UPDATE_ERROR'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        $this->MoveLink($id, $gid, $oldLink['gid'], $rank, $oldLink['rank']);

        $tags = preg_replace('#/|\\\#', '-', $tags);
        $tags = preg_replace('#&|"|\'|<|>#', '', $tags);

        $tags = array_filter(explode(',', $tags));
        $tags = array_map( array('Jaws_UTF8', 'trim') , $tags);

        $to_be_added_tags = array_diff($tags, $oldLink['tags']);
        foreach ($to_be_added_tags as $newtag) {
            $res = $this->AddTagToLink($id, $newtag);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_ADD_TAG_ERROR'), RESPONSE_ERROR);
                break;
            }
        }

        $to_be_removed_tags = array_diff($oldLink['tags'], $tags);
        foreach ($to_be_removed_tags as $oldtag) {
            $res = $this->RemoveTagFromLink($id, $oldtag);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_DELETE_TAG_ERROR'), RESPONSE_ERROR);
                break;
            }
        }

        if ($oldLink['gid'] != $gid) {
            $this->PopulateFeed($oldLink['gid']);
        }
        $this->PopulateFeed($gid);

        $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * function for change gid, pid and rank of menus
     *
     * @access  public
     * @param   int     $lid        link ID
     * @param   int     $new_gid    new group ID
     * @param   int     $old_gid    old group ID
     * @param   int     $new_rank   new rank
     * @param   int     $old_rank   old rank
     * @return  bool    True on success and False on failure
     */
    function MoveLink($lid, $new_gid, $old_gid, $new_rank, $old_rank)
    {
        if ($new_gid != $old_gid) {
            // resort menu items in old_pid
            $linksTable = Jaws_ORM::getInstance()->table('linkdump_links');
            $res = $linksTable->update(
                array(
                    'rank' => $linksTable->expr('rank - ?', 1)
                )
            )->where('gid', $old_gid)->and()->where('rank', $old_rank, '>')->exec();

            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        }

        if ($new_gid != $old_gid) {
            // resort menu items in new_pid
            $linksTable = Jaws_ORM::getInstance()->table('linkdump_links');
            $res = $linksTable->update(
                array(
                    'rank' => $linksTable->expr('rank + ?', 1)
                )
            )->where('id', $lid, '<>')->and()->where('gid', $new_gid)->and()->where('rank', $new_rank, '>=')->exec();

            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        } elseif (empty($old_rank)) {
            $linksTable = Jaws_ORM::getInstance()->table('linkdump_links');
            $res = $linksTable->update(
                array(
                    'rank' => $linksTable->expr('rank + ?', 1)
                )
            )->where('id', $lid, '<>')->and()->where('gid', $new_gid)->and()->where('rank', $new_rank, '>=')->exec();


            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        } elseif ($new_rank > $old_rank) {
            // resort menu items in new_pid
            $linksTable = Jaws_ORM::getInstance()->table('linkdump_links');
            $linksTable->update(
                array(
                    'rank' => $linksTable->expr('rank - ?', 1)
                )
            )->where('id', $lid, '<>')->and()->where('gid', $new_gid)->and()->where('rank', $old_rank, '>');
            $res = $linksTable->and()->where('rank', $new_rank, '<=')->exec();

            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        } elseif ($new_rank < $old_rank) {
            $linksTable = Jaws_ORM::getInstance()->table('linkdump_links');
            $linksTable->update(
                array(
                    'rank' => $linksTable->expr('rank + ?', 1)
                )
            )->where('id', $lid, '<>')->and()->where('gid', $new_gid)->and()->where('rank', $new_rank, '>=');
            $res = $linksTable->and()->where('rank', $old_rank, '<')->exec();

            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        }

        return true;
    }

    /**
     * Adds Tag to Link
     * 
     * @access  public
     * @param   int     $lid    link ID
     * @param   string  $tag
     * @return  mixed   True on Success and Jaws_Error on Failure
     */
    function AddTagToLink($lid, $tag)
    {
        $tagsTable = Jaws_ORM::getInstance()->table('linkdump_tags');
        $tagsTable->select('id')->where('tag', Jaws_UTF8::str_replace(' ', '_', Jaws_UTF8::strtolower($tag)));
        $tid = $tagsTable->getOne();
        if (Jaws_Error::IsError($tid)) {
            return new Jaws_Error($tid->getMessage(), 'SQL');
        }

        if (empty($tid)) {
            $tagsTable = Jaws_ORM::getInstance()->table('linkdump_tags');
            $tid = $tagsTable->insert(array('tag'=>$tag))->exec();
            if (Jaws_Error::IsError($tid)) {
                return new Jaws_Error($tid->getMessage(), 'SQL');
            }
        }

        $tData['link_id'] = (int)$lid;
        $tData['tag_id']  = (int)$tid;

        $ltagsTable = Jaws_ORM::getInstance()->table('linkdump_links_tags');
        $res = $ltagsTable->insert($tData)->exec();

        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return true;
    }

    /**
    * Removes Tag From Link
    *
    * @access  public
    * @param    int     $id     Tag ID
    * @param    string  $tag
    * @return   mixed   True on Success and Jaws_Error on Failure
    */
    function RemoveTagFromLink($id, $tag)
    {
        $tag = Jaws_UTF8::str_replace(' ', '_', Jaws_UTF8::strtolower($tag));
        $tagsTable = Jaws_ORM::getInstance()->table('linkdump_tags');
        $tid = $tagsTable->select('id')->where('tag', trim($tag))->getOne();

        if (Jaws_Error::IsError($tid)) {
            return new Jaws_Error($tid->getMessage(), 'SQL');
        }

        if (!empty($tid)) {
            $ltagsTable = Jaws_ORM::getInstance()->table('linkdump_links_tags');
            $res = $ltagsTable->delete()->where('link_id', (int)$id)->and()->where('tag_id', $tid)->exec();
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage(), 'SQL');
            }
        }

        return true;
    }

    /**
     * Delete link
     *
     * @access  public
     * @param   int     $lid    Link's id
     * @param   string  $gid    Group ID
     * @param   int     $rank   
     * @return  mixed   True on success on Jaws_Error otherwise
     */
    function DeleteLink($lid, $gid = '', $rank = 0)
    {
        $linksTable = Jaws_ORM::getInstance()->table('linkdump_links');
        $res = $linksTable->delete()->where('id', $lid)->exec();

        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_DELETE_ERROR'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        $this->MoveLink($lid, $gid, $gid, 0xfff, $rank);

        $ltagsTable = Jaws_ORM::getInstance()->table('linkdump_links_tags');
        $res = $ltagsTable->delete()->where('link_id', $lid)->exec();

        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_DELETE_ERROR'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        $this->PopulateFeed($gid);
        $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Will Poupulate the linkdump feed
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  bool    True on Success and False on Failure
     */
    function PopulateFeed($gid)
    {
        ///FIXME maybe it would be good to put some error checking here and return some messages if there's an error
        $group = $this->GetGroup($gid);
        if (Jaws_Error::IsError($group) || empty($group) || !isset($group['id'])) {
            return false;
        }

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $feedname = empty($group['fast_url']) ?
                    $GLOBALS['app']->UTF8->str_replace(' ', '-', $group['title']) : $xss->filter($group['fast_url']);
        $feedname = preg_replace('/[@?^=%&:;\/~\+# ]/i', '\1', $feedname);

        $html = $GLOBALS['app']->LoadGadget('LinkDump', 'HTML');
        @file_put_contents(JAWS_DATA. "xml/linkdump.$feedname.rdf", $html->PopulateFeed($gid, $group['limit_count']));
        Jaws_Utils::chmod(JAWS_DATA. "xml/linkdump.$feedname.rdf");

        return true;
    }

    /**
    * Insert a group
    * 
    * @access  public
    * @param    string  $title      group title
    * @param    string  $fast_url
    * @param    int     $limit_count
    * @param    string  $link_type
    * @param    string  $order_type
    * @return   bool    True Success and False on Failure
    */
    function InsertGroup($title, $fast_url, $limit_count, $link_type, $order_type)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'linkdump_groups');

        $gData['title']       = $title;
        $gData['fast_url']    = $fast_url;
        $gData['limit_count'] = $limit_count;
        $gData['link_type']   = $link_type;
        $gData['order_type']  = $order_type;

        $groupsTable = Jaws_ORM::getInstance()->table('linkdump_groups');
        $res = $groupsTable->insert($gData)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }
        $gid = $GLOBALS['db']->lastInsertID('linkdump_groups', 'id');
        $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_GROUPS_ADDED'), RESPONSE_NOTICE, $gid);

        return true;
    }

    /**
    * Update a group
    * 
    * @access  public
    * @param    int     $gid        group ID
    * @param    string  $title      group title
    * @param    string  $fast_url
    * @param    int     $limit_count
    * @param    string  $link_type
    * @param    string  $order_type
    * @return   bool    True on Success and False on Failure
    */
    function UpdateGroup($gid, $title, $fast_url, $limit_count, $link_type, $order_type)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'linkdump_groups', false);

        $gData['title']       = $title;
        $gData['fast_url']    = $fast_url;
        $gData['limit_count'] = $limit_count;
        $gData['link_type']   = $link_type;
        $gData['order_type']  = $order_type;

        $groupsTable = Jaws_ORM::getInstance()->table('linkdump_groups');
        $res = $groupsTable->update($gData)->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $this->PopulateFeed($gid);
        $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_GROUPS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Delete a group
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  bool    True if query was successful and false on error
     */
    function DeleteGroup($gid)
    {
        if ($gid == 1) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_GROUPS_NOT_DELETABLE'), RESPONSE_ERROR);
            return false;
        }
        $group = $this->GetGroup($gid);
        if (Jaws_Error::IsError($group)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        if(!isset($group['id'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_GROUPS_NOT_EXISTS'), RESPONSE_ERROR);
            return false;
        }

        $links = $this->GetGroupLinks($gid);
        foreach ($links as $link) {
            $ltagsTable = Jaws_ORM::getInstance()->table('linkdump_links_tags');
            $res = $ltagsTable->delete()->where('link_id', $link['id'])->exec();
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        }

        $linksTable = Jaws_ORM::getInstance()->table('linkdump_links');
        $res = $linksTable->delete()->where('gid', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $groupsTable = Jaws_ORM::getInstance()->table('linkdump_groups');
        $res = $groupsTable->delete()->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_GROUPS_DELETED', $gid), RESPONSE_NOTICE);

        return true;
    }

}