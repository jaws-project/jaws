<?php
/**
 * Blog Gadget
 *
 * @category   GadgetModel
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Model_Posts extends Jaws_Gadget_Model
{

    /**
     * Get entries
     *
     * @access  public
     * @param   int     $cat            Category ID
     * @param   array   $conditions     Array with extra conditions
     * @param   int     $extralimit     Integer which limits number of results
     * @param   int     $extraoffset    Integer which sets an offset to skip results
     * @return  array   Returns an array of entries and Jaws_Error on error
     */
    function GetEntries($cat = null, $conditions = null, $extralimit = null, $extraoffset = null)
    {
        $now = Jaws_DB::getInstance()->date();
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select(
            'blog.id:integer', 'username', 'email', 'nickname', 'blog.title', 'blog.subtitle', 'blog.fast_url',
            'summary', 'text', 'blog.publishtime', 'blog.updatetime', 'clicks:integer',
            'allow_comments:boolean', 'image'
        );
        $blogTable->join('users', 'blog.user_id', 'users.id', 'left');

        if (!is_null($cat)) {
            $blogTable->join('blog_entrycat', 'blog.id', 'blog_entrycat.entry_id', 'left');
            if (empty($cat)) {
                $blogTable->where('blog_entrycat.category_id', null, 'is null');
            } else {
                $blogTable->where('blog_entrycat.category_id', (int)$cat);
            }
        }

        $blogTable->and()->where('published', true)->and()->where('blog.publishtime', $now, '<=');
        if (!is_null($conditions)) {
            foreach ($conditions as $condition) {
                $blogTable->and()->where($condition[0], $condition[1], $condition[2]);
            }
        }

        if (is_null($extralimit)) {
            $extralimit = $this->gadget->registry->fetch('last_entries_limit');
        }
        $result = $blogTable->limit($extralimit, $extraoffset)->orderBy('blog.publishtime desc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('BLOG_ERROR_GETTING_ENTRIES'));
        }

        $entries = array();
        $ids     = array();
        //Only load Jaws_Gravatar if we really have entries
        if (count($result) > 0) {
            $date = Jaws_Date::getInstance();
        }
        foreach ($result as $r) {
            $r['avatar_source'] = Jaws_Gravatar::GetGravatar($r['email']);
            $r['categories']    = array();
            $entries[$r['id']]  = $r;
            $ids[] = $r['id'];
        }
        $result = null;
        $model = $this->gadget->model->load('Categories');
        $categories = $model->GetCategoriesInEntries($ids);
        foreach ($categories as $cat) {
            $entries[$cat['entry_id']]['categories'][] = array(
                'id'       => $cat['id'],
                'name'     => $cat['name'],
                'fast_url' => $cat['fast_url'],
            );
        }
        $categories = null;

        foreach ($entries as $key => $entry) {
            if (empty($entry['categories'])) {
                $entries[$key]['categories'][] = array(
                    'id'       => 0,
                    'name'     => _t('BLOG_UNCATEGORIZED'),
                    'fast_url' => '',
                );
            } else {
                foreach ($entry['categories'] as $cat) {
                    if (!$this->gadget->GetPermission('CategoryAccess', $cat['id'])) {
                        unset($entries[$key]);
                    }
                }
            }
        }

        return $entries;
    }

    /**
     * Get entries in a given page ordered by date (page size = last_entries_limit)
     *
     * @access  public
     * @param   int     $page
     * @param   string  $min_date   minimum date
     * @param   string  $max_date   maximum date
     * @return  array   Returns an array of entries of a certain date and Jaws_Error on error
     */
    function GetEntriesByDate($page, $min_date, $max_date)
    {
        if ($page > 0) {
            $page = $page - 1;
        } else {
            $page = 0;
        }

        $limit = $this->gadget->registry->fetch('last_entries_limit');
        $offset = $limit * $page;

        $whereArray = array(
            array('published', true, '='),
            array('blog.publishtime', $min_date, '>='),
            array('blog.publishtime', $max_date, '<'),
        );

        return $this->GetEntries(null, $whereArray, $limit, $offset);
    }


    /**
     * Get entries as an archive
     *
     * @access  public
     * @return  mixed   Returns a list of entries in Archive Format and Jaws_Error on error
     */
    function GetEntriesAsArchive()
    {
        $now = Jaws_DB::getInstance()->date();
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select('id:integer', 'publishtime', 'updatetime', 'title', 'fast_url', 'categories');
        $blogTable->where('published', true)->and()->where('publishtime', $now, '<=')->orderBy('publishtime desc');
        $result = $blogTable->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('BLOG_ERROR_GETTING_ENTRIES_ASARCHIVE'));
        }

        // Check dynamic ACL
        foreach ($result as $key => $entry) {
            foreach (array_filter(explode(',', $entry['categories'])) as $cat) {
                if (!$this->gadget->GetPermission('CategoryAccess', $cat)) {
                    unset($result[$key]);
                }
            }
        }

        return $result;
    }


    /**
     * Get entries grouped by categories
     *
     * @access  public
     * @return  mixed   Returns a list of entries in Category Format and Jaws_Error on error
     */
    function GetEntriesAsCategories()
    {
        $now = Jaws_DB::getInstance()->date();
        $catTable = Jaws_ORM::getInstance()->table('blog_category');
        $catTable->select(
            'blog_category.id:integer', 'name', 'blog_category.fast_url',
            'count(blog_entrycat.entry_id) as howmany:integer'
        );
        $catTable->join('blog_entrycat', 'blog_category.id', 'blog_entrycat.category_id', 'left');
        $catTable->join('blog', 'blog.id', 'blog_entrycat.entry_id', 'left');
        $catTable->openWhere('published', true)
            ->and()
            ->closeWhere('blog.publishtime', $now, '<=')
            ->or()
            ->where('blog.id', null, 'is null');
        $result = $catTable->groupBy('blog_category.id', 'name', 'blog_category.fast_url')->orderBy('name')->fetchAll();
        if (!Jaws_Error::IsError($result)) {
            // Check dynamic ACL
            foreach ($result as $key => $cat) {
                if (!$this->gadget->GetPermission('CategoryAccess', $cat['id'])) {
                    unset($result[$key]);
                }
            }
        }

        return $result;
    }

    /**
     * Get last entries of all categories or just of only one category
     *
     * @access  public
     * @param   int     $cat    Category ID
     * @return  mixed   Returns a list of recent entries and Jaws_Error on error
     */
    function GetRecentEntries($cat = null, $limit = 0)
    {
        $now = Jaws_DB::getInstance()->date();
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select(
            'blog.id:integer', 'user_id:integer', 'username', 'users.nickname', 'title', 'subtitle', 'summary',
            'text', 'fast_url', 'blog.publishtime', 'blog.updatetime', 'clicks:integer', 'type:integer',
            'favorite:boolean', 'allow_comments:boolean', 'published:boolean', 'categories', 'image'
        );
        $blogTable->join('users', 'blog.user_id', 'users.id', 'left');

        if (is_numeric($cat)) {
            $blogTable->join('blog_entrycat', 'blog.id', 'blog_entrycat.entry_id');
            $blogTable->where('blog_entrycat.category_id', $cat);
        }

        if (empty($limit)) {
            $limit = $this->gadget->registry->fetch('last_entries_limit');
        }

        $blogTable->and()->where('published', true)->and()->where('blog.publishtime', $now, '<=');
        $entries = $blogTable->orderBy('blog.publishtime desc')->limit($limit)->fetchAll();

        // Check dynamic ACL
        foreach ($entries as $key => $entry) {
            foreach (array_filter(explode(',', $entry['categories'])) as $cat) {
                if (!$this->gadget->GetPermission('CategoryAccess', $cat)) {
                    unset($entries[$key]);
                }
            }
        }

        return $entries;
    }

    /**
     * Get last entries of all categories or just of only one type
     *
     * @access  public
     * @param   int     $type    Type ID
     * @param   int     $limit
     * @return  mixed   Returns a list of recent entries and Jaws_Error on error
     */
    function GetRecentEntriesByType($type, $limit = 0)
    {
        $now = Jaws_DB::getInstance()->date();
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select(
            'blog.id:integer', 'user_id:integer', 'username', 'users.nickname', 'title', 'subtitle', 'summary',
            'text', 'fast_url', 'blog.publishtime', 'blog.updatetime', 'clicks:integer', 'type:integer',
            'favorite:boolean', 'allow_comments:boolean', 'published:boolean', 'categories', 'image'
        );
        $blogTable->join('users', 'blog.user_id', 'users.id', 'left');
        $blogTable->where('type', $type);
        if (empty($limit)) {
            $limit = $this->gadget->registry->fetch('last_entries_limit');
        }

        $blogTable->and()->where('published', true)->and()->where('blog.publishtime', $now, '<=');
        $entries = $blogTable->orderBy('blog.publishtime desc')->limit($limit)->fetchAll();

        // Check dynamic ACL
        foreach ($entries as $key => $entry) {
            foreach (array_filter(explode(',', $entry['categories'])) as $cat) {
                if (!$this->gadget->GetPermission('CategoryAccess', $cat)) {
                    unset($entries[$key]);
                }
            }
        }

        return $entries;
    }

    /**
     * Get entries of a category in a given page
     *
     * @access  public
     * @param   int     $category
     * @param   int     $page
     * @return  mixed   Returns an array of entries and Jaws_Error on error
     */
    function GetEntriesByCategory($category, $page)
    {
        if ($page > 0) {
            $page = $page - 1;
        } else {
            $page = 0;
        }

        $limit = $this->gadget->registry->fetch('last_entries_limit');
        $offset = $limit * $page;
        $result = $this->GetEntries($category, null, $limit, $offset);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('BLOG_ERROR_GETTING_ENTRIES_BYCATEGORY'));
        }

        return $result;
    }

    /**
     * Increment visits counter of an entry
     *
     * @access  public
     * @param   int     $id     ID of the Entry
     * @return  bool    True if counter was successfully increment and false on error
     */
    function ViewEntry($id)
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $result = $blogTable->update(
            array(
                'clicks' => $blogTable->expr('clicks + ?', 1)
            )
        )->where('id', $id)->exec();

        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return true;
    }

    /**
     * Get an entry
     *
     * @access  public
     * @param   int     $id         ID of the Entry
     * @param   bool    $published  If it is true then get the entry only if it is published
     * @return  mixed   Properties of the entry(an array) and Jaws_Error on error
     */
    function GetEntry($id, $published = false)
    {
        // super admins can get/show drafted entries
        $published = (bool)$published && !$this->app->session->user->superadmin;
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select(
            'blog.id:integer', 'blog.user_id:integer', 'username', 'email', 'nickname', 'blog.title', 'subtitle',
            'summary', 'text', 'fast_url', 'meta_keywords', 'meta_description', 'trackbacks', 'published:boolean',
            'image', 'blog.publishtime', 'blog.updatetime', 'clicks:integer', 'allow_comments:boolean',
            'favorite:boolean', 'type:integer'
        )->join('users', 'blog.user_id', 'users.id', 'left');

        if (is_numeric($id)) {
            $blogTable->where('blog.id', $id);
        } else {
            $blogTable->where('blog.fast_url', $id);
        }

        if ($published) {
            // entry's author can get/show drafted entries
            $now = Jaws_DB::getInstance()->date();
            $user = (int)$this->app->session->user->id;
            $blogTable->and()->openWhere('blog.user_id', $user)->or();
            $blogTable->openWhere('published', $published)->and()->closewhere('blog.publishtime', $now, '<=');
            $blogTable->closeWhere();
        }
        $row = $blogTable->fetchRow();
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error(_t('BLOG_ERROR_GETTING_ENTRY'));
        }

        $entry = array();
        if (!empty($row)) {
            $model = $this->gadget->model->load('Categories');
            $entry = $row;
            $entry['avatar_source'] = Jaws_Gravatar::GetGravatar($row['email']);
            $entry['categories']    = $model->GetCategoriesInEntry($row['id']);
            if (empty($entry['categories'])) {
                $entry['categories'][] = array(
                    'id'       => 0,
                    'name'     => _t('BLOG_UNCATEGORIZED'),
                    'fast_url' => '',
                );
            }

            $entry['type'] = Jaws_Gadget::getInstance('Categories')->model->load('Categories')->GetCategory($row['type']);

            $entry['tags'] = array();
            if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
                $model = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
                $tags = $model->GetReferenceTags('Blog', 'post', $row['id']);
                $entry['tags'] = array_filter($tags);
            }
        }

        return $entry;
    }

    /**
     * Get latest published entry ID
     *
     * @access  public
     * @return  mixed   ID of the latest published entry and false on error
     */
    function GetLatestPublishedEntryID()
    {
        $now = Jaws_DB::getInstance()->date();
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select('id:integer')->where('published', true)->and()->where('publishtime', $now, '<=');
        $result = $blogTable->orderBy('publishtime desc')->limit(1)->fetchOne();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return $result;
    }

    /**
     * Get last entries
     *
     * @access  public
     * @param   int     $limit
     * @return  mixed   An array of the last entries and Jaws_Error on error
     */
    function GetLastEntries($limit)
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select(
            'blog.id:integer', 'username', 'email', 'nickname', 'blog.title', 'blog.subtitle', 'blog.fast_url',
            'summary', 'text', 'users.nickname as name', 'blog.publishtime', 'blog.updatetime', 'clicks:integer',
            'allow_comments:boolean', 'blog.user_id:integer'
        )->join('users', 'blog.user_id', 'users.id', 'left');

        $result = $blogTable->orderBy('blog.publishtime desc')->limit($limit)->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('BLOG_ERROR_GETTING_LAST_ENTRIES'));
        }

        foreach ($result as $key => $value) {
            $result[$key]['avatar_source'] = Jaws_Gravatar::GetGravatar($value['email']);
        }

        return $result;
    }

    /**
     * Verify if an entry exists
     *
     * @access  public
     * @param   int     $post_id    The entry ID (ID or fast_URL, string)
     * @return  bool    True if entry exists, else, false.
     */
    function DoesEntryExists($post_id)
    {
        $column =  is_numeric($post_id) ? 'id' : 'fast_url';
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $count = $blogTable->select('count(id)')->where($column, $post_id)->fetchOne();
        if (Jaws_Error::IsError($count)) {
            return false;
        }

        if ($count > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get next/previous published entry
     * NOP = next or previous
     *
     * @access  public
     * @param   int     $id         ID of the Entry
     * @param   string  $direction  OPTIONAL direction
     * @return  bool    Properties of the entry(an array) and false on error
     */
    function GetNOPEntry($id, $direction = 'next')
    {
        $options = array(
            'next' => array(
                'sign' => '>',
                'direction' => 'asc',
            ),
            'previous' => array(
                'sign' => '<',
                'direction' => 'desc',
            )
        );

        if (!array_key_exists($direction, $options)) {
            $option = $options['next'];
        } else {
            $option = $options[$direction];
        }

        $now = Jaws_DB::getInstance()->date();
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select('id:integer', 'title', 'fast_url');
        $blogTable->where('id', $id, $option['sign'])->and()->where('published', true);
        $blogTable->and()->where('publishtime', $now, '<=');
        $row = $blogTable->orderBy('id ' . $option['direction'])->limit(1)->fetchRow();
        if (Jaws_Error::IsError($row)) {
            return false;
        }

        return $row;
    }

    /**
     * Get the fast url
     *
     * @access  public
     * @param   string  $fasturl    The fastURL of entry
     * @return  mixed   An array contains entry info and false otherwise
     */
    function GetFastURL($fasturl)
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $result = $blogTable->select('id:integer', 'title', 'fast_url')->where('fast_url', $fasturl)->fetchRow();

        if (Jaws_Error::IsError($result)) {
            return false;
        }

        return $result;
    }

    /**
     * Get entries in a given page (page size = last_entries_limit)
     *
     * @access  public
     * @param   int     $cat            category
     * @param   int     $page           page
     * @param   array   $condition      conditions array
     * @return  array  An array with the entries
     */
    function GetEntriesAsPage($cat = null, $page = 0, $condition = null)
    {
        if ($page > 0) {
            $page = $page - 1;
        } else {
            $page = 0;
        }

        $limit = $this->gadget->registry->fetch('last_entries_limit');
        $offset = $limit * $page;

        $res = $this->GetEntries($cat, $condition, $limit, $offset);

        return $res;
    }

    /**
     * Get number of pages limited by last_entries_limit
     *
     * @access  public
     * @param   int $category   category iD
     * @return  int number of pages
     */
    function GetNumberOfPages($category = null)
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select('count(blog.id)');
        if (!empty($category)) {
            $blogTable->join('blog_entrycat', 'blog.id', 'blog_entrycat.entry_id', 'left');
            $blogTable->where('blog_entrycat.category_id', (int)$category)->and();
        }

        $blogTable->where('published', true)->and()->where('publishtime', Jaws_DB::getInstance()->date(), '<=');
        $howmany = $blogTable->fetchOne();
        return Jaws_Error::IsError($howmany)? 0 : $howmany;
    }

    /**
     * Get popular posts
     *
     * @access  public
     * @param   int     $from       From time(0: all time, 1: today)
     * @param   int     $limit      Count of posts to be returned
     * @param   int     $offset     Offset of data array
     * @return  mixed   List of popular posts or Jaws_Error on error
     */
    function GetPopularPosts($from = 0, $limit = 0, $offset = null)
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select(
            'blog.id:integer', 'blog.user_id:integer', 'blog.title', 'blog.subtitle', 'blog.fast_url', 'summary',
            'text', 'clicks:integer', 'allow_comments', 'username', 'nickname',
            'blog.publishtime:timestamp', 'blog.updatetime:timestamp', 'categories', 'image'
        );
        $blogTable->join('users', 'blog.user_id', 'users.id', 'left');
        $blogTable->where('published', true)->and()->where('publishtime', Jaws_DB::getInstance()->date(), '<=');
        // from today
        if ($from == 1) {
            $today = $this->app->UserTime2UTC(
                $this->app->UTC2UserTime(time(), 'Y-m-d 00:00:00'),
                'Y-m-d H:i:s'
            );
            $blogTable->and()->where('publishtime', $today, '>=');
        }
        $entries = $blogTable->limit((int)$limit, $offset)->orderBy('clicks desc')->fetchAll();

        // Check dynamic ACL
        foreach ($entries as $key => $entry) {
            foreach (array_filter(explode(',', $entry['categories'])) as $cat) {
                if (!$this->gadget->GetPermission('CategoryAccess', $cat)) {
                    unset($entries[$key]);
                }
            }
        }

        return $entries;
    }

    /**
     * Get popular posts count
     *
     * @access  public
     * @param   int     $from       From time(0: all time, 1: today)
     * @return  mixed   List of popular posts or Jaws_Error on error
     */
    function GetPopularPostsCount($from = 0)
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select('categories');
        $blogTable->where('published', true)->and()->where('publishtime', Jaws_DB::getInstance()->date(), '<=');
        // from today
        if ($from == 1) {
            $today = $this->app->UserTime2UTC(
                $this->app->UTC2UserTime(time(), 'Y-m-d 00:00:00'),
                'Y-m-d H:i:s'
            );
            $blogTable->and()->where('publishtime', $today, '>=');
        }
        $entries = $blogTable->fetchColumn();

        // Check dynamic ACL
        foreach ($entries as $key => $categories) {
            foreach (array_filter(explode(',', $categories)) as $cat) {
                if (!$this->gadget->GetPermission('CategoryAccess', $cat)) {
                    unset($entries[$key]);
                }
            }
        }

        return count($entries);
    }

    /**
     * Get favorite posts
     *
     * @access  public
     * @return  mixed   List of popular posts or Jaws_Error on error
     */
    function GetFavoritePosts($limit = 0, $offset = null)
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->limit($this->gadget->registry->fetch('favorite_limit'), 0);
        $blogTable->select(
            'blog.id:integer', 'blog.user_id:integer', 'blog.title', 'blog.subtitle', 'blog.fast_url', 'summary',
            'text', 'clicks:integer', 'allow_comments', 'username', 'nickname', 'type:integer',
            'blog.publishtime:timestamp', 'blog.updatetime:timestamp', 'categories', 'image'
        );
        $blogTable->join('users', 'blog.user_id', 'users.id', 'left');
        $blogTable->where('published', true)->and()->where('publishtime', Jaws_DB::getInstance()->date(), '<=');
        $blogTable->and()->where('favorite', true);
        $entries = $blogTable->limit((int)$limit, $offset)->orderBy('blog.createtime desc')->fetchAll();

        // Check dynamic ACL
        foreach ($entries as $key => $entry) {
            foreach (array_filter(explode(',', $entry['categories'])) as $cat) {
                if (!$this->gadget->GetPermission('CategoryAccess', $cat)) {
                    unset($entries[$key]);
                }
            }
        }

        return $entries;
    }

    /**
     * Get posts authors
     *
     * @access  public
     * @return  mixed   List of posts authors or Jaws_Error on error
     */
    function GetPostsAuthors()
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->limit($this->gadget->registry->fetch('popular_limit'), 0);
        $blogTable->select('user_id', 'username', 'nickname', 'count(blog.id) as howmany');
        $blogTable->join('users', 'blog.user_id', 'users.id', 'left');
        $blogTable->groupBy('user_id', 'username', 'nickname');
        $blogTable->where('published', true)->and()->where('publishtime', Jaws_DB::getInstance()->date(), '<=');
        $blogTable->orderBy('user_id');
        return $blogTable->fetchAll();
    }

    /**
     * Temporary function for updating split summary and body of entry
     *
     * @access  public
     * @param   int     $id         ID of the Entry
     * @param   string  $summary    Summary of the entry
     * @param   string  $text       Main text of the entry
     * @return  bool    True if counter was successfully increment and false on error
     */
    function SplitEntry($id, &$summary, &$text)
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->update(array('summary' => $summary, 'text' => $text));
        return $blogTable->where('id', $id)->exec();
    }

    /**
     * Get entries that match parameters
     *
     * @access  public
     * @param   int     $limit    Limit of data
     * @param   string  $category Category id
     * @param   string  $status   Status of the entry, 0 = Draft, 1 = Published
     * @param   string  $match    Match word
     * @param   string  $user_id  User id
     * @return  mixed   An array of entries and Jaws_Error on error
     */
    function AdvancedSearch($limit, $category, $status, $match, $user_id)
    {
        // Removed until ACLs are in place.
        /*$sql = 'SELECT [[blog]].[id], [user_id], [username], [nickname],
                [category_id], [title], [publishtime], [published]
            FROM [[blog]] INNER JOIN [[users]]
            ON [[blog]].[user_id] = [[users]].[id] ';*/

        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select(
            'blog.id:integer', 'blog.user_id:integer', 'username', 'title', 'subtitle', 'summary', 'text',
            'fast_url', 'blog.publishtime', 'blog.updatetime', 'published:boolean', 'categories'
        )->join('users', 'blog.user_id', 'users.id');

        if (trim($category) != '') {
            $blogTable->join('blog_entrycat', 'blog.id', 'blog_entrycat.entry_id');
        }
        if (trim($match) != '') {
            $searchdata = array_filter(array_map('trim', explode(' ', $match)));
            /**
             * This query needs more work, not use $v straight, should be
             * like rest of the param stuff.
             */
            foreach ($searchdata as $str) {
                $blogTable->and()
                    ->openWhere()
                    ->where('blog.title', $str, 'like')
                    ->or()
                    ->where('blog.subtitle', $str, 'like')
                    ->or()
                    ->where('summary', $str, 'like')
                    ->or()
                    ->where('text', $str, 'like')
                    ->closeWhere();
            }
        }

        if (trim($status) != '') {
            $blogTable->and()->where('published', (bool)$status);
        }

        if (trim($category) != '') {
            $blogTable->and()->where('blog_entrycat.category_id', $category);
        }

        if (is_numeric($limit)) {
            $blogTable->limit(10, $limit);
        }

        if (!$this->gadget->GetPermission('ModifyOthersEntries')) {
            if (trim($user_id) != '') {
                $blogTable->and()->where('user_id', $user_id);
            }
        }
        $result = $blogTable->orderBy('blog.publishtime desc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('BLOG_ERROR_ADVANCED_SEARCH'));
        }

        // Check dynamic ACL
        foreach ($result as $key => $entry) {
            foreach (array_filter(explode(',', $entry['categories'])) as $cat) {
                if (!$this->gadget->GetPermission('CategoryManage', $cat)) {
                    unset($result[$key]);
                }
            }
        }
        return $result;
    }

    /**
     * Get posts that match filters
     *
     * @access  public
     * @param   array   $filters    Filters for limiting posts
                (category, user, published, start_time, stop_time, offset, limit)
     * @return  mixed   An array of posts or Jaws_Error on failure
     */
    function GetPosts($filters = array())
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select(
            'id:integer', 'title', 'subtitle', 'fast_url', 'summary', 'text',
            'publishtime', 'updatetime', 'clicks:integer',
            'allow_comments:boolean', 'user_id', 'categories', 'published'
        );

        // published filter
        if (isset($filters['published']) && !empty($filters['published'])) {
            $blogTable->and()->where('published', (bool)$filters['published']);
        }
        // category filter
        if (isset($filters['category']) && !empty($filters['category'])) {
            $blogTable->and()->where('categories', ",{$filters['category']},", 'like');
        }
        // user filter
        if (isset($filters['user']) && !empty($filters['user'])) {
            $blogTable->and()->where('user_id', (int)$filters['user']);
        }
        // start time filter
        if (isset($filters['start_time']) && !empty($filters['start_time'])) {
            $blogTable->and()->where('publishtime', $filters['start_time'], '>=');
        }
        // stop time filter
        if (isset($filters['stop_time']) && !empty($filters['stop_time'])) {
            $blogTable->and()->where('publishtime', $filters['stop_time'], '<');
        }
        // limit, offset
        $blogTable->limit(@$filters['limit'], @$filters['offset']);
        $result = $blogTable->orderBy('publishtime desc')->fetchAll();
        // Check dynamic ACL
        foreach ($result as $key => $post) {
            foreach (array_filter(explode(',', $post['categories'])) as $cat) {
                if (!$this->gadget->GetPermission('CategoryManage', $cat)) {
                    unset($result[$key]);
                }
            }
        }

        return $result;
    }

}