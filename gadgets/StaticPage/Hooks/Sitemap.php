<?php
/**
 * StaticPage - Sitemap hook
 *
 * @category    GadgetHook
 * @package     StaticPage
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Hooks_Sitemap extends Jaws_Gadget_Hook
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
            $gModel = $this->gadget->model->load('Group');
            $categories = $gModel->GetGroups(true);
            if (Jaws_Error::IsError($categories)) {
                return $categories;
            }

            foreach ($categories as $category) {
                $result[] = array(
                    'id'     => $category['id'],
                    'title'  => $category['title'],
                );
            }
        } elseif ($data_type == 1 || $data_type == 2) {
            $gModel = $this->gadget->model->load('Group');
            $categories = $gModel->GetGroups(true);
            if (Jaws_Error::IsError($categories)) {
                return $categories;
            }
            foreach ($categories as $category) {
                $cat = empty($category['fast_url']) ? $category['id'] : $category['fast_url'];
                $result[] = array(
                    'id'     => $category['id'],
                    'parent' => $category['id'],
                    'title'  => $category['title'],
                    'lastmod'=> null,
                    'url'    => $this->gadget->urlMap('GroupPages', array('gid' => $cat), true),
                );
            }

            if ($data_type == 2) {
                $pModel = $this->gadget->model->load('Page');
                $pages  = $pModel->GetPages(null, null, 1, false, true);
                if (Jaws_Error::IsError($pages)) {
                    return $pages;
                }
                foreach ($pages as $page) {
                    $entry = empty($page['fast_url']) ? $page['id'] : $page['fast_url'];
                    $result[] = array(
                        'id'        => $page['base_id'],
                        'parent'    => $page['group_id'],
                        'title'     => $page['title'],
                        'lastmod'   => $page['updated'],
                        'url'       => $this->gadget->urlMap('Page', array('pid' => $entry), true),
                    );
                }
            }
        }
        return $result;
    }

}