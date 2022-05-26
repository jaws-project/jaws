<?php
/**
 * FeedReader Gadget Action
 *
 * @category   GadgetAdmin
 * @package    FeedReader
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh  <afz@php.net>
 * @copyright  2005-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class FeedReader_Actions_Admin_Feed extends Jaws_Gadget_Action
{
    /**
     * Prepares data of feed sites for data grid
     *
     * @access  public
     * @param   int    $offset  Data offset
     * @return  array  List of feed sites
     */
    function GetFeedSites($offset = null)
    {
        $model = $this->gadget->model->loadAdmin('Feed');

        $sites = $model->GetFeeds(false, 0, 12, $offset);
        if (Jaws_Error::IsError($sites)) {
            return array();
        }

        $newData = array();
        foreach ($sites as $site) {
            $siteData = array();
            $siteData['id']    = $site['id'];
            $siteData['title'] = '<span style="white-space: nowrap;"><a href="'.$site['url'].'" title="'.$site['url'];
            $siteData['title'].= '" target="_blank" style="text-decoration: none;">'.$site['title'].'</a></span>';
            $siteData['published'] = ($site['published']? Jaws::t('YESS') : Jaws::t('NOO'));

            $actions = '';
            $link =& Piwi::CreateWidget('Link', Jaws::t('EDIT'),
                "javascript:editFeed(this, '".$site['id']."');",
                STOCK_EDIT);
            $actions.= $link->Get().'&nbsp;';
            $link =& Piwi::CreateWidget('Link', Jaws::t('DELETE'),
                "javascript:deleteFeed(this, '".$site['id']."');",
                STOCK_DELETE);
            $actions.= $link->Get().'&nbsp;';

            $siteData['actions'] = $actions;
            $newData[] = $siteData;
        }

        return $newData;
    }

    /**
     * Builds the datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function DataGrid()
    {
        $model = $this->gadget->model->loadAdmin('Feed');
        $total = $model->TotalOfData('feeds');

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->TotalRows($total);
        $datagrid->pageBy(12);
        $datagrid->SetID('feedsites_datagrid');
        $column1 = Piwi::CreateWidget('Column', Jaws::t('ID'));
        $column1->SetStyle('width: 32px; white-space:nowrap;');
        $datagrid->AddColumn($column1);
        $datagrid->AddColumn(Piwi::CreateWidget('Column', Jaws::t('TITLE')));
        $column3 = Piwi::CreateWidget('Column', Jaws::t('PUBLISHED'), null, false);
        $column3->SetStyle('width: 56px; white-space:nowrap;');
        $datagrid->AddColumn($column3);
        $column4 = Piwi::CreateWidget('Column', Jaws::t('ACTIONS'), null, false);
        $column4->SetStyle('width: 60px; white-space:nowrap;');
        $datagrid->AddColumn($column4);
        $datagrid->SetStyle('margin-top: 0px; width: 100%;');

        return $datagrid->Get();
    }

    /**
     * Builds the FeedReader administration UI
     *
     * @access  public
     * @return  string  XHTML template content for administration UI
     */
    function ManageFeeds()
    {
        $this->AjaxMe('script.js');
        $tpl = $this->gadget->template->loadAdmin('FeedReader.html');
        $tpl->SetBlock('feedreader');

        $tpl->SetVariable('grid', $this->DataGrid());
        $tpl->SetVariable('dir', Jaws::t('LANG_DIRECTION'));

        // Tabs titles
        $tpl->SetVariable('legend_title', Jaws::t('PROPERTIES'));

        $titleentry =& Piwi::CreateWidget('Entry', 'title', '');
        $titleentry->SetID('title');
        $titleentry->setStyle('width: 270px;');
        $tpl->SetVariable('lbl_title', Jaws::t('TITLE'));
        $tpl->SetVariable('title', $titleentry->Get());

        $urlentry =& Piwi::CreateWidget('Entry', 'url', 'http://');
        $urlentry->SetID('url');
        $urlentry->setStyle('width: 270px;');
        $tpl->SetVariable('lbl_url', Jaws::t('URL'));
        $tpl->SetVariable('url', $urlentry->Get());

        $cachecombo =& Piwi::CreateWidget('Combo', 'cache_time');
        $cachecombo->SetID('cache_time');
        $cachecombo->setStyle('width: 128px;');
        $cachecombo->AddOption(Jaws::t('DISABLE'),              0);
        $cachecombo->AddOption(Jaws::t('DATE_MINUTES', 10),   600);
        $cachecombo->AddOption(Jaws::t('DATE_MINUTES', 30),  1800);
        $cachecombo->AddOption(Jaws::t('DATE_HOURS',   1),   3600);
        $cachecombo->AddOption(Jaws::t('DATE_HOURS',   5),  18000);
        $cachecombo->AddOption(Jaws::t('DATE_HOURS',   10), 36000);
        $cachecombo->AddOption(Jaws::t('DATE_DAYS',    1),  86400);
        $cachecombo->AddOption(Jaws::t('DATE_WEEKS',   1), 604800);
        $cachecombo->SetDefault(3600);
        $tpl->SetVariable('lbl_cache_time', $this::t('CACHE_TIME'));
        $tpl->SetVariable('cache_time', $cachecombo->Get());

        $viewType =& Piwi::CreateWidget('Combo', 'view_type');
        $viewType->SetID('view_type');
        $viewType->setStyle('width: 128px;');
        $viewType->AddOption($this::t('VIEW_TYPE_SIMPLE'),        0);
        $viewType->AddOption($this::t('VIEW_TYPE_MARQUEE_UP'),    1);
        $viewType->AddOption($this::t('VIEW_TYPE_MARQUEE_DOWN'),  2);
        $viewType->AddOption($this::t('VIEW_TYPE_MARQUEE_LEFT'),  3);
        $viewType->AddOption($this::t('VIEW_TYPE_MARQUEE_RIGHT'), 4);
        $tpl->SetVariable('lbl_view_type', $this::t('VIEW_TYPE'));
        $tpl->SetVariable('view_type', $viewType->Get());

        $titleViewcombo =& Piwi::CreateWidget('Combo', 'title_view');
        $titleViewcombo->SetID('title_view');
        $titleViewcombo->setStyle('width: 128px;');
        $titleViewcombo->AddOption($this::t('TITLE_VIEW_DISABLE'),  0);
        $titleViewcombo->AddOption($this::t('TITLE_VIEW_INTERNAL'), 1);
        $titleViewcombo->AddOption($this::t('TITLE_VIEW_EXTERNAL'), 2);
        $tpl->SetVariable('lbl_title_view', $this::t('TITLE_VIEW'));
        $tpl->SetVariable('title_view', $titleViewcombo->Get());

        $countentry =& Piwi::CreateWidget('Entry', 'count_entry', '');
        $countentry->setStyle('width: 120px;');
        $tpl->SetVariable('lbl_count_entry', $this::t('SITE_COUNT_ENTRY'));
        $tpl->SetVariable('count_entry', $countentry->Get());

        $alias =& Piwi::CreateWidget('Entry', 'alias', '');
        $alias->setStyle('width: 120px;');
        $tpl->SetVariable('lbl_alias', Jaws::t('ALIAS'));
        $tpl->SetVariable('alias', $alias->Get());

        $published =& Piwi::CreateWidget('Combo', 'published');
        $published->SetID('published');
        $published->setStyle('width: 128px;');
        $published->AddOption(Jaws::t('NOO'),  0);
        $published->AddOption(Jaws::t('YESS'), 1);
        $published->SetDefault(1);
        $tpl->SetVariable('lbl_published', Jaws::t('PUBLISHED'));
        $tpl->SetVariable('published', $published->Get());

        $btncancel =& Piwi::CreateWidget('Button', 'btn_cancel', Jaws::t('CANCEL'), STOCK_CANCEL);
        $btncancel->SetStyle('visibility: hidden;');
        $btncancel->AddEvent(ON_CLICK, 'stopAction();');
        $tpl->SetVariable('btn_cancel', $btncancel->Get());

        $btnsave =& Piwi::CreateWidget('Button', 'btn_save',
            Jaws::t('SAVE'), STOCK_SAVE);
        $btnsave->AddEvent(ON_CLICK, 'updateFeed();');
        $tpl->SetVariable('btn_save', $btnsave->Get());

        $this->gadget->define('incompleteFeedFields', $this::t('INCOMPLETE_FIELDS'));
        $this->gadget->define('confirmFeedDelete',    $this::t('CONFIRM_DELETE_FEED'));

        $tpl->ParseBlock('feedreader');

        return $tpl->Get();
    }
}