<?php
/**
 * Directory - Search gadget hook
 *
 * @category    GadgetHook
 * @package     Directory
 */
class Directory_Hooks_Search extends Jaws_Gadget_Hook
{
    /**
     * Gets search fields of the gadget
     *
     * @access  public
     * @return  array   List of search fields
     */
    function GetOptions() {
        return array(
            'directory' => array('title', 'description', 'user_filename'),
        );
    }

    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $table  Table name
     * @param   object  $objORM Jaws_ORM instance object
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($table, &$objORM)
    {
        $objORM->table('directory');
        $objORM->select('id', 'title', 'description', 'user_filename', 'update_time:integer');
        $objORM->loadWhere('search.terms');
        $result = $objORM->orderBy('id desc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $files = array();
        foreach ($result as $p) {
            $file = array();
            $file['title']   = $p['title'];
            $file['url']     = $this->gadget->urlMap('Directory', array('id'  => $p['id']));
            $file['image']   = 'gadgets/Directory/Resources/images/logo.png';
            $file['snippet'] = $p['description'];
            $file['date']    = $p['update_time'];
            $stamp           = $p['update_time'];
            $files[$stamp]   = $file;
        }

        return $files;
    }
}