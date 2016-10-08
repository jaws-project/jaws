<?php
/**
 * PrivateMessage Gadget
 *
 * @category    GadgetModel
 * @package     PrivateMessage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class PrivateMessage_Model_Users extends Jaws_Gadget_Model
{
    /**
     * Get groups
     *
     * @access  public
     * @param   $term   Search term(searched in username, nickname and email)
     * @return  array   Returns an array of the available groups
     */
    function GetGroups($term)
    {
        $groupsTable = Jaws_ORM::getInstance()->table('groups');
        $groupsTable->select('id:integer', 'name', 'title', 'description', 'enabled:boolean');

        $term = Jaws_UTF8::strtolower($term);
        $groupsTable->where('enabled', true);

        $groupsTable->and()->openWhere('lower(name)', $term, 'like');
        $groupsTable->or()->closeWhere('lower(title)',$term, 'like');

        $groupsTable->orderBy('name');
        return $groupsTable->fetchAll();
    }
 }