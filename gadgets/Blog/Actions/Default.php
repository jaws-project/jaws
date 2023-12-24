<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Default extends Jaws_Gadget_Action
{
    /**
     * Calls default action(view)
     *
     * @access  public
     * @return  string XHTML template content
     */
    function DefaultAction()
    {
        $default_view = $this->gadget->registry->fetch('default_view');
        switch ($default_view) {
            case 'default_category':
                $cat = $this->gadget->registry->fetch('default_category');
                $postsHTML = $this->gadget->action->load('Posts');
                return $postsHTML->ViewPage($cat);
                break;

            case 'monthly':
                $dpModel = $this->gadget->model->load('DatePosts');
                $dates = $dpModel->GetPostsDateLimitation(true);
                $date = Jaws_Date::getInstance();
                $mDate = $date->Format($dates['max_date'], 'yyyy-MM');
                $mDate = explode('-', $mDate);
                $dateHTML = $this->gadget->action->load('DatePosts');
                return $dateHTML->ViewDatePage($mDate[0], $mDate[1]);
                break;

            case 'latest_entry':
                $postHTML = $this->gadget->action->load('Post');
                return $postHTML->LastPost();
                break;

            default:
                $postsHTML = $this->gadget->action->load('Posts');
                return $postsHTML->ViewPage();
        }
    }

    /**
     * Get navigation links
     * 
     * @access  public
     * @param   string  $purl
     * @param   string  $ptitle     title
     * @param   string  $nurl       url
     * @param   string  $ntitle     title
     * @return  string  XHTML template content
     */
    function GetNavigation($purl, $ptitle, $nurl, $ntitle)
    {
        $tpl = $this->gadget->template->load('PageNavigation.html');
        $tpl->SetBlock('pager');
        $tpl->SetBlock('pager/simple-navigation');

        if (!is_null($purl)) {
                $tpl->SetBlock('pager/simple-navigation/previous');
                $tpl->SetVariable('url', $purl);
                $tpl->SetVariable('title', $ptitle);
                $tpl->ParseBlock('pager/simple-navigation/previous');
        }

        if (!is_null($nurl)) {
                $tpl->SetBlock('pager/simple-navigation/next');
                $tpl->SetVariable('url', $nurl);
                $tpl->SetVariable('title',$ntitle);
                $tpl->ParseBlock('pager/simple-navigation/next');
        }

        $tpl->ParseBlock('pager/simple-navigation');
        $tpl->ParseBlock('pager');

        return $tpl->Get();
    }

    /**
     * Displays a given blog entry according to given parameters
     *
     * @access  public
     * @param   object  $tpl            Jaws_Template object
     * @param   string  $tpl_base_block Template block name
     * @param   int     $entry          entry id
     * @param   bool    $show_summary   Show post summary
     * @return  string XHTML template content
     */
    function ShowEntry(&$tpl, $tpl_base_block, $entry, $show_summary = true)
    {
        $tpl->SetBlock("$tpl_base_block/entry");
        $tpl->SetVariablesArray($entry);

        $tpl->SetVariable('posted_by', $this::t('POSTED_BY'));
        $tpl->SetVariable('author-url',   $this->gadget->urlMap('ViewAuthorPage', array('id' => $entry['username'])));
        $date = Jaws_Date::getInstance();
        $tpl->SetVariable('createtime-iso',       $date->ToISO($entry['publishtime']));
        $tpl->SetVariable('createtime',           $date->Format($entry['publishtime']));
        $tpl->SetVariable('createtime-monthname', $date->Format($entry['publishtime'], 'MMMM'));
        $tpl->SetVariable('createtime-monthabbr', $date->Format($entry['publishtime'], 'MM'));
        $tpl->SetVariable('createtime-month',     $date->Format($entry['publishtime'], 'M'));
        $tpl->SetVariable('createtime-dayname',   $date->Format($entry['publishtime'], 'EEEE'));
        $tpl->SetVariable('createtime-dayabbr',   $date->Format($entry['publishtime'], 'dd'));
        $tpl->SetVariable('createtime-day',       $date->Format($entry['publishtime'], 'd'));
        $tpl->SetVariable('createtime-year',      $date->Format($entry['publishtime'], 'yyyy'));
        $tpl->SetVariable('createtime-time',      $date->Format($entry['publishtime'], 'h:m aa'));
        $tpl->SetVariable('entry-visits',         $this::t('ENTRY_VISITS', $entry['clicks']));

        if(empty($entry['image'])) {
            $tpl->SetVariable('image', Jaws::t('NOIMAGE'));
            $tpl->SetVariable('url_image', 'data:image/png;base64,');
        } else {
            $tpl->SetVariable('image', $entry['image']);
            $tpl->SetVariable('url_image', $this->app->getDataURL(). 'blog/images/'. $entry['image']);
        }

        $id = empty($entry['fast_url']) ? $entry['id'] : $entry['fast_url'];
        $perm_url = $this->gadget->urlMap('SingleView', array('id' => $id));

        $summary = $entry['summary'];
        $text    = $entry['text'];

        if ($show_summary){
            if (Jaws_UTF8::trim($text) != '') {
                $tpl->SetBlock("$tpl_base_block/entry/read-more");
                $tpl->SetVariable('url', $perm_url);
                $tpl->SetVariable('read_more', $this::t('READ_MORE'));
                $tpl->ParseBlock("$tpl_base_block/entry/read-more");
            }
            // parse via plugins
            $summary = $this->gadget->plugin->parse(
                empty($summary)? $text : $summary,
                Jaws_Plugin::PLUGIN_TYPE_MODIFIER,
                $entry['id'],
                'SingleView'
            );
            $tpl->SetVariable('text', $summary);
        } else {
            $this->app->layout->addLink(
                array(
                    'href'  => $this->gadget->urlMap('Atom'),
                    'type'  => 'application/atom+xml',
                    'rel'   => 'alternate',
                    'title' => 'Atom - All'
                )
            );
            $this->app->layout->addLink(
                array(
                    'href'  => $this->gadget->urlMap('RSS'),
                    'type'  => 'application/rss+xml',
                    'rel'   => 'alternate',
                    'title' => 'RSS 2.0 - All'
                )
            );
            // parse via plugins
            $text = $this->gadget->plugin->parse(
                empty($text)? $summary : $text,
                Jaws_Plugin::PLUGIN_TYPE_ALLTYPES,
                $entry['id'],
                'SingleView'
            );
            $tpl->SetVariable('text', $text);
        }

        $tpl->SetVariable('permanent-link', $perm_url);

        $pos = 1;
        $tpl->SetVariable('posted_in', $this::t('POSTED_IN'));
        foreach ($entry['categories'] as $cat) {
            $tpl->SetBlock("$tpl_base_block/entry/category");
            $tpl->SetVariable('id',   $cat['id']);
            $tpl->SetVariable('name', $cat['name']);
            $cid = empty($cat['fast_url']) ? $cat['id'] : $cat['fast_url'];
            $tpl->SetVariable('url',  $this->gadget->urlMap('ShowCategory', array('id' => $cid)));
            if ($pos == count($entry['categories'])) {
                $tpl->SetVariable('separator', '');
            } else {
                $tpl->SetVariable('separator', ',');
            }
            $pos++;
            $tpl->ParseBlock("$tpl_base_block/entry/category");
        }

        if(!empty($entry['type'])) {
            $tpl->SetBlock("$tpl_base_block/entry/type");
            $tpl->SetVariable('title', $entry['type']['title']);
            $tpl->SetVariable(
                'url',
                $this->gadget->urlMap('TypePosts', array('type' => $entry['type']['id']))
            );

            $tpl->ParseBlock("$tpl_base_block/entry/type");
        }

        $commentsCount = 0;
        $comments = $this::t('NO_COMMENT');
        if (Jaws_Gadget::IsGadgetInstalled('Comments')) {
            $cModel = Jaws_Gadget::getInstance('Comments')->model->load('Comments');
            $commentsCount = $cModel->GetCommentsCount(
                'Blog',
                'Post',
                $entry['id'],
                '',
                Comments_Info::COMMENTS_STATUS_APPROVED
            );
            if (!empty($commentsCount)) {
                $comments = $this::t('HAS_N_COMMENTS', $commentsCount);
            }
        }

        if ($commentsCount != 0 ||
            ($entry['allow_comments'] === true &&
             $this->gadget->registry->fetch('allow_comments') == 'true' &&
             $this->gadget->registry->fetch('allow_comments', 'Comments') != 'false'))
        {
            $tpl_block = $show_summary? 'comment-link' : 'comments-statistic';
            $tpl->SetBlock("$tpl_base_block/entry/$tpl_block");
            $tpl->SetVariable('url', $perm_url);

            $tpl->SetVariable('text_comments', $comments);
            $tpl->SetVariable('num_comments', $commentsCount);
            $tpl->ParseBlock("$tpl_base_block/entry/$tpl_block");
        }

        // Show Tags
        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $tagsHTML = Jaws_Gadget::getInstance('Tags')->action->load('Tags');
            $tagsHTML->loadReferenceTags('Blog', 'post', $entry['id'], $tpl, 'single_view/entry');
        }

        // Show Rating
        if (Jaws_Gadget::IsGadgetInstalled('Rating')) {
            $ratingHTML = Jaws_Gadget::getInstance('Rating')->action->load('RatingTypes');
            $ratingHTML->loadReferenceRating('Blog', 'post', $entry['id'], 0, $tpl, 'single_view/entry');
        }

        $tpl->ParseBlock("$tpl_base_block/entry");
        return $tpl->Get();
    }

}