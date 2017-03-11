<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2017 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
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
        return array(
            array(
                'title' => _t('GLOBAL_TIME'),
                'value' => array(
                    0 => _t('BLOG_POPULAR_POSTS_ALLTIME'),
                    1 => _t('BLOG_POPULAR_POSTS_TODAY'),
                ),
            ),
        );
    }

    /**
     * Get popular posts
     *
     * @access  public
     * @param   int     $from   From time(0: all time, 1: today)
     * @return  string  XHTML Template content
     */
    function PopularPosts($from = 0)
    {
        $tpl = $this->gadget->template->load('PopularPosts.html');
        $tpl->SetBlock('popular_posts');
        $tpl->SetVariable('title', _t('BLOG_POPULAR_POSTS'));

        $model = $this->gadget->model->load('Posts');
        $entries = $model->GetPopularPosts($from);
        if (!Jaws_Error::IsError($entries)) {
            $date = Jaws_Date::getInstance();
            foreach ($entries as $entry) {
                $tpl->SetBlock('popular_posts/item');

                $tpl->SetVariablesArray($entry);
                $id = empty($entry['fast_url']) ? $entry['id'] : $entry['fast_url'];
                $perm_url = $this->gadget->urlMap('SingleView', array('id' => $id));
                $tpl->SetVariable('url', $perm_url);

                $tpl->SetVariable('posted_by', _t('BLOG_POSTED_BY'));
                $tpl->SetVariable(
                    'author-url',
                    $this->gadget->urlMap('ViewAuthorPage', array('id' => $entry['username']))
                );
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

                $tpl->ParseBlock('popular_posts/item');
            }
        }

        $tpl->ParseBlock('popular_posts');
        return $tpl->Get();
    }

}