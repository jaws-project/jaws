<?php
/**
 * Poll Gadget
 *
 * @category   GadgetModel
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Poll_Model_Group extends Jaws_Gadget_Model
{

    /**
     * Retrieve information of a group
     *
     * @access  public
     * @param   int     $gid    group ID
     * @return  mixed   An array of group's data and Jaws_Error on error
     */
    function GetPollGroup($gid)
    {
        $table = Jaws_ORM::getInstance()->table('poll_groups');
        $table->select('id', 'title', 'visible')->where('id', $gid);
        $result = $table->fetchRow();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }

    /**
     * Retrieve poll groups
     *
     * @access  public
     * @param   int     $limit  limit groups
     * @param   int     $offset offset groups
     * @return  mixed   An array of available poll groups and Jaws_Error on error
     */
    function GetPollGroups($limit = 0, $offset = null)
    {
        if (!empty($limit)) {
            $res = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage());
            }
        }

        $table = Jaws_ORM::getInstance()->table('poll_groups');
        $table->select('id', 'title', 'visible')->orderBy('id asc');
        $result = $table->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($result->getMessage());
        }

        return $result;
    }
}