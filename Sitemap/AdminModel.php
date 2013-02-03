<?php
require_once JAWS_PATH . 'gadgets/Sitemap/Model.php';
/**
 * Sitemap Gadget
 *
 * @category   GadgetModel
 * @package    Sitemap
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_AdminModel extends Sitemap_Model
{
    /**
     * Gets max position for a given parent...
     *
     * @access  private
     * @param   int     $parent_id  ID of the parent
     * @return  int     Max position.
     */
    function GetMaxPosition($parent_id)
    {
        $sql = 'SELECT MAX([rank]) FROM [[sitemap]] WHERE [parent_id] = {parent_id}';
        $mp = $GLOBALS['db']->queryOne($sql, array('parent_id' => $parent_id));
        return Jaws_Error::IsError($mp) ? 1 : $mp + 1;
    }

    /**
     * Creates a new menu item
     *
     * @access  public
     * @param   int     $parent_id  ID of the parent item
     * @param   string  $title      Item title
     * @param   string  $shortname  Item shortname (this is used as part of the link)
     * @param   string  $type       Item type (staticpage, blog, url, etc)
     * @param   string  $reference  Item type reference
     * @param   string  $change     (Optional) Change frequency. Values can be always, hourly, daily, weekly,
     *                              monthly, yearly, never
     * @param   string  $priority   (Optional) Priority of this item relative to other items on the site. Can be 
     *                              values from 1 to 5 (only numbers!).
     * @return  bool    True if the sitemap was added without errors, otherwise returns false
     */
    function NewItem($parent_id, $title, $shortname, $type, $reference, $change = '', $priority = '')
    {
        if (empty($title) || empty($shortname)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_NEW_ITEM'), RESPONSE_ERROR);
            return new Jaws_Error(_t('SITEMAP_ERROR_NEW_ITEM'), _t('SITEMAP_NAME'));
        }
        
        if (!empty($priority) && is_numeric($priority)) {
            if ($priority < 0 && $priority > 1) {
                $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_PRIORITY_FORMAT'), RESPONSE_ERROR);
                return new Jaws_Error(_t('SITEMAP_ERROR_PRIORITY_FORMAT'), _t('SITEMAP_NAME'));
            }            
        }

        if (!empty($change) && !in_array($change, array('hourly', 'daily', 'weekly', 'monthly', 
                                                        'yearly', 'never'))) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_CHANGE_FREQ_FORMAT'), RESPONSE_ERROR);
            return new Jaws_Error(_t('SITEMAP_ERROR_CHANGE_FREQ_FORMAT'), _t('SITEMAP_NAME'));
        }

        $position = $this->GetMaxPosition($parent_id);
        $params                 = array();
        $params['now']          = $GLOBALS['db']->Date();
        $params['parent_id']    = $parent_id;
        $params['title']        = $title;
        $params['shortname']    = $shortname;
        $params['type']         = $type;
        $params['reference']    = $reference;
        $params['position']     = $position;
        $params['priority']     = $priority;
        $params['changefreq']   = $change;
        
        if ($parent_id == 0) {
            $params['path'] = $shortname;
        } else {
            $pitem = $this->GetItem($parent_id);
            $params['path'] = $pitem['path'] . '/' . $shortname;
        }

        $sql = '
            INSERT INTO [[sitemap]]
                ([parent_id], [title], [shortname], [rfc_type], [reference], [rank],
                 [path], [changefreq], [priority], [createtime], [updatetime])
            VALUES
                ({parent_id}, {title}, {shortname}, {type}, {reference}, {position},
                 {path}, {changefreq}, {priority}, {now}, {now})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_NEW_ITEM'), RESPONSE_ERROR);
            return new Jaws_Error(_t('SITEMAP_ERROR_NEW_ITEM'), _t('SITEMAP_NAME'));
        }

        $sql = 'SELECT
                [id], [parent_id], [title], [shortname], [rfc_type], [changefreq],
                [priority], [reference], [rank], [createtime], [updatetime]
                FROM [[sitemap]]
                WHERE [createtime] = {now}';

        $result = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_NEW_ITEM'), RESPONSE_ERROR);
            return new Jaws_Error(_t('SITEMAP_ERROR_NEW_ITEM'), _t('SITEMAP_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_CREATED'), RESPONSE_NOTICE);
        return $result;

    }

    /**
     * Deletes the item
     *
     * @access  public
     * @param   int     $id Item ID
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function DeleteItem($id)
    {
        $item = $this->GetItem($id);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_DELETE'), RESPONSE_ERROR);
            return new Jaws_Error(_t('SITEMAP_ERROR_DELETE'), _t('SITEMAP_NAME'));
        }

        // Delete item and children
        $path = $item['path'] . '%';
        $sql = 'DELETE FROM [[sitemap]] WHERE [path] LIKE {path}';
        $result = $GLOBALS['db']->query($sql, array('path' => $path));
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_DELETE'), RESPONSE_ERROR);
            return new Jaws_Error(_t('SITEMAP_ERROR_DELETE'), _t('SITEMAP_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_DELETED'), RESPONSE_NOTICE);
        return true;
    }


    /**
     * Updates the item
     *
     * @access  public
     * @param   int     $id         ID of the Sitemap
     * @param   int     $parent_id  Parent ID
     * @param   string  $title      Item title
     * @param   string  $shortname  Item shortname (used as link)
     * @param   string  $type       Item type ('static_page', 'blog', 'url', etc)
     * @param   string  $reference  Type reference
     * @param   string  $change     (Optional) Change frequency. Values can be always, hourly, daily, weekly,
     *                              monthly, yearly, never
     * @param   string  $priority   (Optional) Priority of this item relative to other item on the site. Can be 
     *                              values from 1 to 5 (only numbers!).
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function UpdateItem($id, $parent_id, $title, $shortname, $type, $reference, $change = '', $priority = '')
    {
        $item = $this->GetItem($id);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_UPDATE'), RESPONSE_ERROR);
            return new Jaws_Error(_t('SITEMAP_ERROR_UPDATE'), _t('SITEMAP_NAME'));
        }

        
        if (!empty($priority) && is_numeric($priority)) {
            if ($priority < 0 && $priority > 1) {
                $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_PRIORITY_FORMAT'), RESPONSE_ERROR);
                return new Jaws_Error(_t('SITEMAP_ERROR_PRIORITY_FORMAT'), _t('SITEMAP_NAME'));
            }            
        }

        if (!empty($change) && !in_array($change, array('hourly', 'daily', 'weekly', 'monthly', 
                                                        'yearly', 'never'))) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_CHANGE_FREQ_FORMAT'), RESPONSE_ERROR);
            return new Jaws_Error(_t('SITEMAP_ERROR_CHANGE_FREQ_FORMAT'), _t('SITEMAP_NAME'));
        }

        $params = array();
        $params['now']        = $GLOBALS['db']->Date();
        $params['id']         = (int)$id;
        $params['title']      = $title;
        $params['shortname']  = $shortname;
        $params['type']       = $type;
        $params['reference']  = $reference;
        $params['parent_id']  = $parent_id;
        $params['priority']   = $priority;
        $params['changefreq'] = $change;
        
        if ($parent_id != $item['parent_id']) {
            $params['position'] = $this->GetMaxPosition($parent_id);
        } else {
            $params['position'] = $item['rank'];
        }


        if ($parent_id == 0) {
            $params['path'] = $shortname;
        } else {
            $pitem = $this->GetItem($parent_id);
            $params['path'] = $pitem['path'] . '/' . $shortname;
        }


        $sql = '
            UPDATE [[sitemap]] SET
                [title]      = {title},
                [shortname]  = {shortname},
                [rfc_type]   = {type},
                [reference]  = {reference},
                [parent_id]  = {parent_id},
                [rank]       = {position},
                [path]       = {path},
                [priority]   = {priority},
                [changefreq] = {changefreq},
                [updatetime] = {now}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_UPDATE'), RESPONSE_ERROR);
            return new Jaws_Error(_t('SITEMAP_ERROR_UPDATE'), _t('SITEMAP_NAME'));
        }

        // If shortname has been changed we need to update all its children paths...
        if ($item['path'] != $params['path']) {
            $GLOBALS['db']->dbc->loadModule('Function', null, true);
            $replace_path = $GLOBALS['db']->dbc->function->replace('[[sitemap]].[path]', "'".$item['path']."'", "'".$params['path']."'");

            $sql = "
                UPDATE [[sitemap]] SET
                    [path] = $replace_path
                WHERE [path] LIKE {likepath}";
            $cparams = array();
            $cparams['likepath'] = $item['path'] . '/%';

            $result = $GLOBALS['db']->query($sql, $cparams);
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_UPDATE'), RESPONSE_ERROR);
                return new Jaws_Error(_t('SITEMAP_ERROR_UPDATE'), _t('SITEMAP_NAME'));
            }
        }
        
        $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Moves sitemap item to some direction
     *
     * @access  public
     * @param   int     $id         Item id
     * @param   string  $direction  Where to move it (up or down)
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function MoveItem($id, $direction)
    {
        $item = $this->GetItem($id);
        $sql = '
            SELECT
                [id], [rank]
            FROM [[sitemap]]
            WHERE [parent_id] = {parent}
            ORDER BY [rank] ASC';

        $result = $GLOBALS['db']->queryAll($sql, array('parent' => $item['parent_id']));
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_MOVE_ITEM'), RESPONSE_ERROR);
            return new Jaws_Error(_t('SITEMAP_ERROR_MOVE_ITEM'), _t('SITEMAP_NAME'));
        }

        $items = array ();
        foreach ($result as $row) {
            $res['id'] = $row['id'];
            $res['position'] = $row['rank'];
            $items[$row['id']] = $res;
        }
        reset($items);

        $found = false;
        while (!$found) {
            $v = current ($items);
            if ($v['id'] == $id) {
                $found = true;
                $position = $v['position'];
                $id = $v['id'];
            } else {
                next ($items);
            }
        }
        $run_queries = false;

        if ($direction == 'up' && prev($items)) {
            $v = current($items);
            $m_position = $v['position'];
            $m_id = $v['id'];
            $run_queries = true;
        }

        if ($direction == 'down' && next($items)) {
            $v = current($items);
            $m_position = $v['position'];
            $m_id = $v['id'];
            $run_queries = true;
        }

        if ($run_queries) {
            $now = $GLOBALS['db']->Date();

            $params = array();
            $params['now']      = $now;
            $params['id']       = $id;
            $params['position'] = $m_position;

            $sql = '
                UPDATE [[sitemap]] SET
                    [rank] = {position},
                    [updatetime] = {now}
                WHERE [id] = {id}';

            $result = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_MOVE_ITEM'), RESPONSE_ERROR);
                return new Jaws_Error(_t('SITEMAP_ERROR_MOVE_ITEM'), _t('SITEMAP_NAME'));
            }

            $params = array();
            $params['now']      = $now;
            $params['id']       = $m_id;
            $params['position'] = $position;

            $sql = '
                UPDATE [[sitemap]] SET
                    [rank] = {position},
                    [updatetime] = {now}
                WHERE [id] = {id}';

            $result = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ERROR_MOVE_ITEM'), RESPONSE_ERROR);
                return new Jaws_Error(_t('SITEMAP_ERROR_MOVE_ITEM'), _t('SITEMAP_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('SITEMAP_ITEM_MOVED'), RESPONSE_NOTICE);
        return true;
    }
}
