<?php
/**
 * LinkDump Gadget Admin
 *
 * @category   GadgetModel
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'gadgets/LinkDump/Model.php';
class LinkDumpAdminModel extends LinkDumpModel
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  boolean Returns true on a successfull Install and Jaws_Error on errors
     */
    function InstallGadget()
    {
        $new_dir = JAWS_DATA . 'xml' . DIRECTORY_SEPARATOR;
        if (!Jaws_Utils::mkdir($new_dir)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_CREATING_DIR', $new_dir), _t('LINKDUMP_NAME'));
        }

        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $result = $this->installSchema('insert.xml', '', 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry key
        $GLOBALS['app']->Registry->NewKey('/gadgets/LinkDump/max_limit_count', '100');
        $GLOBALS['app']->Registry->NewKey('/gadgets/LinkDump/links_target', 'blank');

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  boolean  Success/Failure (Jaws_Error)
     */
    function UninstallGadget()
    {
        $tables = array('linkdump_links',
                        'linkdump_groups',
                        'linkdump_tags',
                        'linkdump_links_tags');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('LINKDUMP_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        // registry keys
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/LinkDump/max_limit_count');
        $GLOBALS['app']->Registry->DeleteKey('/gadgets/LinkDump/links_target');

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  boolean  Success/Failure (Jaws_Error)
     */
    function UpdateGadget($old, $new)
    {
        $result = $this->installSchema('schema.xml', '', "$old.xml");
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (version_compare($old, '0.4.0', '<')) {
            $result = $this->installSchema('insert.xml', '', 'schema.xml', true);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            // ACL keys
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/LinkDump/ManageLinks', 'true');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/LinkDump/ManageGroups', 'true');
            $GLOBALS['app']->ACL->NewKey('/ACL/gadgets/LinkDump/ManageTags',   'true');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/LinkDump/AddLink');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/LinkDump/UpdateLink');
            $GLOBALS['app']->ACL->DeleteKey('/ACL/gadgets/LinkDump/DeleteLink');

            // Registry keys.
            $GLOBALS['app']->Registry->NewKey('/gadgets/LinkDump/max_limit_count', '100');
            $GLOBALS['app']->Registry->NewKey('/gadgets/LinkDump/links_target', 'blank');
            $GLOBALS['app']->Registry->DeleteKey('/gadgets/LinkDump/limitation');
            $GLOBALS['app']->Registry->DeleteKey('/gadgets/LinkDump/target');
        }

        return true;
    }

    /**
    * Insert a link
    * @access  public
    *
    * @return  boolean Success/Failure (Jaws_Error)
    */
    function InsertLink($gid, $title, $url, $fast_url, $desc, $tags, $rank)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'linkdump_links');

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');

        $params = array();
        $params['title']       = $xss->parse($title);
        $params['description'] = $xss->parse($desc);
        $params['url']         = $url;
        $params['fast_url']    = $xss->parse($fast_url);
        $params['gid']         = $gid;
        $params['rank']        = $rank;
        $params['now']         = $GLOBALS['db']->Date();

        $sql = '
            INSERT INTO [[linkdump_links]]
                ([gid], [title], [description], [url], [fast_url], [rank], [createtime], [updatetime])
            VALUES
                ({gid}, {title}, {description}, {url}, {fast_url}, {rank}, {now}, {now})';

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse($res->GetMessage(), RESPONSE_ERROR);
            return new Jaws_Error(_t('LINKDUMP_LINKS_ADD_ERROR', 'AddLink'), _t('LINKDUMP_NAME'));
        }

        $lid = $GLOBALS['db']->lastInsertID('linkdump_links', 'id');
        if (Jaws_Error::IsError($lid)) {
            $GLOBALS['app']->Session->PushLastResponse($lid->GetMessage(), RESPONSE_ERROR);
            return new Jaws_Error(_t('LINKDUMP_ERROR_LINK_NOT_ADDED', 'AddLink'), _t('LINKDUMP_NAME'));
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
     * @param   string  $title          Title of the link
     * @param   string  $description    Link's description
     * @param   string  $url            Link's URL
     * @return  boolean True on success and Jaws_Error in otherwise
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

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');

        $params = array();
        $params['id']          = (int)$id;
        $params['gid']         = (int)$gid;
        $params['title']       = $xss->parse($title);
        $params['description'] = $xss->parse($desc);
        $params['url']         = $url;
        $params['fast_url']    = $xss->parse($fast_url);
        $params['now']         = $GLOBALS['db']->Date();
        $params['rank']        = $rank;

        $sql = '
            UPDATE [[linkdump_links]] SET
                [title]       = {title},
                [gid]         = {gid},
                [description] = {description},
                [url]         = {url},
                [fast_url]    = {fast_url},
                [updatetime]  = {now},
                [rank]        = {rank}
            WHERE [id] = {id}';

        $res = $GLOBALS['db']->query($sql, $params);
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
     * @return  array   Response (notice or error)
     */
    function MoveLink($lid, $new_gid, $old_gid, $new_rank, $old_rank)
    {
        if ($new_gid != $old_gid) {
            // resort menu items in old_pid
            $sql = '
                UPDATE [[linkdump_links]] SET
                    [rank] = [rank] - 1
                WHERE
                    [gid] = {gid}
                  AND
                    [rank] > {rank}';

            $params         = array();
            $params['gid']  = $old_gid;
            $params['rank'] = $old_rank;
            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        }

        if ($new_gid != $old_gid) {
            // resort menu items in new_pid
            $sql = '
                UPDATE [[linkdump_links]] SET
                    [rank] = [rank] + 1
                WHERE
                    [id] <> {lid}
                  AND
                    [gid] = {gid}
                  AND
                    [rank] >= {new_rank}';

            $params             = array();
            $params['lid']      = $lid;
            $params['gid']      = $new_gid;
            $params['new_rank'] = $new_rank;
            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        } elseif (empty($old_rank)) {
            $sql = '
                UPDATE [[linkdump_links]] SET
                    [rank] = [rank] + 1
                WHERE
                    [id] <> {lid}
                  AND
                    [gid] = {gid}
                  AND
                    [rank] >= {new_rank}';

            $params             = array();
            $params['lid']      = $lid;
            $params['gid']      = $new_gid;
            $params['new_rank'] = $new_rank;
            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        } elseif ($new_rank > $old_rank) {
            // resort menu items in new_pid
            $sql = '
                UPDATE [[linkdump_links]] SET
                    [rank] = [rank] - 1
                WHERE
                    [id] <> {lid}
                  AND
                    [gid] = {gid}
                  AND
                    [rank] > {old_rank}
                  AND
                    [rank] <= {new_rank}';

            $params             = array();
            $params['lid']      = $lid;
            $params['gid']      = $new_gid;
            $params['old_rank'] = $old_rank;
            $params['new_rank'] = $new_rank;
            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        } elseif ($new_rank < $old_rank) {
            // resort menu items in new_pid
            $sql = '
                UPDATE [[linkdump_links]] SET
                    [rank] = [rank] + 1
                WHERE
                    [id] <> {lid}
                  AND
                    [gid] = {gid}
                  AND
                    [rank] >= {new_rank}
                  AND
                    [rank] < {old_rank}';

            $params             = array();
            $params['lid']      = $lid;
            $params['gid']      = $new_gid;
            $params['old_rank'] = $old_rank;
            $params['new_rank'] = $new_rank;
            $res = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        }

        return true;
    }

    /**
     * This is the short Description for the Function
     *
     * This is the long description for the Class
     *
     * @return	mixed	 Description
     * @access	public
     */
    function AddTagToLink($lid, $tag)
    {
        $tag = Jaws_UTF8::str_replace(' ', '_', Jaws_UTF8::strtolower($tag));
        $sql = 'SELECT [id] FROM [[linkdump_tags]] WHERE [tag] = {tag}';
        $tid = $GLOBALS['db']->queryOne($sql, array('tag' => $tag));
        if (Jaws_Error::IsError($tid)) {
            return new Jaws_Error($tid->getMessage(), 'SQL');
        }

        if (empty($tid)) {
            $sql = 'INSERT INTO [[linkdump_tags]] ([tag]) VALUES({tag})';
            $res = $GLOBALS['db']->query($sql, array('tag' => $tag));
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage(), 'SQL');
            }

            $tid = $GLOBALS['db']->lastInsertID('linkdump_tags', 'id');
            if (Jaws_Error::IsError($tid)) {
                return new Jaws_Error($tid->GetMessage(), 'SQL');
            }
        }

        $params = array();
        $params['link_id'] = (int)$lid;
        $params['tag_id']  = (int)$tid;

        $sql = 'INSERT INTO [[linkdump_links_tags]] ([tag_id], [link_id]) VALUES({tag_id}, {link_id})';
        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        return true;
    }

    /**
    * This is the short Description for the Function
    *
    * @return mixed	 Description
    * @access public
    */
    function RemoveTagFromLink($id, $tag)
    {
        $tag = Jaws_UTF8::str_replace(' ', '_', Jaws_UTF8::strtolower($tag));
        $sql = 'SELECT [id] FROM [[linkdump_tags]] WHERE [tag] = {tag}';
        $tid = $GLOBALS['db']->queryOne($sql, array('tag' => trim($tag)));
        if (Jaws_Error::IsError($tid)) {
            return new Jaws_Error($tid->getMessage(), 'SQL');
        }
        if (!empty($tid)) {
            $params = array();
            $params['link_id'] = (int)$id;
            $params['tag_id']  = $tid;

            $sql = 'DELETE FROM [[linkdump_links_tags]] WHERE [link_id] = {link_id} AND [tag_id] = {tag_id}';
            $res = $GLOBALS['db']->query($sql, $params);
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
     * @param   int $lid Link's id
     * @return  Boolean True on success on Jaws_Error otherwise
     */
    function DeleteLink($lid, $gid = '', $rank = 0)
    {
        $params = array();
        $params['lid'] = $lid;

        $sql = 'DELETE FROM [[linkdump_links]] WHERE [id] = {lid}';
        $res  = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_LINKS_DELETE_ERROR'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        $this->MoveLink($lid, $gid, $gid, 0xfff, $rank);

        $sql = 'DELETE FROM [[linkdump_links_tags]] WHERE [link_id] = {lid}';
        $res = $GLOBALS['db']->query($sql, $params);
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
    * @access  public
    *
    * @return  boolean Success/Failure (Jaws_Error)
    */
    function InsertGroup($title, $fast_url, $limit_count, $link_type, $order_type)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'linkdump_groups');

        $sql = '
            INSERT INTO [[linkdump_groups]]
                ([title], [fast_url], [limit_count], [link_type], [order_type])
            VALUES
                ({title}, {fast_url}, {limit_count}, {link_type}, {order_type})';

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params = array();
        $params['title']       = $xss->parse($title);
        $params['fast_url']    = $xss->parse($fast_url);
        $params['limit_count'] = $limit_count;
        $params['link_type']   = $link_type;
        $params['order_type']  = $order_type;
        $res = $GLOBALS['db']->query($sql, $params);
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
    * @access  public
    *
    * @return  boolean Success/Failure (Jaws_Error)
    */
    function UpdateGroup($gid, $title, $fast_url, $limit_count, $link_type, $order_type)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'linkdump_groups', false);

        $sql = '
            UPDATE [[linkdump_groups]] SET
                [title]       = {title},
                [fast_url]    = {fast_url},
                [limit_count] = {limit_count},
                [link_type]   = {link_type},
                [order_type]  = {order_type}
            WHERE [id] = {gid}';

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $params                = array();
        $params['gid']         = $gid;
        $params['title']       = $xss->parse($title);
        $params['fast_url']    = $xss->parse($fast_url);
        $params['limit_count'] = $limit_count;
        $params['link_type']   = $link_type;
        $params['order_type']  = $order_type;
        $res = $GLOBALS['db']->query($sql, $params);
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
     * @return  boolean True if query was successful and Jaws_Error on error
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
            $sql = 'DELETE FROM [[linkdump_links_tags]] WHERE [link_id] = {id}';
            $res = $GLOBALS['db']->query($sql, array('id' => $link['id']));
            if (Jaws_Error::IsError($res)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
                return false;
            }
        }

        $sql = 'DELETE FROM [[linkdump_links]] WHERE [gid] = {gid}';
        $res = $GLOBALS['db']->query($sql, array('gid' => $gid));
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $sql = 'DELETE FROM [[linkdump_groups]] WHERE [id] = {gid}';
        $res = $GLOBALS['db']->query($sql, array('gid' => $gid));
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED'), RESPONSE_ERROR);
            return false;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('LINKDUMP_GROUPS_DELETED', $gid), RESPONSE_NOTICE);

        return true;
    }

}