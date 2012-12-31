<?php
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
class Blog_Actions_Categories extends Blog_HTML
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
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');

        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('id', 'page'), 'get');

        $page = $post['page'];
        if (is_null($page) || $page <= 0 ) {
            $page = 1;
        }

        if (empty($cat)) {
            $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
            $cat = $xss->defilter($post['id'], true);
        }

        $catInfo = $model->GetCategory($cat);
        if (!Jaws_Error::IsError($catInfo) && isset($catInfo['id'])) {
            $name = $catInfo['name'];
            $tpl = new Jaws_Template('gadgets/Blog/templates/');
            $tpl->Load('ViewCategory.html', true);

            $GLOBALS['app']->Layout->AddHeadLink($GLOBALS['app']->Map->GetURLFor('Blog',
                                                                                 'ShowAtomCategory',
                                                                                 array('id' => $cat)),
                                                 'alternate',
                                                 'application/atom+xml',
                                                 'Atom - '.$name);
            $GLOBALS['app']->Layout->AddHeadLink($GLOBALS['app']->Map->GetURLFor('Blog',
                                                                                 'ShowRSSCategory',
                                                                                 array('id' => $cat)),
                                                 'alternate',
                                                 'application/rss+xml',
                                                 'RSS 2.0 - '.$name);

            $this->SetTitle($name);
            $this->AddToMetaKeywords($catInfo['meta_keywords']);
            $this->SetDescription($catInfo['meta_description']);
            $tpl->SetBlock('view_category');
            $tpl->SetVariable('title', $name);

            $total  = $model->GetCategoryNumberOfPages($catInfo['id']);
            $limit  = $this->gadget->GetRegistry('last_entries_limit');
            $params = array('id'  => $cat);
            $tpl->SetVariable('navigation',
                              $this->GetNumberedPageNavigation($page, $limit, $total, 'ShowCategory', $params));
            $entries = $model->GetEntriesByCategory($catInfo['id'], $page);
            if (!Jaws_Error::IsError($entries)) {
                $res = '';
                $tpl->SetBlock('view_category/entry');
                $tplEntry = $tpl->GetRawBlockContent();
                foreach ($entries as $entry) {
                    $res .= $this->ShowEntry($entry, true, true, $tplEntry);
                }
                $tpl->SetCurrentBlockContent($res);
                $tpl->ParseBlock('view_category/entry');
            }

            $tpl->ParseBlock('view_category');
            return $tpl->Get();
        } else {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(404);
        }
    }

    /**
     * Displays a list of blog categories with a link to each one's posts
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function CategoriesList()
    {
        $this->SetTitle(_t('BLOG_CATEGORIES'));
        $layoutGadget = $GLOBALS['app']->LoadGadget('Blog', 'LayoutHTML');
        return $layoutGadget->CategoriesList();
    }

}