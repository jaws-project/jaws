<?php
require_once JAWS_PATH. 'gadgets/Blog/Actions/Default.php';
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Categories extends Blog_Actions_Default
{
    /**
     * Displays a list of blog posts included on the given category
     *
     * @access  public
     * @param   int     category ID
     * @return  string  XHTML template content
     */
    function ShowCategory($cat = '')
    {
        $cModel = $this->gadget->loadModel('Categories');
        $pModel = $this->gadget->loadModel('Posts');

        $post = jaws()->request->fetch(array('id', 'page'), 'get');
        $page = $post['page'];
        if (is_null($page) || $page <= 0 ) {
            $page = 1;
        }

        if (empty($cat)) {
            $cat = Jaws_XSS::defilter($post['id'], true);
        }

        $catInfo = $cModel->GetCategory($cat);

        if (!Jaws_Error::IsError($catInfo) && isset($catInfo['id'])) {
            $name = $catInfo['name'];
            $tpl = $this->gadget->loadTemplate('CategoryPosts.html');

            $GLOBALS['app']->Layout->AddHeadLink(
                $this->gadget->urlMap('ShowAtomCategory', array('id' => $cat)),
                'alternate',
                'application/atom+xml',
                'Atom - '. $name
            );
            $GLOBALS['app']->Layout->AddHeadLink(
                $this->gadget->urlMap('ShowRSSCategory', array('id' => $cat)),
                'alternate',
                'application/rss+xml',
                'RSS 2.0 - '. $name
            );

            $this->SetTitle($name);
            $this->AddToMetaKeywords($catInfo['meta_keywords']);
            $this->SetDescription($catInfo['meta_description']);
            $tpl->SetBlock('view_category');
            $tpl->SetVariable('title', $name);

            $total  = $cModel->GetCategoryNumberOfPages($catInfo['id']);
            $limit  = $this->gadget->registry->fetch('last_entries_limit');
            $params = array('id'  => $cat);
            $tpl->SetVariable('navigation',
                              $this->GetNumberedPageNavigation($page, $limit, $total, 'ShowCategory', $params));
            $entries = $pModel->GetEntriesByCategory($catInfo['id'], $page);
            if (!Jaws_Error::IsError($entries)) {
                foreach ($entries as $entry) {
                    $this->ShowEntry($tpl, 'view_category', $entry);
                }
            }

            $tpl->ParseBlock('view_category');
            return $tpl->Get();
        } else {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(404);
        }
    }


    /**
     * Displays a list of blog categories with a link to each one's posts and xml feeds
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function CategoriesList()
    {
        $tpl = $this->gadget->loadTemplate('Categories.html');
        $tpl->SetBlock('categories_list');
        $tpl->SetVariable('title', _t('BLOG_CATEGORIES'));
        $model = $this->gadget->loadModel('Posts');
        $entries = $model->GetEntriesAsCategories();
        if (!Jaws_Error::IsError($entries)) {
            foreach ($entries as $e) {
                if (!$this->gadget->GetPermission('CategoryAccess', $e['id'])) {
                    break;
                }
                $tpl->SetBlock('categories_list/item');
                $tpl->SetVariable('category', $e['name']);
                $cid = empty($e['fast_url']) ? $e['id'] : $e['fast_url'];
                $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Blog', 'ShowCategory', array('id' => $cid)));
                $tpl->SetVariable('rssfeed',
                    $GLOBALS['app']->Map->GetURLFor('Blog',
                        'ShowRSSCategory',
                        array('id' => $cid)));
                $tpl->SetVariable('atomfeed',
                    $GLOBALS['app']->Map->GetURLFor('Blog',
                        'ShowAtomCategory',
                        array('id' => $cid)));
                $tpl->SetVariable('howmany', $e['howmany']);
                $tpl->ParseBlock('categories_list/item');
            }
        }
        $tpl->ParseBlock('categories_list');

        return $tpl->Get();
    }



}