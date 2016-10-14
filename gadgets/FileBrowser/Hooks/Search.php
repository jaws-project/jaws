<?php
/**
 * FileBrowser - Search gadget hook
 *
 * @category   GadgetHook
 * @package    FileBrowser
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FileBrowser_Hooks_Search extends Jaws_Gadget_Hook
{
    /**
     * Gets the gadget's search fields
     *
     * @access  public
     * @return  array   array of search fields
     */
    function GetOptions() {
        return array(
            'filebrowser' => array('title', 'description'),
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
        if (!$this->gadget->GetPermission('OutputAccess')) {
            return array();
        }

        if ($GLOBALS['app']->Registry->fetch('frontend_avail', 'FileBrowser') != 'true') {
            return array();
        }

        $objORM->table('filebrowser');
        $objORM->select('id', 'filename', 'title', 'description', 'fast_url', 'path', 'updatetime');
        $objORM->loadWhere('search.terms');
        $files = $objORM->orderBy('updatetime desc')->fetchAll();
        if (Jaws_Error::IsError($files)) {
            return false;
        }

        $fModel = $this->gadget->model->load('Files');
        $date = Jaws_Date::getInstance();
        $result = array();
        foreach ($files as $file) {
            $item = array();
            $item['title'] = $file['title'];
            $filepath = ltrim($file['path']. '/'.$file['filename'], '/');
            if (is_dir($fModel->GetFileBrowserRootDir() . $filepath)) {
                $item['url']   = $this->gadget->urlMap('Display', array('path' => $filepath));
                $item['image'] = 'images/mimetypes/folder.png';
            } else {
                $fid = empty($file['fast_url'])? $file['id'] : $file['fast_url'];
                $item['url'] = $this->gadget->urlMap('FileInfo', array('id' => $fid));
                $filetype = ltrim(strrchr($file['filename'], '.'), '.');
                $fileicon = $fModel->getExtImage($filetype);
                if (is_file(JAWS_PATH . 'images/'. $fileicon)) {
                    $item['image'] = 'images/'. $fileicon;
                } else {
                    $item['image'] = 'images/mimetypes/unknown.png';
                }
            }

            $item['snippet'] = $file['description'];
            $item['date']    = $date->ToISO($file['updatetime']);
            $result[] = $item;
        }

        return $result;
    }

}