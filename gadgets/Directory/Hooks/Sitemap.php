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
        $result = array(
            '/' => array(
                'id'     => 0,
                'parent' => 0,
                'title'  => $this::t('TITLE'),
                'url'    => $this->gadget->urlMap('Directory', array(), array('absolute' => true))
            ),
            'levels' => array(),
            'items'  => array()
        );
        if ($data_type == 0 || $data_type == 1) {
            $result['levels'][] = array(
                'id' => Directory_Info::FILE_TYPE_TEXT,
                'title' => $this::t('FILE_TYPE_TEXT'),
                'url' => $this->gadget->urlMap(
                    'Directory',
                    array('type' => Directory_Info::FILE_TYPE_TEXT),
                    array('absolute' => true)
                )
            );
            $result['levels'][] = array(
                'id' => Directory_Info::FILE_TYPE_IMAGE,
                'title' => $this::t('FILE_TYPE_IMAGE'),
                'url' => $this->gadget->urlMap(
                    'Directory',
                    array('type' => Directory_Info::FILE_TYPE_IMAGE),
                    array('absolute' => true)
                )
            );
            $result['levels'][] = array(
                'id' => Directory_Info::FILE_TYPE_AUDIO,
                'title' => $this::t('FILE_TYPE_AUDIO'),
                'url' => $this->gadget->urlMap(
                    'Directory',
                    array('type' => Directory_Info::FILE_TYPE_AUDIO),
                    array('absolute' => true)
                )
            );
            $result['levels'][] = array(
                'id' => Directory_Info::FILE_TYPE_VIDEO,
                'title' => $this::t('FILE_TYPE_VIDEO'),
                'url' => $this->gadget->urlMap(
                    'Directory',
                    array('type' => Directory_Info::FILE_TYPE_VIDEO),
                    array('absolute' => true)
                )
            );
            $result['levels'][] = array(
                'id' => Directory_Info::FILE_TYPE_ARCHIVE,
                'title' => $this::t('FILE_TYPE_ARCHIVE'),
                'url' => $this->gadget->urlMap(
                    'Directory',
                    array('type' => Directory_Info::FILE_TYPE_ARCHIVE),
                    array('absolute' => true)
                )
            );
            $result['levels'][] = array(
                'id' => Directory_Info::FILE_TYPE_UNKNOWN,
                'title' => $this::t('FILE_TYPE_OTHER'),
                'url' => $this->gadget->urlMap(
                    'Directory',
                    array('type' => Directory_Info::FILE_TYPE_UNKNOWN),
                    array('absolute' => true)
                )
            );
        }

        if ($data_type == 2) {
            $fModel = $this->gadget->model->load('Files');
            $files = $fModel->GetFiles(array('published' => true));
            if (Jaws_Error::IsError($files)) {
                return $files;
            }
            foreach ($files as $file) {
                $result['items'][] = array(
                    'id' => $file['id'],
                    'parent' => $file['file_type'],
                    'title' => $file['title'],
                    'lastmod' => $file['update_time'],
                    'url' => $this->gadget->urlMap(
                        'Directory',
                        array('type' => $file['file_type']),
                        array('absolute' => true)
                    ),
                );
            }
        }

        return $result;
    }

}