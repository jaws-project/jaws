<?php
/**
 * Phoo - Sitemap hook
 *
 * @category    GadgetHook
 * @package     Phoo
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Hooks_Sitemap extends Jaws_Gadget_Hook
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
        if ($data_type == 0) {
            $gModel = $this->gadget->model->load('Groups');
            $groups = $gModel->GetGroups(true);
            if (Jaws_Error::IsError($groups)) {
                return $groups;
            }

            foreach ($groups as $group) {
                $result[] = array(
                    'id'     => $group['id'],
                    'title'  => $group['name'],
                );
            }
        } elseif ($data_type == 1 || $data_type == 2) {
            $gModel = $this->gadget->model->load('Groups');
            $groups = $gModel->GetGroups(true);
            if (Jaws_Error::IsError($groups)) {
                return $groups;
            }
            foreach ($groups as $group) {
                $entry = empty($group['fast_url']) ? $group['id'] : $group['fast_url'];
                $result[] = array(
                    'id'     => $group['id'],
                    'parent' => $group['id'],
                    'title'  => $group['name'],
                    'lastmod'=> null,
                    'url'    => $this->gadget->urlMap('AlbumList', array('group' => $entry), true),
                );
            }

            if ($data_type == 2) {
                $pModel = $this->gadget->model->load('Albums');
                $albums  = $pModel->GetAlbums('name', 'asc', 0, true);
                if (Jaws_Error::IsError($albums)) {
                    return $albums;
                }
                foreach ($albums as $album) {
                    $entry = empty($album['fast_url']) ? $album['id'] : $album['fast_url'];
                    $result[] = array(
                        'id'        => $album['id'],
                        'parent'    => null,
                        'title'     => $album['name'],
                        'lastmod'   => $album['createtime'],
                        'url'       => $this->gadget->urlMap('ViewAlbum', array('id' => $entry), true),
                    );
                }
            }
        }
        return $result;
    }

}