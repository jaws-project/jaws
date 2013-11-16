<?php
/**
 * StaticPage Gadget
 *
 * @category   GadgetModel
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Model_Admin_Group extends StaticPage_Model_Group
{

    /**
     * Creates a new group
     *
     * @access  public
     * @param   string  $title      Title of the group
     * @param   string  $fast_url   The fast URL of the group
     * @param   string  $meta_keys  Meta keywords
     * @param   string  $meta_desc  Meta description
     * @param   bool    $visible    Visibility status of the group
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function InsertGroup($title, $fast_url, $meta_keys, $meta_desc, $visible)
    {
        $fast_url = empty($fast_url)? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'static_pages_groups', true);

        $params['title']            = $title;
        $params['fast_url']         = $fast_url;
        $params['meta_keywords']    = $meta_keys;
        $params['meta_description'] = $meta_desc;
        $params['visible']          = (bool)$visible;

        $spgTable = Jaws_ORM::getInstance()->table('static_pages_groups');
        $res = $spgTable->insert($params)->exec();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('STATICPAGE_NAME'));
        }

        $this->gadget->acl->insert('AccessGroup', $res, true);
        $this->gadget->acl->insert('ManageGroup', $res, true);
        return true;
    }

    /**
     * Updates the group
     *
     * @access  public
     * @param   int     $gid        Group ID
     * @param   string  $title      Title of the group
     * @param   string  $fast_url   The fast URL of the group
     * @param   string  $meta_keys  Meta keywords
     * @param   string  $meta_desc  Meta description
     * @param   bool    $visible    Visibility status of the group
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdateGroup($gid, $title, $fast_url, $meta_keys, $meta_desc, $visible)
    {
        $fast_url = empty($fast_url) ? $title : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'static_pages_groups', false);

        $params['title']            = $title;
        $params['fast_url']         = $fast_url;
        $params['meta_keywords']    = $meta_keys;
        $params['meta_description'] = $meta_desc;
        $params['visible']          = (bool)$visible;

        $spgTable = Jaws_ORM::getInstance()->table('static_pages_groups');
        $res = $spgTable->update($params)->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('STATICPAGE_NAME'));
        }

        return true;
    }

    /**
     * Gets total number of groups
     *
     * @access  public
     * @return  mixed   Number of groups or Jaws_Error
     */
    function GetGroupsCount()
    {
        $spgTable = Jaws_ORM::getInstance()->table('static_pages_groups');
        $count = $spgTable->select('count(id)')->fetchOne();
        if (Jaws_Error::IsError($count)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('STATICPAGE_NAME'));
        }

        return $count;
    }

    /**
     * Deletes the group
     *
     * @access  public
     * @param   int     $gid   Group ID
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function DeleteGroup($gid)
    {
        if ($gid == 1) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_GROUP_NOT_DELETABLE'), _t('STATICPAGE_NAME'));
        }

        $spgTable = Jaws_ORM::getInstance()->table('static_pages_groups');
        $res = $spgTable->delete()->where('id', $gid)->exec();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_QUERY_FAILED'), _t('STATICPAGE_NAME'));
        }

        $this->gadget->acl->delete('AccessGroup', $gid);
        $this->gadget->acl->delete('ManageGroup', $gid);
        return true;
    }


}