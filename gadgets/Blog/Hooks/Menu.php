<?php
/**
 * Blog - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Blog
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2007-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $items = array();
        $items[] = array('url'    => $this->gadget->urlMap('DefaultAction'),
                         'title'  => $this->gadget->title);
        $items[] = array('url'    => $this->gadget->urlMap('Archive'),
                         'title'  => $this::t('ARCHIVE'));
        $items[] = array('url'    => $this->gadget->urlMap('CategoriesList'),
                         'title'  => $this::t('ACTIONS_CATEGORIESLIST'),
                         'title2' => $this::t('CATEGORIES'));
        $items[] = array('url'    => $this->gadget->urlMap('Types'),
                         'title'  => $this::t('ACTIONS_TYPES'),
                         'title2' => $this::t('TYPES'));
        $items[] = array('url'    => $this->gadget->urlMap('PopularPosts'),
                         'title'  => $this::t('POPULAR_POSTS'));
        $items[] = array('url'    => $this->gadget->urlMap('PostsAuthors'),
                         'title'  => $this::t('POSTS_AUTHORS'));

        //Blog model
        $pModel      = $this->gadget->model->load('Posts');
        $cModel      = $this->gadget->model->load('Categories');
        $categories = $cModel->GetCategories();
        if (!Jaws_Error::IsError($categories)) {
            foreach ($categories as $cat) {
                $url = $this->gadget->urlMap(
                    'ShowCategory',
                    array('id' => empty($cat['fast_url'])? $cat['id'] : $cat['fast_url'])
                );
                $items[] = array(
                    'url'   => $url,
                    'title' => $cat['name'],
                    'permission' => array(
                        'key' => 'CategoryAccess',
                        'subkey' => $cat['id']
                    )
                );
            }
        }

        $entries = $pModel->GetEntries('');
        if (!Jaws_Error::IsError($entries)) {
            foreach ($entries as $entry) {
                $url = $this->gadget->urlMap(
                    'SingleView',
                    array('id' => empty($entry['fast_url'])? $entry['id'] : $entry['fast_url'])
                );
                $items[] = array(
                    'url'   => $url,
                    'title' => $entry['title']
                );
            }
        }

        return $items;
    }
}
