<?php
/**
 * Menu Gadget
 *
 * @category   GadgetModel
 * @package    Menu
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class MenuModel extends Jaws_Model
{
    /**
     * Returns a menu
     *
     * @access  public
     * @param   int     $mid    menu ID
     * @return  mixed  Array with all the available menus and Jaws_Error on error
     */
    function GetMenu($mid)
    {
        $sql = '
            SELECT
                [id], [pid], [gid], [menu_type], [title], [url], [url_target], [rank], [visible], [image]
            FROM [[menus]]
            WHERE
                [id] = {mid}';

        $params = array();
        $params['mid'] = $mid;

        $types  = array('integer', 'integer', 'integer', 'text', 'text', 'text', 'integer', 'integer', 'integer', 'boolean');
        $result = $GLOBALS['db']->queryRow($sql, $params, $types);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('MENU_ERROR_GET_MENUS'), _t('MENU_NAME'));
        }

        return $result;
    }

    /**
     * Returns a list of  menus at a request level
     *
     * @access  public
     * @param   int     $pid
     * @param   int     $gid            group ID
     * @param   bool    $onlyVisible    show only visible
     * @return  mixed   Array with all the available menus and Jaws_Error on error
     */
    function GetLevelsMenus($pid, $gid = null, $onlyVisible = false)
    {
        $sql = '
            SELECT [id], [gid], [title], [url], [url_target], [image], [visible]
                FROM [[menus]]
                WHERE ';
        $sql.= (empty($gid)? '' : '[gid] = {gid} AND ') . '[pid] = {pid}'.
               ($onlyVisible?' AND [visible] = {visible} ':' ');
        $sql.= 'ORDER BY [rank] ASC';

        $params = array();
        $params['gid']     = $gid;
        $params['pid']     = $pid;
        $params['visible'] = 1;

        // using boolean type for blob to check it empty or not
        $types = array('integer', 'integer', 'text', 'text', 'integer', 'boolean', 'integer');
        $result = $GLOBALS['db']->queryAll($sql, $params, $types);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('MENU_ERROR_GET_MENUS'), _t('MENU_NAME'));
        }

        return $result;
    }

    /**
     * Returns the image of the menu
     *
     * @access  public
     * @param   int     $id
     * @return  blob    image or Jaws_Error on error
     */
    function GetMenuImage($id)
    {
        $sql = '
            SELECT [image]
            FROM [[menus]]
            WHERE [id] = {id}';

        $params = array();
        $params['id'] = (int)$id;
        $types  = array('blob');
        $blob = $GLOBALS['db']->queryOne($sql, $params, $types);
        if (Jaws_Error::IsError($blob)) {
            return new Jaws_Error($blob->getMessage(), 'SQL');
        }

        $result = '';
        while (!feof($blob)) {
            $result.= fread($blob, 8192);
        }
        return $result;
    }

    /**
     * Returns a list with all the menus
     *
     * @access  public
     * @param   int     $gid        group ID
     * @return  mixed  Array with all the available menus and Jaws_Error on error
     */
    function GetGroups($gid = null)
    {
        $sql = '
            SELECT
                [id], [title], [title_view], [view_type], [rank], [visible]
            FROM [[menus_groups]] ';
        $sql.= (empty($gid)? '' : 'WHERE [id] = {gid} ') . 'ORDER BY [rank] DESC';

        $params = array();
        $params['gid'] = $gid;

        if (!empty($gid)) {
            $result = $GLOBALS['db']->queryRow($sql, $params);
        } else {
            $result = $GLOBALS['db']->queryAll($sql, $params);
        }
        if (Jaws_Error::IsError($result)) {
            //add language word for this
            return new Jaws_Error(_t('MENU_ERROR_GET_GROUPS'), _t('MENU_NAME'));
        }

        return $result;
    }

}