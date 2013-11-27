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

                foreach ($categories as $categoriy) {
                    $cat = empty($categoriy['fast_url']) ? $categoriy['id'] : $categoriy['fast_url'];
                    $result[] = array(
                        'id'     => $categoriy['id'],
                        'parent' => $categoriy['id'],
                        'title'  => $categoriy['title'],
                        'url'    => $this->gadget->urlMap('ShowCategory', array('id' => $cat)),
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
                    $result[] = array(
                        'id'    => $post['id'],
                        'title' => $post['title'],
                        'url'   => $this->gadget->urlMap('SingleView', array('id' => $entry)),
                    );
                }
                break;

            default:
                $cModel = $this->gadget->model->load('Categories');
                $categories = $cModel->GetCategories();
                if (Jaws_Error::IsError($categories)) {
                    return $categories;
                }

                foreach ($categories as $categoriy) {
                    $result[] = array(
                        'id'     => $categoriy['id'],
                        'title'  => $categoriy['name'],
                    );
                }
        }

        return $result;
    }

}