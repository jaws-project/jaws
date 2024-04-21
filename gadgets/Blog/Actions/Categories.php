<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2024 Jaws Development Group
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
                    'name'              => $this::t('UNCATEGORIZED'),
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

        $this->app->layout->addLink(
            array(
                'href'  => $this->gadget->urlMap('ShowAtomCategory', array('id' => $cat)),
                'type'  => 'application/atom+xml',
                'rel'   => 'alternate',
                'title' => 'Atom - '. $name
            )
        );
        $this->app->layout->addLink(
            array(
                'href'  => $this->gadget->urlMap('ShowRSSCategory', array('id' => $cat)),
                'type'  => 'application/rss+xml',
                'rel'   => 'alternate',
                'title' => 'RSS 2.0 - '. $name
            )
        );

        $this->title = $name;
        $this->description = $catInfo['meta_description'];
        $this->AddToMetaKeywords($catInfo['meta_keywords']);
        $tpl->SetBlock('view_category');
        $tpl->SetVariable('title', $name);

        $total  = $cModel->GetCategoryNumberOfPages($catInfo['id']);
        $limit  = $this->gadget->registry->fetch('last_entries_limit');
        $params = array('id'  => $cat);
        $this->gadget->action->load('PageNavigation')->pagination(
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
        if ($this->app->requestedActionMode === 'normal') {
            $tFilename = 'Categories.html';
        } else {
            $tFilename = 'Categories0.html';
        }
        $tpl = $this->gadget->template->load($tFilename);
        $tpl->SetBlock('categories_list');
        $tpl->SetVariable('title', $this::t('CATEGORIES'));
        $pModel = $this->gadget->model->load('Posts');
        $entries = $pModel->GetEntriesAsCategories();
        if (!Jaws_Error::IsError($entries)) {
            $cModel = $this->gadget->model->load('Categories');
            $howmany = $cModel->GetCategoryNumberOfPages(0);
            if (!empty($howmany)) {
                $entries[] = array(
                    'id'        =>  0,
                    'name'      =>  $this::t('UNCATEGORIZED'),
                    'fast_url'  => '',
                    'howmany'   => $howmany,
                );
            }

            foreach ($entries as $e) {
                $tpl->SetBlock('categories_list/item');
                $tpl->SetVariable('category', $e['name']);
                $cid = empty($e['fast_url']) ? $e['id'] : $e['fast_url'];
                $tpl->SetVariable('url', $this->gadget->urlMap('ShowCategory', array('id' => $cid)));

                if (Jaws_FileManagement_File::file_exists(ROOT_DATA_PATH . "blog/categories/{$e['id']}.png")) {
                    $tpl->SetVariable('url_image', $this->app->getDataURL("blog/categories/{$e['id']}.png"));
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