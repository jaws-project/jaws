<?php
/**
 * Blog - Sitemap hook
 *
 * @category    GadgetHook
 * @package     Blog
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2008-2021 Jaws Development Group
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
        $result = array(
            '/' => array(
                'id'     => 0,
                'parent' => 0,
                'title'  => _t('BLOG_TITLE'),
                'url'    => $this->gadget->urlMap('DefaultAction', array(), array('absolute' => true))
            ),
            'levels' => array(),
            'items'  => array()
        );
        if ($data_type == 0) {
            $cModel = $this->gadget->model->load('Categories');
            $categories = $cModel->GetCategories();
            if (Jaws_Error::IsError($categories)) {
                return $categories;
            }

            foreach ($categories as $category) {
                $result['levels'][] = array(
                    'id'     => $category['id'],
                    'title'  => $category['name'],
                );
            }
        } elseif ($data_type == 1 || $data_type == 2) {
            $cModel = $this->gadget->model->load('Categories');
            $categories = $cModel->GetCategories();
            if (Jaws_Error::IsError($categories)) {
                return $categories;
            }
            $result['levels'] = array();
            foreach ($categories as $category) {
                $cat = empty($category['fast_url']) ? $category['id'] : $category['fast_url'];
                $result['levels'][] = array(
                    'id'     => $category['id'],
                    'parent' => $category['id'],
                    'title'  => $category['name'],
                    'lastmod'  => $category['updatetime'],
                    'url'    => $this->gadget->urlMap(
                        'ShowCategory',
                        array('id' => $cat),
                        array('absolute' => true)
                    ),
                );
            }

            if($data_type==2) {
                $result['items'] = array();
                $pModel = $this->gadget->model->load('Posts');
                $posts  = $pModel->GetPosts(array('published' => true, 'stop_time' => Jaws_DB::getInstance()->date()));
                if (Jaws_Error::IsError($posts)) {
                    return $posts;
                }
                foreach ($posts as $post) {
                    $entry = empty($post['fast_url']) ? $post['id'] : $post['fast_url'];
                    $categories = explode(",", $post['categories']);
                    $result['items'][] = array(
                        'id'    => $post['id'],
                        'parent' => $categories[0],
                        'title' => $post['title'],
                        'lastmod'  => $post['updatetime'],
                        'url'   => $this->gadget->urlMap(
                            'SingleView',
                            array('id' => $entry),
                            array('absolute' => true)
                        ),
                    );
                }
            }
        }
        return $result;
    }

}