<?php
/**
 * Blog - Sitemap hook
 *
 * @category    GadgetHook
 * @package     Blog
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Hooks_Sitemap extends Jaws_Gadget_Hook
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
        switch ($data_type) {
            case 1:
                $cModel = $this->gadget->model->load('Categories');
                $categories = $cModel->GetCategories();
                if (Jaws_Error::IsError($categories)) {
                    return $categories;
                }

                foreach ($categories as $category) {
                    $cat = empty($category['fast_url']) ? $category['id'] : $category['fast_url'];
                    $result[] = array(
                        'id'     => $category['id'],
                        'parent' => $category['id'],
                        'title'  => $category['name'],
                        'url'    => $this->gadget->urlMap('ShowCategory', array('id' => $cat), true),
                    );
                }
                break;

            case 2:
                $pModel = $this->gadget->model->load('Posts');
                $posts  = $pModel->GetPosts(array('published' => true, 'stop_time' => $GLOBALS['db']->Date()));
                if (Jaws_Error::IsError($posts)) {
                    return $posts;
                }
                foreach ($posts as $post) {
                    $entry = empty($post['fast_url']) ? $post['id'] : $post['fast_url'];
                    $categories = explode(",", $post['categories']);
                    $result[] = array(
                        'id'    => $post['id'],
                        'title' => $post['title'],
                        'parent' => $categories[0],
                        'url'   => $this->gadget->urlMap('SingleView', array('id' => $entry), true),
                    );
                }
                break;

            default:
                $cModel = $this->gadget->model->load('Categories');
                $categories = $cModel->GetCategories();
                if (Jaws_Error::IsError($categories)) {
                    return $categories;
                }

                foreach ($categories as $category) {
                    $result[] = array(
                        'id'     => $category['id'],
                        'title'  => $category['name'],
                    );
                }
        }

        return $result;
    }

}