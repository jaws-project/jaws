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
class Blog_Actions_TypePosts extends Blog_Actions_Default
{
    /**
     * Get TypePosts action params
     *
     * @access  private
     * @return  array    list of TypePosts action params
     */
    function TypePostsLayoutParams()
    {
        $result = array();
        $cModel = Jaws_Gadget::getInstance('Categories')->model->load('Categories');
        $types = $cModel->GetCategories('Blog', 'Types');
        if (!Jaws_Error::isError($types)) {
            $pcats = array();
            foreach ($types as $type) {
                $pcats[$type['id']] = $type['title'];
            }

            $result[] = array(
                'title' => _t('BLOG_TYPE'),
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
     * Displays the recent posts of a dynamic type
     *
     * @access  public
     * @param   int $type    Type ID
     * @param   int $limit
     * @return  string  XHTML Template content
     */
    function TypePosts($type = null, $limit = 0)
    {
        $tpl = $this->gadget->template->load('RecentTypePosts.html');

        if ($GLOBALS['app']->requestedActionMode == ACTION_MODE_NORMAL) {
            $baseBlock = 'recent_posts_normal';
            $type = (int)jaws()->request->fetch('type', 'get');
        } else {
            $baseBlock = 'recent_posts_layout';
        }

        $limit = empty($limit) ? $this->gadget->registry->fetch('last_entries_limit') : $limit;

        $pModel = $this->gadget->model->load('Posts');
        $cModel = Jaws_Gadget::getInstance('Categories')->model->load('Categories');
        $typeInfo = $cModel->GetCategory($type);
        if (Jaws_Error::isError($typeInfo)) {
            return false;
        }
        $cat = $typeInfo['id'];
        $title = _t('BLOG_RECENT_POSTS_BY_TYPE', $typeInfo['title']);
        $entries = $pModel->GetRecentEntriesByType($type, (int)$limit);
        if (Jaws_Error::IsError($entries) || empty($entries)) {
            return false;
        }

        $tpl->SetBlock($baseBlock);
        $tpl->SetVariable('cat',   empty($cat)? '0' : $cat);
        $tpl->SetVariable('title', $title);
        $date = Jaws_Date::getInstance();
        foreach ($entries as $e) {
            $tpl->SetBlock("$baseBlock/item");

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
            $summary = $this->gadget->plugin->parse($summary);
            $text    = $this->gadget->plugin->parse($text);

            if (Jaws_UTF8::trim($text) != '') {
                $tpl->SetBlock("$baseBlock/item/read-more");
                $tpl->SetVariable('url', $perm_url);
                $tpl->SetVariable('read_more', _t('BLOG_READ_MORE'));
                $tpl->ParseBlock("$baseBlock/item/read-more");
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

            $tpl->ParseBlock("$baseBlock/item");
        }

        $tpl->ParseBlock($baseBlock);
        return $tpl->Get();
    }

}