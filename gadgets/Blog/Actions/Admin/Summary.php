<?php
/**
 * Blog Admin HTML file
 *
 * @category   GadgetAdmin
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Admin_Summary extends Blog_Actions_Admin_Default
{
    /**
     * Displays blog summary with some statistics
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function Summary()
    {
        $model = $this->gadget->model->load('Summary');
        $summary = $model->GetSummary();
        if (Jaws_Error::IsError($summary)) {
            $summary = array();
        }

        $tpl = $this->gadget->loadAdminTemplate('Summary.html');
        $tpl->SetBlock('summary');
        $tpl->SetVariable('menubar', $this->MenuBar('Summary'));

        // Ok, start the stats!
        $tpl->SetVariable('blog_stats', _t('BLOG_STATS'));
        // First entry

        $tpl->SetBlock('summary/item');
        $bg = Jaws_Utils::RowColor(null);
        $tpl->SetVariable('bgcolor', $bg);
        $tpl->SetVariable('label', _t('BLOG_FIRST_ENTRY'));
        if (isset($summary['min_date'])) {
            $date = $GLOBALS['app']->loadDate();
            $tpl->SetVariable('value', $date->Format($summary['min_date']));
        } else {
            $tpl->SetVariable('value', '');
        }
        $tpl->ParseBlock('summary/item');

        // Last entry
        $tpl->SetBlock('summary/item');
        $bg = Jaws_Utils::RowColor($bg);
        $tpl->SetVariable('bgcolor', $bg);
        $tpl->SetVariable('label', _t('BLOG_LAST_ENTRY'));
        if (isset($summary['max_date'])) {
            $date = $GLOBALS['app']->loadDate();
            $tpl->SetVariable('value', $date->Format($summary['max_date']));
        } else {
            $tpl->SetVariable('value', '');
        }
        $tpl->ParseBlock('summary/item');


        // Blog entries
        $tpl->SetBlock('summary/item');
        $bg = Jaws_Utils::RowColor($bg);
        $tpl->SetVariable('bgcolor', $bg);
        $tpl->SetVariable('label', _t('BLOG_TOTAL_ENTRIES'));
        $tpl->SetVariable('value', isset($summary['qty_posts']) ? $summary['qty_posts'] : '');
        $tpl->ParseBlock('summary/item');

        // Avg. entries per week
        $tpl->SetBlock('summary/item');
        $bg = Jaws_Utils::RowColor($bg);
        $tpl->SetVariable('bgcolor', $bg);
        $tpl->SetVariable('label', _t('BLOG_AVERAGE_ENTRIES'));
        $tpl->SetVariable('value', isset($summary['AvgEntriesPerWeek']) ? $summary['AvgEntriesPerWeek'] : '');
        $tpl->ParseBlock('summary/item');


        // Comments
        $tpl->SetBlock('summary/item');
        $bg = Jaws_Utils::RowColor($bg);
        $tpl->SetVariable('bgcolor', $bg);
        $tpl->SetVariable('label', _t('BLOG_COMMENTS_RECEIVED'));
        $tpl->SetVariable('value', isset($summary['CommentsQty']) ? $summary['CommentsQty'] : '');
        $tpl->ParseBlock('summary/item');

        // Recent entries
        if (isset($summary['Entries']) && count($summary['Entries']) > 0) {
            $tpl->SetBlock('summary/recent');
            $tpl->SetVariable('title', _t('BLOG_RECENT_ENTRIES'));

            $date = $GLOBALS['app']->loadDate();
            foreach ($summary['Entries'] as $e) {
                $tpl->SetBlock('summary/recent/link');
                $url = BASE_SCRIPT . '?gadget=Blog&action=EditEntry&id='.$e['id'];
                if ($e['published'] === false) {
                    $extra = '<span style="color: #999; font-size: 10px;"> [' . _t('BLOG_DRAFT') . '] </span>';
                } else {
                    $extra = '';
                }
                $tpl->SetVariable('url',   $url);
                $tpl->SetVariable('title', $e['title']);
                $tpl->SetVariable('extra', $extra);
                $tpl->SetVariable('date',  $date->Format($e['publishtime']));
                $tpl->ParseBlock('summary/recent/link');
            }
            $tpl->ParseBlock('summary/recent');
        }

        $tpl->ParseBlock('summary');
        return $tpl->Get();
    }

}