<?php
/**
 * Directory - Tags gadget hook
 *
 * @category    GadgetHook
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Hooks_Tags extends Jaws_Gadget_Hook
{
    /**
     * Fetches files having specific tag
     *
     * @access  public
     * @param   string  $action     Action name
     * @param   array   $references Array of References
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($action, $references)
    {
        if(empty($action) || !is_array($references) ||empty($references)) {
            return false;
        }

        $table = Jaws_ORM::getInstance()->table('directory');
        $table->select('id:integer', 'title', 'description', 'updatetime');
        $result = $table->where('hidden', false)->and()->where('id', $references, 'in')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        $date = Jaws_Date::getInstance();
        $files = array();
        foreach ($result as $r) {
            $file = array();
            $file['title']   = $r['title'];
            $file['url']     = $this->gadget->urlMap('Directory', array('id' => $r['id']));
            $file['outer']   = false;
            $file['image']   = 'gadgets/Directory/Resources/images/logo.png';
            $file['snippet'] = $r['description'];
            $file['date']    = $date->ToISO($r['updatetime']);
            $files[$r['id']] = $file;
        }

        return $files;
    }

}