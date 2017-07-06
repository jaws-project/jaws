<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Categories extends Blog_Actions_Default
{
    /**
     * Displays a list of blog posts included on the given category
     *
     * @access  public
     * @param   int     $cat    category ID
     * @return  string  XHTML template content
     */
    function ShowCategory($cat = null)
    {
        $cModel = $this->gadget->model->load('Categories');
        $pModel = $this->gadget->model->load('Posts');

        $rqst = $this->gadget->request->fetch(array('id', 'page'), 'get');
        $page = $rqst['page'];
        if (is_null($page) || $page <= 0 ) {
            $page = 1;
        }

        if (is_null($cat)) {
            if (empty($rqst['id'])) {
                $catInfo = array(
                    'id'                => 0,
                    'name'              => _t('BLOG_UNCATEGORIZED'),
                    'fast_url'          => '',
                    'description'       => '',
                    'meta_keywords'     => '',
                    'meta_description'  => '',
                );
            } else {
                $cat = Jaws_XSS::defilter($rqst['id']);
                $catInfo = $cModel->GetCategory($cat);
                if (Jaws_Error::IsError($catInfo) || empty($catInfo)) {
                    return Jaws_HTTPError::Get(404);
                }

                // Check dynamic ACL
                if (!$this->gadget->GetPermission('CategoryAccess', $catInfo['id'])) {
                    return Jaws_HTTPError::Get(403);
                }
            }
        }

        $name = $catInfo['name'];
        $tpl = $this->gadget->template->load('CategoryPosts.html');

        $GLOBALS['app']->Layout->addLink(
            array(
                'href'  => $this->gadget->urlMap('ShowAtomCategory', array('id' => $cat)),
                'type'  => 'application/atom+xml',
                'rel'   => 'alternate',
                'title' => 'Atom - '. $name
            )
        );
        $GLOBALS['app']->Layout->addLink(
            array(
                'href'  => $this->gadget->urlMap('ShowRSSCategory', array('id' => $cat)),
                'type'  => 'application/rss+xml',
                'rel'   => 'alternate',
                'title' => 'RSS 2.0 - '. $name
            )
        );

        $this->SetTitle($name);
        $this->AddToMetaKeywords($catInfo['meta_keywords']);
        $this->SetDescription($catInfo['meta_description']);
        $tpl->SetBlock('view_category');
        $tpl->SetVariable('title', $name);

        $total  = $cModel->GetCategoryNumberOfPages($catInfo['id']);
        $limit  = $this->gadget->registry->fetch('last_entries_limit');
        $params = array('id'  => $cat);
        $this->gadget->action->load('Navigation')->pagination(
            $tpl,
            $page,
            $limit,
            $total,
            'ShowCategory',
            $params
        );
        $entries = $pModel->GetEntriesByCategory($catInfo['id'], $page);
        if (!Jaws_Error::IsError($entries)) {
            foreach ($entries as $entry) {
                $this->ShowEntry($tpl, 'view_category', $entry);
            }
        }

        $tpl->ParseBlock('view_category');
        return $tpl->Get();
    }

    /**
     * Displays a list of blog categories with a link to each one's posts and xml feeds
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function CategoriesList()
    {
        $tpl = $this->gadget->template->load('Categories.html');
        $tpl->SetBlock('categories_list');
        $tpl->SetVariable('title', _t('BLOG_CATEGORIES'));
        $pModel = $this->gadget->model->load('Posts');
        $entries = $pModel->GetEntriesAsCategories();
        if (!Jaws_Error::IsError($entries)) {
            $cModel = $this->gadget->model->load('Categories');
            $howmany = $cModel->GetCategoryNumberOfPages(0);
            if (!empty($howmany)) {
                $entries[] = array(
                    'id'        =>  0,
                    'name'      =>  _t('BLOG_UNCATEGORIZED'),
                    'fast_url'  => '',
                    'howmany'   => $howmany,
                );
            }

            foreach ($entries as $e) {
                $tpl->SetBlock('categories_list/item');
                $tpl->SetVariable('category', $e['name']);
                $cid = empty($e['fast_url']) ? $e['id'] : $e['fast_url'];
                $tpl->SetVariable('url', $this->gadget->urlMap('ShowCategory', array('id' => $cid)));

                if (file_exists(JAWS_DATA . "blog/categories/{$e['id']}.png")) {
                    $tpl->SetVariable('url_image', $GLOBALS['app']->getDataURL("blog/categories/{$e['id']}.png"));
                } else {
                    $tpl->SetVariable('url_image', 'data:image/png;base64,');
                }

                $tpl->SetVariable(
                    'rssfeed',
                    $this->gadget->urlMap('ShowRSSCategory', array('id' => $cid))
                );
                $tpl->SetVariable(
                    'atomfeed',
                    $this->gadget->urlMap('ShowAtomCategory', array('id' => $cid))
                );
                $tpl->SetVariable('howmany', $e['howmany']);

                // display subscription if installed
                if (Jaws_Gadget::IsGadgetInstalled('Subscription')) {
                    $sHTML = Jaws_Gadget::getInstance('Subscription')->action->load('Subscription');
                    $tpl->SetVariable('subscription', $sHTML->ShowSubscription('Blog', 'Category', $e['id']));
                }

                $tpl->ParseBlock('categories_list/item');
            }
        }
        $tpl->ParseBlock('categories_list');

        return $tpl->Get();
    }

}