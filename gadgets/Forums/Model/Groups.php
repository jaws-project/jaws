<?php
/**
 * Forums Gadget
 *
 * @category   GadgetModel
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
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
        $table = Jaws_ORM::getInstance()->table('forums_groups');
        $table->select('id:integer', 'title', 'description', 'fast_url',
                       'order:integer', 'locked:boolean', 'published:boolean');
        $result = $table->where('id', (int)$gid)->fetchRow();
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
        $table = Jaws_ORM::getInstance()->table('forums_groups');
        $table->select('id:integer', 'title', 'description', 'fast_url',
                       'order:integer', 'locked:boolean', 'published:boolean');

        if ($onlyPublished) {
            $table->where('published', true);
        }
        $result = $table->orderBy('order asc')->fetchAll();
        return $result;
    }

}