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

        $lData = array();
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
        $tags = array_filter(array_map(array($GLOBALS['app']->UTF8, 'trim'), explode(',', $tags)));
        foreach ($tags as $tag) {
            $res = $this->AddTagToLink($lid, $tag);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_ADD_TAG_ERROR'), RESPONSE_ERROR);
                break;
            }
        }

        $this->InvalidateFeed($gid);
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

        $lData = array();
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
            return $res;
        }

        $this->MoveLink($id, $gid, $oldLink['gid'], $rank, $oldLink['rank']);

        $tags = array_filter(array_map(array($GLOBALS['app']->UTF8, 'trim'), explode(',', $tags)));
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
            $this->InvalidateFeed($oldLink['gid']);
        }
        $this->InvalidateFeed($gid);

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
        $linksTable = Jaws_ORM::getInstance()->table('linkdump_links');
        if ($new_gid != $old_gid) {
            // resort menu items in old_pid
            $res = $linksTable->update(
                array(
                    'rank' => $linksTable->expr('rank - ?', 1)
                )
            )->where('gid', $old_gid)->and()->where('rank', $old_rank, '>')->exec();
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }

            // resort menu items in new_pid
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
        $tag = preg_replace(
            array('#[^\p{L}[:digit:]_\.-\s]#u', '#[\s_-]#u', '#-+#u'),
            array('', '-', '-'),
            $GLOBALS['app']->UTF8->strtolower($tag)
        );

        $objORM = Jaws_ORM::getInstance()->table('linkdump_tags');
        $tid = $objORM->select('id')->where('tag', $tag)->fetchOne();
        if (Jaws_Error::IsError($tid)) {
            return $tid;
        }

        if (empty($tid)) {
            $tid = $objORM->table('linkdump_tags')->insert(array('tag'=>$tag))->exec();
            if (Jaws_Error::IsError($tid)) {
                return $tid;
            }
        }

        $tData = array();
        $tData['link_id'] = (int)$lid;
        $tData['tag_id']  = (int)$tid;

        $res = $objORM->table('linkdump_links_tags')->insert($tData)->exec();
        if (Jaws_Error::IsError($res)) {
            return $res;
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
        $tag = preg_replace(
            array('#[^\p{L}[:digit:]_\.-\s]#u', '#[\s_-]#u', '#-+#u'),
            array('', '-', '-'),
            $GLOBALS['app']->UTF8->strtolower($tag)
        );
        $objORM = Jaws_ORM::getInstance();
        $tid = $objORM->select('id')->table('linkdump_tags')->where('tag', $tag)->fetchOne();
        if (Jaws_Error::IsError($tid)) {
            return $tid;
        }

        if (!empty($tid)) {
            $res = $objORM->delete()->table('linkdump_links_tags')
                ->where('link_id', (int)$id)->and()->where('tag_id', $tid)
                ->exec();
            if (Jaws_Error::IsError($res)) {
                return $res;
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
        $objORM = Jaws_ORM::getInstance();
        $res = $objORM->delete()->table('linkdump_links')->where('id', $lid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_DELETE_ERROR'), RESPONSE_ERROR);
            return $res;
        }

        $this->MoveLink($lid, $gid, $gid, 0xfff, $rank);

        $res = $objORM->delete()->table('linkdump_links_tags')->where('link_id', $lid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_DELETE_ERROR'), RESPONSE_ERROR);
            return $res;
        }

        $this->InvalidateFeed($gid);
        $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Will Inactivate Feed
     *
     * @access  public
     * @param   int|string  $gid    group ID or group fast url
     * @return  bool        True on Success or False on Failure
     */
    function InvalidateFeed($gid)
    {
        if (is_numeric($gid)) {
            $group = $this->GetGroup($gid);
            $gid = $group['fast_url'];
        }

        $rss_path = JAWS_DATA . 'xml/link-' . $gid . '.rss';
        return @unlink($rss_path);
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
        $gid = $groupsTable->insert($gData)->exec();
        if (Jaws_Error::IsError($gid)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

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

        $this->InvalidateFeed($gid);
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

        $objORM = Jaws_ORM::getInstance()->table('linkdump_links_tags');
        $links = $this->GetGroupLinks($gid);
        foreach ($links as $link) {
            $res = $objORM->delete()->where('link_id', $link['id'])->exec();
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        }

        $res = $objORM->delete()->table('linkdump_links')->where('gid', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $res = $objORM->delete()->table('linkdump_groups')->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_GROUPS_DELETED', $gid), RESPONSE_NOTICE);
        return true;
    }

}