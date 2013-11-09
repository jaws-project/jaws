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
class Blog_Actions_Posts extends Blog_Actions_Default
{
    /**
     * Generates XHTML template
     * 
     * @access  public
     * @param   int     $cat    
     * @return  string  XHTML template content
     */
    function ViewPage($cat = null)
    {
        $page = jaws()->request->fetch('page', 'get');
        if (is_null($page) || $page <= 0 ) {
            $page = 1;
        }

        $GLOBALS['app']->Layout->AddHeadLink(
            $GLOBALS['app']->Map->GetURLFor('Blog', 'Atom'),
            'alternate',
            'application/atom+xml',
            'Atom - All'
        );
        $GLOBALS['app']->Layout->AddHeadLink(
            $GLOBALS['app']->Map->GetURLFor('Blog', 'RSS'),
            'alternate',
            'application/rss+xml',
            'RSS 2.0 - All'
        );
        /**
         * This will be supported in next Blog version - Bookmarks for each categorie
        $categories = $model->GetCategories();
        if (!Jaws_Error::IsError($categories)) {
        $GLOBALS['app']->Layout->AddHeadLink(
                $base_url.'blog.atom',
                'alternate',
                'application/atom+xml',
                'Atom - All'
            );
            foreach ($categories as $cat) {
                $name = $cat['name'];
            }
        }
        */

        $tpl = $this->gadget->template->load('Posts.html');
        $tpl->SetBlock('view');

        $model = $this->gadget->model->load('Posts');
        $entries = $model->GetEntriesAsPage($cat, $page);
        if (!Jaws_Error::IsError($entries) && count($entries) > 0) {
            $row = 0;
            $col = 0;
            $index = 0;
            $columns = (int) $this->gadget->registry->fetch('columns');
            $columns = ($columns <= 0)? 1 : $columns;
            foreach ($entries as $entry) {
                if ($col == 0) {
                    $tpl->SetBlock('view/entryrow');
                    $tpl->SetVariable('row', $row);
                }

                $tpl->SetBlock('view/entryrow/column');
                $tpl->SetVariable('col', $col);
                $res = $this->ShowEntry($tpl, 'view/entryrow/column', $entry);
                $tpl->ParseBlock('view/entryrow/column');

                $index++;
                $col = $index % $columns;
                if ($col == 0 || $index == count($entries)) {
                    $row++;
                    $tpl->ParseBlock('view/entryrow');
                }
            }
        }

        if ($tpl->VariableExists('navigation')) {
            $total = $model->GetNumberOfPages($cat);
            $limit = $this->gadget->registry->fetch('last_entries_limit');
            $tpl->SetVariable('navigation', $this->GetNumberedPageNavigation($page, $limit, $total, 'ViewPage'));
        }
        $tpl->ParseBlock('view');
        return $tpl->Get();
    }

    /**
     * Get CategoryEntries action params
     *
     * @access  private
     * @return  array    list of CategoryEntries action params
     */
    function CategoryEntriesLayoutParams()
    {
        $result = array();
        $bModel = $this->gadget->model->load('Categories');
        $categories = $bModel->GetCategories();
        if (!Jaws_Error::isError($categories)) {
            $pcats = array();
            foreach ($categories as $cat) {
                $pcats[$cat['id']] = $cat['name'];
            }

            $result[] = array(
                'title' => _t('GLOBAL_CATEGORY'),
                'value' => $pcats
            );

            $result[] = array(
                'title' => _t('GLOBAL_COUNT'),
                'value' => $this->gadget->registry->fetch('last_entries_limit')
            );
        }

        return $result;
    }

    /**
     * Displays the recent posts of a dynamic category
     *
     * @access  public
     * @param   int $cat    Category ID
     * @param   int $limit
     * @return  string  XHTML Template content
     */
    function CategoryEntries($cat = null, $limit = 0)
    {
        $cModel = $this->gadget->model->load('Categories');
        $pModel = $this->gadget->model->load('Posts');
        if (is_null($cat)) {
            $title = _t('BLOG_RECENT_POSTS');
        } else {
            $category = $cModel->GetCategory($cat);
            if (Jaws_Error::isError($category)) {
                return false;
            }
            if (array_key_exists('name', $category)) {
                $cat = $category['id'];
                $title = _t('BLOG_RECENT_POSTS_BY_CATEGORY', $category['name']);
            } else {
                $cat = null;
                $title = _t('BLOG_RECENT_POSTS_BY_CATEGORY');
            }
        }
        $tpl = $this->gadget->template->load('RecentPosts.html');
        $tpl->SetBlock('recent_posts');
        $tpl->SetVariable('cat',   empty($cat)? '0' : $cat);
        $tpl->SetVariable('title', $title);
        $entries = $pModel->GetRecentEntries($cat, (int)$limit);
        if (!Jaws_Error::IsError($entries)) {
            $date = $GLOBALS['app']->loadDate();
            foreach ($entries as $e) {
                $tpl->SetBlock('recent_posts/item');

                $id = empty($e['fast_url']) ? $e['id'] : $e['fast_url'];
                $perm_url = $GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView', array('id' => $id));

                $summary = $e['summary'];
                $text    = $e['text'];

                // for compatibility with old versions
                $more_pos = Jaws_UTF8::strpos($text, '[more]');
                if ($more_pos !== false) {
                    $summary = Jaws_UTF8::substr($text, 0, $more_pos);
                    $text    = Jaws_UTF8::str_replace('[more]', '', $text);

                    // Update this entry to split summary and body of post
                    $pModel->SplitEntry($e['id'], $summary, $text);
                }

                $summary = empty($summary)? $text : $summary;
                $summary = $this->gadget->ParseText($summary);
                $text    = $this->gadget->ParseText($text);

                if (Jaws_UTF8::trim($text) != '') {
                    $tpl->SetBlock('recent_posts/item/read-more');
                    $tpl->SetVariable('url', $perm_url);
                    $tpl->SetVariable('read_more', _t('BLOG_READ_MORE'));
                    $tpl->ParseBlock('recent_posts/item/read-more');
                }

                $tpl->SetVariable('url', $perm_url);
                $tpl->SetVariable('title', $e['title']);
                $tpl->SetVariable('text', $summary);
                $tpl->SetVariable('username', $e['username']);
                $tpl->SetVariable('posted_by', _t('BLOG_POSTED_BY'));
                $tpl->SetVariable('name', $e['nickname']);
                $tpl->SetVariable('author-url', $GLOBALS['app']->Map->GetURLFor('Blog',
                    'ViewAuthorPage',
                    array('id' => $e['username'])));
                $tpl->SetVariable('createtime', $date->Format($e['publishtime']));
                $tpl->SetVariable('createtime-monthname', $date->Format($e['publishtime'], 'MN'));
                $tpl->SetVariable('createtime-month', $date->Format($e['publishtime'], 'm'));
                $tpl->SetVariable('createtime-day', $date->Format($e['publishtime'], 'd'));
                $tpl->SetVariable('createtime-year', $date->Format($e['publishtime'], 'Y'));
                $tpl->SetVariable('createtime-time', $date->Format($e['publishtime'], 'g:ia'));
                $tpl->ParseBlock('recent_posts/item');
            }
        }
        $tpl->ParseBlock('recent_posts');

        return $tpl->Get();
    }

    /**
     * Get popular posts
     *
     * @access  public
     * @return  string  XHTML Template content
     */
    function PopularPosts()
    {
        $tpl = $this->gadget->template->load('PopularPosts.html');
        $tpl->SetBlock('popular_posts');
        $tpl->SetVariable('title', _t('BLOG_POPULAR_POSTS'));

        $model = $this->gadget->model->load('Posts');
        $entries = $model->GetPopularPosts();
        if (!Jaws_Error::IsError($entries)) {
            $date = $GLOBALS['app']->loadDate();
            foreach ($entries as $entry) {
                $tpl->SetBlock('popular_posts/item');

                $tpl->SetVariablesArray($entry);
                $id = empty($entry['fast_url']) ? $entry['id'] : $entry['fast_url'];
                $perm_url = $GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView', array('id' => $id));
                $tpl->SetVariable('url', $perm_url);

                $tpl->SetVariable('posted_by', _t('BLOG_POSTED_BY'));
                $tpl->SetVariable('author-url', $GLOBALS['app']->Map->GetURLFor('Blog',
                    'ViewAuthorPage',
                    array('id' => $entry['username'])));
                $tpl->SetVariable('createtime-iso',       $date->ToISO($entry['publishtime']));
                $tpl->SetVariable('createtime',           $date->Format($entry['publishtime']));
                $tpl->SetVariable('createtime-monthname', $date->Format($entry['publishtime'], 'MN'));
                $tpl->SetVariable('createtime-monthabbr', $date->Format($entry['publishtime'], 'M'));
                $tpl->SetVariable('createtime-month',     $date->Format($entry['publishtime'], 'm'));
                $tpl->SetVariable('createtime-dayname',   $date->Format($entry['publishtime'], 'DN'));
                $tpl->SetVariable('createtime-dayabbr',   $date->Format($entry['publishtime'], 'D'));
                $tpl->SetVariable('createtime-day',       $date->Format($entry['publishtime'], 'd'));
                $tpl->SetVariable('createtime-year',      $date->Format($entry['publishtime'], 'Y'));
                $tpl->SetVariable('createtime-time',      $date->Format($entry['publishtime'], 'g:ia'));
                $tpl->SetVariable('entry-visits',         _t('BLOG_ENTRY_VISITS', $entry['clicks']));

                $tpl->ParseBlock('popular_posts/item');
            }
        }

        $tpl->ParseBlock('popular_posts');
        return $tpl->Get();
    }

    /**
     * Get posts authors
     *
     * @access  public
     * @return  string  XHTML Template content
     */
    function PostsAuthors()
    {
        $tpl = $this->gadget->template->load('Authors.html');
        $tpl->SetBlock('posts_authors');
        $tpl->SetVariable('title', _t('BLOG_POSTS_AUTHORS'));

        $model = $this->gadget->model->load('Posts');
        $authors = $model->GetPostsAuthors();
        if (!Jaws_Error::IsError($entries)) {
            $date = $GLOBALS['app']->loadDate();
            foreach ($authors as $author) {
                $tpl->SetBlock('posts_authors/item');
                $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Blog',
                    'ViewAuthorPage',
                    array('id' => $author['username'])));
                $tpl->SetVariable('title', $author['nickname']);
                $tpl->SetVariable('posts-count', _t('BLOG_AUTHOR_POSTS', $author['howmany']));
                $tpl->ParseBlock('posts_authors/item');
            }
        }

        $tpl->ParseBlock('posts_authors');
        return $tpl->Get();
    }

    /**
     * Displays a list of recent blog entries ordered by date
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function RecentPosts()
    {
        return $this->CategoryEntries();
    }

}