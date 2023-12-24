<?php
/**
 * Blog Gadget
 *
 * @category    Gadget
 * @package     Blog
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2017-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_PopularPosts extends Jaws_Gadget_Action
{
    /**
     * Get popular action params
     *
     * @access  private
     * @return  array    list of PopularPosts action params
     */
    function PopularPostsLayoutParams()
    {
        $result = array();

        $result[] = array(
            'title' => Jaws::t('TIME'),
            'value' => array(
                0 => $this::t('POPULAR_POSTS_ALLTIME'),
                1 => $this::t('POPULAR_POSTS_TODAY'),
            ),
        );

        $result[] = array(
            'title' => Jaws::t('COUNT'),
            'value' => $this->gadget->registry->fetch('popular_limit')
        );
        return $result;
    }

    /**
     * Get popular posts
     *
     * @access  public
     * @param   int     $from   From time(0: all time, 1: today)
     * @param   int     $limit
     * @return  string  XHTML Template content
     */
    function PopularPosts($from = 0, $limit = 0)
    {
        $tpl = $this->gadget->template->load('PopularPosts.html');
        if ($this->app->requestedActionMode == ACTION_MODE_NORMAL) {
            $baseBlock = 'popular_posts_normal';
            $page = (int)$this->gadget->request->fetch('page', 'get');
            $page = empty($page)? 1 : (int)$page;
        } else {
            $page = 1;
            $baseBlock = 'popular_posts_layout';
        }
        $limit = empty($limit)? $this->gadget->registry->fetch('popular_limit') : $limit;

        $tpl->SetBlock($baseBlock);
        $tpl->SetVariable('title', $this::t('POPULAR_POSTS'));

        $model = $this->gadget->model->load('Posts');
        $entries = $model->GetPopularPosts($from, $limit, ($page - 1) * $limit);
        $entriesCount = $model->GetPopularPostsCount($from);
        if (!Jaws_Error::IsError($entries)) {
            $date = Jaws_Date::getInstance();
            foreach ($entries as $entry) {
                $tpl->SetBlock("$baseBlock/item");

                $tpl->SetVariablesArray($entry);
                $id = empty($entry['fast_url']) ? $entry['id'] : $entry['fast_url'];
                $perm_url = $this->gadget->urlMap('SingleView', array('id' => $id));
                $tpl->SetVariable('url', $perm_url);

                $tpl->SetVariable('posted_by', $this::t('POSTED_BY'));
                $tpl->SetVariable(
                    'author-url',
                    $this->gadget->urlMap('ViewAuthorPage', array('id' => $entry['username']))
                );
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

                $tpl->ParseBlock("$baseBlock/item");
            }
        }

        if ($this->app->requestedActionMode == ACTION_MODE_NORMAL) {
            // Pagination
            $this->gadget->action->load('PageNavigation')->pagination(
                $tpl,
                $page,
                $limit,
                $entriesCount,
                'PopularPosts',
                array(),
                $this::t('PAGES_COUNT', $entriesCount)
            );
        } else {
            if ($entriesCount > $limit) {
                $tpl->SetBlock("$baseBlock/more");
                $tpl->SetVariable('lbl_more', Jaws::t('MORE'));
                $tpl->SetVariable('url_more', $this->gadget->urlMap('PopularPosts'));
                $tpl->ParseBlock("$baseBlock/more");
            }
        }

        $tpl->ParseBlock($baseBlock);
        return $tpl->Get();
    }

}