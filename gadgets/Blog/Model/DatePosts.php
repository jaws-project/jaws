<?php
/**
 * Blog Gadget
 *
 * @category   GadgetModel
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Model_DatePosts extends Jaws_Gadget_Model
{
    /**
     * Get date limitation of the blog entries
     *
     * @access  public
     * @param   bool    $published      is published
     * @return  array   An array that has the date limitation of blog entries
     */
    function GetPostsDateLimitation($published = null)
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select('min(publishtime) as min_date', 'max(publishtime) as max_date', 'count(id) as qty_posts');

        if (!is_null($published)) {
            $blogTable->where('published', $published);
        }

        $summary = $blogTable->fetchRow();
        if (Jaws_Error::IsError($summary)) {
            $summary = array();
        }

        return $summary;
    }

    /**
     * Get number of date's pages
     *
     * @access  public
     * @param   string  $min_date   minimum date
     * @param   string  $max_date   maximum date
     * @return  int number of pages
     */
    function GetDateNumberOfPages($min_date, $max_date)
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select('count(blog.id)');
        $blogTable->where('published', true)->and();
        $blogTable->where('publishtime', $min_date, '>=')->and()->where('publishtime', $max_date, '<');
        $howmany = $blogTable->fetchOne();
        return Jaws_Error::IsError($howmany)? 0 : $howmany;
    }

    /**
     * Get entries as a calendar
     *
     * @access  public
     * @param   string  $begintime  Begin date time
     * @param   string  $endtime    End date time
     * @return  mixed   An array of entries of a certain year and month and Jaws_Error on error
     */
    function GetEntriesAsCalendar($begintime, $endtime)
    {
        $now = $GLOBALS['db']->Date();
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select('title', 'fast_url', 'publishtime');
        $blogTable->where('published', true)->and()->where('publishtime', $begintime, '>=')->and();
        $blogTable->where('publishtime', $endtime, '<')->and()->where('publishtime', $now, '<=');
        $result = $blogTable->orderBy('publishtime asc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('BLOG_ERROR_GETTING_ENTRIES_ASCALENDAR'));
        }

        return $result;
    }

    /**
     * Get an month/year where exists entries
     *
     * @access  public
     * @return  mixed   An array of relations between months and years of the blog and Jaws_Error on error
     */
    function GetMonthsEntries()
    {
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select(
            $blogTable->substring('blog.publishtime', 6, 2)->alias('month'),
            $blogTable->substring('blog.publishtime', 1, 4)->alias('year')
        )->groupBy(
                $blogTable->substring('blog.publishtime', 6, 2),
                $blogTable->substring('blog.publishtime', 1, 4),
                'publishtime'
            );

        $result = $blogTable->orderBy('publishtime desc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('BLOG_ERROR_GETTING_MONTH_ENTRIES'));
        }

        return $result;
    }

    /**
     * Get entries as a history
     *
     * @access  public
     * @return  mixed   Returns a list of entries in History Format and Jaws_Error on error
     */
    function GetEntriesAsHistory()
    {
        $now = $GLOBALS['db']->Date();
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select('publishtime')->where('published', true)->and()->where('publishtime', $now, '<=');
        $result = $blogTable->orderBy('publishtime desc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('BLOG_ERROR_GETTING_ENTRIES_ASHISTORY'));
        }

        return $result;
    }

}