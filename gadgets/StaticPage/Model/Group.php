<?php
/**
 * StaticPage Gadget
 *
 * @category   GadgetModel
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Model_Group extends Jaws_Gadget_Model
{
    /**
     * Gets properties of a group
     *
     * @access  public
     * @param   int     $id  Group ID
     * @return  mixed   Array of group info or Jaws_Error
     */
    function GetGroup($id)
    {
        $spgTable = Jaws_ORM::getInstance()->table('static_pages_groups');
        $spgTable->select('id:integer', 'title', 'fast_url', 'meta_keywords', 'meta_description', 'visible:boolean');

        if (is_numeric($id)) {
            $spgTable->where('id', $id);
        } else {
            $spgTable->where('fast_url', $id);
        }

        $group = $spgTable->fetchRow();
        if (Jaws_Error::IsError($group)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'));
        }

        return $group;
    }

    /**
     * Returns list of groups
     *
     * @access  public
     * @param   bool    $visible    Visibility status of groups
     * @param   bool    $limit      Number of groups to retrieve
     * @param   bool    $offset     Start offset of result boundaries
     * @return  mixed   Array of groups or Jaws_Error
     */
    function GetGroups($visible = null, $limit = null, $offset = null)
    {
        $spgTable = Jaws_ORM::getInstance()->table('static_pages_groups');
        $spgTable->select('id:integer', 'title', 'fast_url', 'visible:boolean')->limit($limit, $offset);

        if ($visible != null) {
            $spgTable->where('visible', (bool)$visible);
        }
        $groups = $spgTable->fetchAll();
        if (Jaws_Error::IsError($groups)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'));
        }

        return $groups;
    }
}