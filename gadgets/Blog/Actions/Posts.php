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

        $GLOBALS['app']->Layout->addLink(
            array(
                'href'  => $this->gadget->urlMap('Atom'),
                'type'  => 'application/atom+xml',
                'rel'   => 'alternate',
                'title' => 'Atom - All'
            )
        );
        $GLOBALS['app']->Layout->addLink(
            array(
                'href'  => $this->gadget->urlMap('RSS'),
                'type'  => 'application/rss+xml',
                'rel'   => 'alternate',
                'title' => 'RSS 2.0 - All'
            )
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

        $this->setTitle(_t('BLOG_RECENT_POSTS'));

        $tpl = $this->gadget->template->load('Posts.html');
        $tpl->SetBlock('view');

        $model = $this->gadget->model->load('Posts');
        $entries = $model->GetEntriesAsPage($cat, $page);
        if (!Jaws_Error::IsError($entries) && count($entries) > 0) {
            $row = 0;
            $col = 0;
            $index = 0;
            $tpl->SetVariable('title', _t('BLOG_RECENT_POSTS'));
            $columns = (int) $this->gadget->registry->fetch('columns');
            $columns = ($columns <= 0)? 1 : $columns;
            foreach ($entries as $entry) {
                if ($col == 0) {
                    $tpl->SetBlock('view/entryrow');
                    $tpl->SetVariable('row', $row);
                }

                $tpl->SetBlock('view/entryrow/column');
                $tpl->SetVariable('col', $col);
                $this->ShowEntry($tpl, 'view/entryrow/column', $entry);
                $tpl->ParseBlock('view/entryrow/column');

                $index++;
                $col = $index % $columns;
                if ($col == 0 || $index == count($entries)) {
                    $row++;
                    $tpl->ParseBlock('view/entryrow');
                }
            }
        }

        $total = $model->GetNumberOfPages($cat);
        $limit = $this->gadget->registry->fetch('last_entries_limit');
        $this->gadget->action->load('Navigation')->pagination($tpl, $page, $limit, $total, 'ViewPage');
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
        $entries = $pModel->GetRecentEntries($cat, (int)$limit);
        if (Jaws_Error::IsError($entries) || empty($entries)) {
            return false;
        }

        $tpl = $this->gadget->template->load(empty($cat)? 'RecentPosts.html' : 'RecentCategoryPosts.html');
        $tpl->SetBlock('recent_posts');
        $tpl->SetVariable('cat',   empty($cat)? '0' : $cat);
        $tpl->SetVariable('title', $title);
        $date = Jaws_Date::getInstance();
        foreach ($entries as $e) {
            $tpl->SetBlock('recent_posts/item');

            $id = empty($e['fast_url']) ? $e['id'] : $e['fast_url'];
            $perm_url = $this->gadget->urlMap('SingleView', array('id' => $id));

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
            $summary = $this->gadget->plugin->parseAdmin($summary);
            $text    = $this->gadget->plugin->parseAdmin($text);

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
            $tpl->SetVariable(
                'author-url',
                $this->gadget->urlMap('ViewAuthorPage', array('id' => $e['username']))
            );
            $tpl->SetVariable('createtime', $date->Format($e['publishtime']));
            $tpl->SetVariable('createtime-monthname', $date->Format($e['publishtime'], 'MN'));
            $tpl->SetVariable('createtime-month', $date->Format($e['publishtime'], 'm'));
            $tpl->SetVariable('createtime-day', $date->Format($e['publishtime'], 'd'));
            $tpl->SetVariable('createtime-year', $date->Format($e['publishtime'], 'Y'));
            $tpl->SetVariable('createtime-time', $date->Format($e['publishtime'], 'g:ia'));

            if(empty($e['image'])) {
                $tpl->SetVariable('image', _t('GLOBAL_NOIMAGE'));
                $tpl->SetVariable('url_image', 'data:image/png;base64,');
            } else {
                $tpl->SetVariable('image', $e['image']);
                $tpl->SetVariable('url_image', $GLOBALS['app']->getDataURL(). 'blog/images/'. $e['image']);
            }

            $tpl->ParseBlock('recent_posts/item');
        }

        $tpl->ParseBlock('recent_posts');
        return $tpl->Get();
    }

    /**
     * Get FavoritePosts action params
     *
     * @access  private
     * @return  array    list of FavoritePosts action params
     */
    function FavoritePostsLayoutParams()
    {
        $result = array();
        $result[] = array(
            'title' => _t('GLOBAL_COUNT'),
            'value' => 5
        );

        return $result;
    }

    /**
     * Get favorite posts
     *
     * @access  public
     * @param   int     $limit
     * @return  string  XHTML Template content
     */
    function FavoritePosts($limit = 0)
    {
        $tpl = $this->gadget->template->load('FavoritePosts.html');
        $tpl->SetBlock('favorite_posts');
        $tpl->SetVariable('title', _t('BLOG_FAVORITE_POSTS'));

        $model = $this->gadget->model->load('Posts');
        $entries = $model->GetFavoritePosts((int)$limit);
        if (!Jaws_Error::IsError($entries)) {
            $date = Jaws_Date::getInstance();
            foreach ($entries as $index => $entry) {
                $tpl->SetBlock('favorite_posts/item');

                $tpl->SetVariablesArray($entry);
                $id = empty($entry['fast_url']) ? $entry['id'] : $entry['fast_url'];
                $perm_url = $this->gadget->urlMap('SingleView', array('id' => $id));
                $tpl->SetVariable('url', $perm_url);

                $tpl->SetVariable('posted_by', _t('BLOG_POSTED_BY'));
                $tpl->SetVariable(
                    'author-url',
                    $this->gadget->urlMap('ViewAuthorPage', array('id' => $entry['username']))
                );
                $tpl->SetVariable('post_id', $entry['id']);
                $tpl->SetVariable('post_index', $index);
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

                if(empty($entry['image'])) {
                    $tpl->SetVariable('image', _t('GLOBAL_NOIMAGE'));
                    $tpl->SetVariable('url_image', 'data:image/png;base64,');
                } else {
                    $tpl->SetVariable('image', $entry['image']);
                    $tpl->SetVariable('url_image', $GLOBALS['app']->getDataURL(). 'blog/images/'. $entry['image']);
                }

                $tpl->ParseBlock('favorite_posts/item');
            }
        }

        $tpl->ParseBlock('favorite_posts');
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
            $date = Jaws_Date::getInstance();
            foreach ($authors as $author) {
                $tpl->SetBlock('posts_authors/item');
                $tpl->SetVariable(
                    'url',
                    $this->gadget->urlMap('ViewAuthorPage', array('id' => $author['username']))
                );
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