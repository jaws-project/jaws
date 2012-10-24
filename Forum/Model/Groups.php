<?php
/**
 * Forum Gadget
 *
 * @category   GadgetModel
 * @package    Forum
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forum_Model_Groups extends Jaws_Gadget_Model
{
    /**
     * Returns array of group properties
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  mixed   Array of group properties or Jaws_Error on error
     */
    function GetGroup($gid)
    {
        $sql = '
            SELECT
                [id], [title], [description], [fast_url], [order], [locked], [published]
            FROM [[forums_groups]]
            WHERE [id] = {gid}';

        $params = array();
        $params['gid'] = $gid;

        $types = array('integer', 'text', 'text', 'text', 'integer', 'boolean', 'boolean');
        $result = $GLOBALS['db']->queryRow($sql, $params, $types);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('FORUM_ERROR_GET_FORUMS'), _t('FORUM_NAME'));
        }

        return $result;
    }

    /**
     * Returns array of groups and properties
     *
     * @access  public
     * @param   bool    $onlyPublished
     * @return  mixed   Array of groups and properties or Jaws_Error on error
     */
    function GetGroups($onlyPublished = false)
    {
        $sql = '
            SELECT
                [id], [title], [description], [fast_url], [order], [locked], [published]
            FROM [[forums_groups]]';
            if ($onlyPublished) {
                $sql .= ' WHERE [published] = {published}';
            }
            $sql .= ' ORDER BY [order] DESC';

        $params = array();
        $params['published'] = true;

        $types = array('integer', 'text', 'text', 'text', 'integer', 'boolean', 'boolean');
        $result = $GLOBALS['db']->queryAll($sql, $params, $types);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('FORUM_ERROR_GET_FORUMS'), _t('FORUM_NAME'));
        }

        return $result;
    }

}