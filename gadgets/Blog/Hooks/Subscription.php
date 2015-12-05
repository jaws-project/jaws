<?php
/**
 * Forums gadget hook
 *
 * @category    GadgetHook
 * @package     Forums
 */
class Blog_Hooks_Subscription extends Jaws_Gadget_Hook
{
    /**
     * Returns available subscription items
     *
     * @access  public
     * @return array An array of subscription
     */
    function Execute()
    {
        $blogItems = array();

        $cModel = $this->gadget->model->load('Categories');

        $categories = $cModel->GetCategories(false);
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $item = array();
                $item['action'] = 'Category';
                $item['reference'] = $category['id'];
                $item['title'] = _t('BLOG_CATEGORY', $category['name']);
                $item['url'] = $this->gadget->urlMap('ShowCategory', array('id' => $category['id']));
                $blogItems[] = $item;
            }
        }

        return $blogItems;
    }

}