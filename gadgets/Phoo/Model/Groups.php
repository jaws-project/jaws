<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetModel
 * @package    Phoo
 * @author     Hamid Reza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2013-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Model_Groups extends Jaws_Gadget_Model
{
    /**
     * Get Groups list
     *
     * @access  public
     * @return  mixed    array with the groups or Jaws_Error on error
     */
    function GetGroups()
    {
        $table = Jaws_ORM::getInstance()->table('phoo_group');
        $table->select('id', 'name', 'fast_url', 'description', 'meta_keywords', 'meta_description');
        return $table->fetchAll();
    }

    /**
     * Get info of selected Group
     *
     * @access  public
     * @param   int      $gid         Group ID
     * @return  mixed    array with the groups or Jaws_Error on error
     */
    function GetGroup($gid)
    {
        $table = Jaws_ORM::getInstance()->table('phoo_group');
        $table->select('id', 'name', 'fast_url', 'description', 'meta_keywords', 'meta_description')->where('id', $gid);
        return $table->fetchRow();
    }
}