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
class Blog_Actions_Archive extends Blog_Actions_Default
{
    /**
     * Displays a list of blog entries ordered by date
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Archive()
    {
        $tpl = $this->gadget->loadTemplate('Archive.html');
        $model = $this->gadget->loadModel('Posts');
        $archiveEntries = $model->GetEntriesAsArchive();
        $auxMonth = '';
        $this->SetTitle(_t('BLOG_ARCHIVE'));
        $tpl->SetBlock('archive');
        $tpl->SetVariable('title', _t('BLOG_ARCHIVE'));
        if (!Jaws_Error::IsError($archiveEntries)) {
            $date = $GLOBALS['app']->loadDate();
            foreach ($archiveEntries as $entry) {
                $currentMonth = $date->Format($entry['publishtime'], 'MN');
                if ($currentMonth != $auxMonth) {
                    if ($auxMonth != '') {
                        $tpl->ParseBlock('archive/month');
                    }
                    $tpl->SetBlock('archive/month');
                    $year = $date->Format($entry['publishtime'], 'Y');
                    $tpl->SetVariable('month', $currentMonth);
                    $tpl->SetVariable('year', $year);
                    $auxMonth = $currentMonth;
                }
                $tpl->SetBlock('archive/month/record');
                $tpl->SetVariable('id', $entry['id']);
                $tpl->SetVariable('date',           $date->Format($entry['publishtime']));
                $tpl->SetVariable('date-monthname', $currentMonth);
                $tpl->SetVariable('date-month',     $date->Format($entry['publishtime'], 'm'));
                $tpl->SetVariable('date-day',       $date->Format($entry['publishtime'], 'd'));
                $tpl->SetVariable('date-year',      $year);
                $tpl->SetVariable('date-time',      $date->Format($entry['publishtime'], 'g:ia'));
                $tpl->SetVariable('title', $entry['title']);
                if (empty($entry['comments'])) {
                    $tpl->SetVariable('comments', _t('BLOG_NO_COMMENT'));
                } else {
                    $tpl->SetVariable('comments', _t('BLOG_HAS_N_COMMENTS', $entry['comments']));
                }

                $id = !empty($entry['fast_url']) ? $entry['fast_url'] : $entry['id'];
                $url = $GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView', array('id' => $id));
                $tpl->SetVariable('view-link', $url);
                $tpl->ParseBlock('archive/month/record');
            }
            $tpl->ParseBlock('archive/month');
        }
        $tpl->ParseBlock('archive');

        return $tpl->Get('archive');
    }

}