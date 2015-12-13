<?php
/**
 * Directory - Search gadget hook
 *
 * @category    GadgetHook
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
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
        return array(array('[title]', '[description]', '[user_filename]'));
    }

    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $pSql   Prepared search(WHERE) SQL
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($pSql = '')
    {
        $sql = '
            SELECT
               [id], [title], [description], [user_filename], [updatetime]
            FROM [[directory]]
            WHERE
                [hidden] = {hidden}
            ';

        $sql .= ' AND ' . $pSql;
        $sql .= ' ORDER BY id desc';

        $params = array();
        $params['hidden'] = false;
        $types = array('text', 'text', 'text', 'integer');
        $result = Jaws_DB::getInstance()->queryAll($sql, $params, $types);
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        $date  = Jaws_Date::getInstance();
        $files = array();
        foreach ($result as $p) {
            $file = array();
            $file['title']   = $p['title'];
            $file['url']     = $this->gadget->urlMap('Directory', array('id'  => $p['id']));
            $file['image']   = 'gadgets/Directory/Resources/images/logo.png';
            $file['snippet'] = $p['description'];
            $file['date']    = $p['updatetime'];
            $stamp           = $p['updatetime'];
            $files[$stamp]   = $file;
        }

        return $files;
    }
}