<?php
/**
 * Blog Layout HTML file (for layout purposes)
 *
 * @category   GadgetLayout
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_LayoutHTML extends Jaws_Gadget_HTML
{
    /**
     * Get CategoryEntries action params
     *
     * @access  private
     * @return  array    list of CategoryEntries action params
     */
    function CategoryEntriesLayoutParams()
    {
        $result = array();
        $bModel = $GLOBALS['app']->LoadGadget('Blog', 'Model');
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
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        if (is_null($cat)) {
            $title = _t('BLOG_RECENT_POSTS');
        } else {
            $category = $model->GetCategory($cat);
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

        $tpl = $this->gadget->loadTemplate('RecentPosts.html');
        $tpl->SetBlock('recent_posts');
        $tpl->SetVariable('cat',   empty($cat)? '0' : $cat);
        $tpl->SetVariable('title', $title);
        $entries = $model->GetRecentEntries($cat, (int)$limit);
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
                    $model->SplitEntry($e['id'], $summary, $text);
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
     * Displays a list of blog entries ordered by date and grouped by month
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function MonthlyHistory()
    {
        $tpl = $this->gadget->loadTemplate('MonthlyHistory.html');
        $tpl->SetBlock('monthly_history');
        $tpl->SetVariable('title', _t('BLOG_ARCHIVE'));
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        $entries = $model->GetEntriesAsHistory();
        if (!Jaws_Error::IsError($entries)) {
            $aux_mon_year = '';
            $date = $GLOBALS['app']->loadDate();
            foreach ($entries as $key => $entry) {
                $mon_year = $date->Format($entry['publishtime'], 'm,Y');
                if ($mon_year != $aux_mon_year) {
                    if (!empty($aux_mon_year)) {
                        $tpl->SetBlock('monthly_history/item');
                        $tpl->SetVariable('url',
                                          $GLOBALS['app']->Map->GetURLFor('Blog',
                                                                          'ViewDatePage',
                                                                          array('year'  => $year,
                                                                                'month' => $month)));
                        $tpl->SetVariable('month', $date->MonthString($month) );
                        $tpl->SetVariable('year', $year);
                        $tpl->SetVariable('howmany', $howmany);
                        $tpl->ParseBlock('monthly_history/item');
                    }
                    $aux_mon_year = $mon_year;
                    $year  = substr(strstr($mon_year, ','), 1);
                    $month = substr($mon_year, 0, strpos($mon_year, ','));
                    $howmany = 0;
                }
                $howmany++;

                if ($key == (count($entries) - 1)) {
                    $tpl->SetBlock('monthly_history/item');
                    $tpl->SetVariable('url',
                                      $GLOBALS['app']->Map->GetURLFor('Blog',
                                                                      'ViewDatePage',
                                                                      array('year'  => $year,
                                                                            'month' => $month)));
                    $tpl->SetVariable('month', $date->MonthString($month) );
                    $tpl->SetVariable('year', $year);
                    $tpl->SetVariable('howmany', $howmany);
                    $tpl->ParseBlock('monthly_history/item');
                }
            }
        }
        $tpl->ParseBlock('monthly_history');

        return $tpl->Get('archive');
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
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        $entries = $model->GetEntriesAsCategories();
        if (!Jaws_Error::IsError($entries)) {
            foreach ($entries as $e) {
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

    /**
     * Displays a calendar of the current month/year
     *
     * @access  public
     * @return  bool    True on successful installation, False otherwise
     */
    function Calendar()
    {
        require_once JAWS_PATH.'include/Jaws/Calendar.php';
        $cal = new Jaws_Calendar('gadgets/Blog/templates/');

        //By default.
        $objDate = $GLOBALS['app']->loadDate();
        $dt = explode('-', $objDate->Format(time(), 'Y-m-d'));
        $year  = $dt[0];
        $month = $dt[1];
        $day   = $dt[2];

        $request =& Jaws_Request::getInstance();
        $get = $request->get(array('gadget', 'action', 'year', 'month', 'day'), 'get');

        // If we are showing a specific month then show calendar of such month
        if (!is_null($get['gadget']) && !is_null($get['action']) && !is_null($get['month'])) {
            if (
                ($get['gadget'] == 'Blog') &&
                ($get['action'] == 'ViewDatePage') &&
                (trim($get['month']) != '')
            ) {
                $year  = $get['year'];
                $month = !is_null($get['month']) ? $get['month'] : '';
                $day   = !is_null($get['day'])   ? $get['day']   : '';
            }
        }

        $cal->Year  = $year;
        $cal->Month = $month;
        $cal->Day   = $day;

        if ($month == '1') {
            $lyear  = $year - 1;
            $lmonth = '12';
        } else {
            $lyear  = $year;
            $lmonth = $month - 1;
        }
        if ($lmonth < 10) {
            $lmonth = '0' . $lmonth;
        }
        $url = $GLOBALS['app']->Map->GetURLFor('Blog', 'ViewDatePage',
                                               array('year'  => $lyear,
                                                     'month' => $lmonth,
                                                     ));
        $date = $GLOBALS['app']->loadDate();
        $cal->addArrow('left', '&laquo;' . $date->MonthString($lmonth), $url);

        if ($month == '12') {
            $ryear  = $year + 1;
            $rmonth = '1';
        } else {
            $ryear  = $year;
            $rmonth = $month + 1;
        }
        if ($rmonth < 10) {
            $rmonth = '0' . $rmonth;
        }
        $url = $GLOBALS['app']->Map->GetURLFor('Blog', 'ViewDatePage',
                                               array('year'  => $ryear,
                                                     'month' => $rmonth,
                                                     ));
        $cal->addArrow('right', $date->MonthString($rmonth) . '&raquo;', $url);

        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        $bgnDate = $objDate->ToBaseDate($year, $month, 1, 0, 0, 0, 'Y-m-d H:i:s');
        $endDate = $objDate->ToBaseDate($year, $month + 1, 1, 0, 0, 0, 'Y-m-d H:i:s');
        $entries = $model->GetEntriesAsCalendar($bgnDate, $endDate);
        if (!Jaws_Error::IsError($entries)) {
            foreach ($entries as $e) {
                $edt = explode('-', $objDate->Format($e['publishtime'], 'Y-m-d'));
                $cal->AddItem($edt[0], $edt[1], $edt[2],
                              $GLOBALS['app']->Map->GetURLFor('Blog', 'ViewDatePage',
                                                              array('year'  => $edt[0],
                                                                    'month' => $edt[1],
                                                                    'day'   => $edt[2],
                                                                    )),
                              $e['title']);
            }
        }

        return $cal->ShowMonth($cal->Month, $cal->Year);
    }

    /**
     * Display a tag cloud
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ShowTagCloud()
    {
        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        $res = $model->CreateTagCloud();
        $sortedTags = $res;
        sort($sortedTags);
        $minTagCount = log((isset($sortedTags[0]) ? $sortedTags[0]['howmany'] : 0));
        $maxTagCount = log(((count($res) != 0)? $sortedTags[count($res) - 1]['howmany'] : 0));
        unset($sortedTags);
        if ($minTagCount == $maxTagCount) {
            $tagCountRange = 1;
        } else {
            $tagCountRange = $maxTagCount - $minTagCount;
        }
        $minFontSize = 1;
        $maxFontSize = 10;
        $fontSizeRange = $maxFontSize - $minFontSize;

        $tpl = $this->gadget->loadTemplate('CategoryCloud.html');
        $tpl->SetBlock('tagcloud');
        $tpl->SetVariable('title', _t('BLOG_TAGCLOUD'));

        foreach ($res as $key => $value) {
            $count  = $value['howmany'];
            $fsize = $minFontSize + $fontSizeRange * (log($count) - $minTagCount)/$tagCountRange;
            $tpl->SetBlock('tagcloud/tag');
            $tpl->SetVariable('size', (int)$fsize);
            $tpl->SetVariable('tagname',  Jaws_UTF8::strtolower($value['name']));
            $tpl->SetVariable('frequency', $value['howmany']);
            $cid = empty($value['fast_url']) ? $value['category_id'] : $value['fast_url'];
            $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Blog', 'ShowCategory', array('id' => $cid)));
            $tpl->SetVariable('category', $value['category_id']);
            $tpl->ParseBlock('tagcloud/tag');
        }
        $tpl->ParseBlock('tagcloud');

        return $tpl->Get();
    }

    /**
     * Displays a link to blog RSS feed
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function RSSLink()
    {
        $tpl = $this->gadget->loadTemplate('XMLLinks.html');
        $tpl->SetBlock('rss_link');
        $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Blog', 'RSS'));
        $tpl->ParseBlock('rss_link');
        return $tpl->Get();
    }

    /**
     * Displays a link to blog Atom feed
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function AtomLink()
    {
        $tpl = $this->gadget->loadTemplate('XMLLinks.html');
        $tpl->SetBlock('atom_link');
        $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Blog', 'Atom'));
        $tpl->ParseBlock('atom_link');
        return $tpl->Get();
    }

    /**
     * Displays a link to Atom feed for blog most recent comments
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function RecentCommentsAtomLink()
    {
        $tpl = $this->gadget->loadTemplate('XMLLinks.html');
        $tpl->SetBlock('recentcomments_atom_link');
        $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Blog', 'RecentCommentsAtom'));
        $tpl->ParseBlock('recentcomments_atom_link');
        return $tpl->Get();
    }

    /**
     * Displays a link to RSS feed for blog most recent comments
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function RecentCommentsRSSLink()
    {
        $tpl = $this->gadget->loadTemplate('XMLLinks.html');
        $tpl->SetBlock('recentcomments_rss_link');
        $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Blog', 'RecentCommentsRSS'));
        $tpl->ParseBlock('recentcomments_rss_link');
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
        $tpl = $this->gadget->loadTemplate('PopularPosts.html');
        $tpl->SetBlock('popular_posts');
        $tpl->SetVariable('title', _t('BLOG_POPULAR_POSTS'));

        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
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
        $tpl = $this->gadget->loadTemplate('Authors.html');
        $tpl->SetBlock('posts_authors');
        $tpl->SetVariable('title', _t('BLOG_POSTS_AUTHORS'));

        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
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

}
