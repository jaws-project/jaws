<?php
/**
 * Forums Gadget
 *
 * @category   GadgetModel
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Forums_Model_Groups extends Jaws_Gadget_Model
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
        $params = array();
        $params['gid'] = $gid;

        $sql = '
            SELECT
                [id], [title], [description], [fast_url], [order], [locked], [published]
            FROM [[forums_groups]]
            WHERE [id] = {gid}';

        $types = array('integer', 'text', 'text', 'text', 'integer', 'boolean', 'boolean');
        $result = $GLOBALS['db']->queryRow($sql, $params, $types);
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
        $params = array();
        $params['published'] = true;

        $sql = '
            SELECT
                [id], [title], [description], [fast_url], [order], [locked], [published]
            FROM [[forums_groups]]';
            if ($onlyPublished) {
                $sql .= ' WHERE [published] = {published}';
            }
            $sql .= ' ORDER BY [order] ASC';

        $types = array('integer', 'text', 'text', 'text', 'integer', 'boolean', 'boolean');
        $result = $GLOBALS['db']->queryAll($sql, $params, $types);
        return $result;
    }

}