<?php
/**
 * Comments Gadget
 *
 * @category   Gadget
 * @package    Comments
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright  2017 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Comments_Actions_MostCommented extends Jaws_Gadget_Action
{
    /**
     * Get MostCommented action params
     *
     * @access  public
     * @return  array list of MostCommented action params
     */
    function MostCommentedLayoutParams()
    {
        $result = array();

        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
        $objTranslate = Jaws_Translate::getInstance();
        $objTranslate->LoadTranslation('Blog', JAWS_COMPONENT_GADGET, $site_language);
        $objTranslate->LoadTranslation('Phoo', JAWS_COMPONENT_GADGET, $site_language);
        $objTranslate->LoadTranslation('Shoutbox', JAWS_COMPONENT_GADGET, $site_language);

        $result[] = array(
            'title' => _t('COMMENTS_GADGETS'),
            'value' => array(
                '' => _t('GLOBAL_ALL') ,
                'Blog' => _t('BLOG_TITLE') ,
                'Phoo' => _t('PHOO_TITLE') ,
                'Shoutbox' => _t('SHOUTBOX_TITLE') ,
                'Comments' => _t('COMMENTS_TITLE') ,
            )
        );


        $result[] = array(
            'title' => _t('GLOBAL_COUNT'),
            'value' => $this->gadget->registry->fetch('recent_comment_limit')
        );

        return $result;
    }


    /**
     * Displays recent comments
     *
     * @access  public
     * @param   string  $gadget
     * @param   mixed   $limit    limit recent comments (int)
     * @return  string  XHTML content
     */
    function MostCommented($gadget = '', $limit = 0)
    {
        // FIXME: Added a registry key for limit count
        $limit = empty($limit)? 10 : $limit;
        if ($GLOBALS['app']->requestedActionMode == ACTION_MODE_NORMAL) {
            $baseBlock = 'comments_normal';
            //$rqst = $this->gadget->request->fetch(array('gadget', 'page'), 'get');
            //$gadget = is_null($rqst['gadget'])? $gadget : $rqst['gadget'];
            $rqst = $this->gadget->request->fetch(array('page'), 'get');
            $page = empty($rqst['page'])? 1 : (int)$rqst['page'];
        } else {
            $page = 1;
            $baseBlock = 'comments_layout';
        }

        $entries = $this->gadget->model->load('Comments')->MostCommented($gadget, $limit, ($page - 1) * $limit);
        if (Jaws_Error::IsError($entries) || empty($entries)) {
            return false;
        }

        $site_language = $this->gadget->registry->fetch('site_language', 'Settings');
        $objTranslate = Jaws_Translate::getInstance();
        $objTranslate->LoadTranslation($gadget, JAWS_COMPONENT_GADGET, $site_language);

        $gadget_name = (empty($gadget)) ? _t('GLOBAL_ALL') : _t(strtoupper($gadget) . '_TITLE');
        $tpl = $this->gadget->template->load('MostCommented.html');
        $tpl->SetBlock($baseBlock);
        $tpl->SetVariable('title', _t('COMMENTS_MOST_COMMENTED', $gadget_name));
        if(!empty($gadget)) {
            $tpl->SetVariable('gadget', $gadget);
        }

        foreach ($entries as $entry) {
            $tpl->SetBlock("$baseBlock/entry");
            $tpl->SetVariable('link', $entry['reference_link']);
            $tpl->SetVariable('title', $entry['reference_title']);
            $tpl->ParseBlock("$baseBlock/entry");
        }

        $mostCount = $this->gadget->model->load('Comments')->GetMostCommentedCount($gadget);
        if (!Jaws_Error::IsError($mostCount)) {
            if ($GLOBALS['app']->requestedActionMode == ACTION_MODE_NORMAL) {
                // Pagination
                $this->gadget->action->load('PageNavigation')->pagination(
                    $tpl,
                    $page,
                    $limit,
                    $mostCount,
                    'MostCommented',
                    array(),
                    _t('COMMENTS_PAGES_COUNT', $mostCount)
                );
            } else {
                if ($mostCount > $limit) {
                    $tpl->SetBlock("$baseBlock/more");
                    $tpl->SetVariable('lbl_more', _t('GLOBAL_MORE'));
                    $tpl->SetVariable('url_more', $this->gadget->urlMap('MostCommented'));
                    $tpl->ParseBlock("$baseBlock/more");
                }
            }
        }

        $tpl->ParseBlock($baseBlock);
        return $tpl->Get();
    }

}