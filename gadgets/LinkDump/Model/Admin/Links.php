<?php
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
class LinkDump_Model_Admin_Links extends Jaws_Gadget_Model
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
            return new Jaws_Error(_t('LINKDUMP_LINKS_ADD_ERROR', 'AddLink'));
        }

        $this->MoveLink($lid, $gid, $gid, $rank, null);

        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $model = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
            $res = $model->InsertReferenceTags('LinkDump', 'link', $lid, true, null, $tags);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_ADD_TAG_ERROR'), RESPONSE_ERROR);
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

        $model = $this->gadget->model->load('Links');
        $oldLink = $model->GetLink($id);
        if (Jaws_Error::IsError($oldLink)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_UPDATE_ERROR'), RESPONSE_ERROR);
            return new Jaws_Error($oldLink->getMessage());
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

        if (Jaws_Gadget::IsGadgetInstalled('Tags') && !empty($tags)) {
            $model = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
            $res = $model->UpdateReferenceTags('LinkDump', 'link', $id, true, time(), $tags);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_UPDATE_TAG_ERROR'), RESPONSE_ERROR);
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

        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $model = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
            $res = $model->DeleteReferenceTags('LinkDump', 'link', $lid);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_DELETE_ERROR'), RESPONSE_ERROR);
                return $res;
            }
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
            $model = $this->gadget->model->load('Groups');
            $group = $model->GetGroup($gid);
            $gid = $group['fast_url'];
        }

        $rss_path = JAWS_DATA . 'xml/link-' . $gid . '.rss';
        return @unlink($rss_path);
    }
}