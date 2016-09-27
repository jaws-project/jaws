<?php
/**
 * Directory - Sitemap hook
 *
 * @category    GadgetHook
 * @package     Directory
 */
class Directory_Hooks_Sitemap extends Jaws_Gadget_Hook
{
    /**
     * Fetch items can be included in sitemap
     *
     * @access  public
     * @param   int     $data_type      Data type
     * @param   int     $updated_time   Last updated time
     *          (0: first level of categories, 1: all levels of categories, 2: flatted all items)
     * @return  mixed   Array of data otherwise Jaws_Error
     */
    function Execute($data_type = 0, $updated_time = 0)
    {
        $result = array();
        if ($data_type == 0 || $data_type == 1) {
            $result[] = array('id' => Directory_Info::FILE_TYPE_TEXT, 'title' => _t('DIRECTORY_FILE_TYPE_TEXT'));
            $result[] = array('id' => Directory_Info::FILE_TYPE_IMAGE, 'title' => _t('DIRECTORY_FILE_TYPE_IMAGE'));
            $result[] = array('id' => Directory_Info::FILE_TYPE_AUDIO, 'title' => _t('DIRECTORY_FILE_TYPE_AUDIO'));
            $result[] = array('id' => Directory_Info::FILE_TYPE_VIDEO, 'title' => _t('DIRECTORY_FILE_TYPE_VIDEO'));
            $result[] = array('id' => Directory_Info::FILE_TYPE_ARCHIVE, 'title' => _t('DIRECTORY_FILE_TYPE_ARCHIVE'));
            $result[] = array('id' => Directory_Info::FILE_TYPE_UNKNOWN, 'title' => _t('DIRECTORY_FILE_TYPE_OTHER'));
        }

        if ($data_type == 2) {
            $fModel = $this->gadget->model->loadAdmin('Files');
            $files = $fModel->GetFiles(array('hidden' => false, 'published' => true));
            if (Jaws_Error::IsError($files)) {
                return $files;
            }
            foreach ($files as $file) {
                $result[] = array(
                    'id' => $file['id'],
                    'parent' => $file['file_type'],
                    'title' => $file['title'],
                    'lastmod' => $file['update_time'],
                    'url' => $this->gadget->urlMap('Directory', array('type' => $file['file_type']), true),
                );
            }
        }
        return $result;
    }

}