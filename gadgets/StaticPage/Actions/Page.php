<?php
/**
 * StaticPage Gadget
 *
 * @category   Gadget
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Actions_Page extends Jaws_Gadget_Action
{
    /**
     * Displays a block of static pages
     *
     * @access  public
     * @return  string  XHTML content
     */
    function PagesList()
    {
        $model = $this->gadget->model->load('Page');
        $pages = $model->GetPages();
        if (Jaws_Error::IsError($pages)) {
            return false;
        }

        $tpl = $this->gadget->template->load('StaticPage.html');
        $tpl->SetBlock('index');
        $tpl->SetVariable('title', _t('STATICPAGE_PAGES_LIST'));
        foreach ($pages as $page) {
            if ($page['published'] === true) {
                $param = array('pid' => empty($page['fast_url']) ? $page['base_id'] : $page['fast_url']);
                $link = $GLOBALS['app']->Map->GetURLFor('StaticPage', 'Page', $param);
                $tpl->SetBlock('index/item');
                $tpl->SetVariable('title', $page['title']);
                $tpl->SetVariable('link',  $link);
                $tpl->ParseBlock('index/item');
            }
        }
        $tpl->ParseBlock('index');

        return $tpl->Get();
    }


    /**
     * Builds an individual page
     *
     * @access  public
     * @param   string  $base_action    Determines the map to be used (Page/Pages)
     * @return  string  XHTML content
     */
    function Page($pid = null, $base_action = 'Page')
    {
        $post = jaws()->request->fetch(array('gid', 'pid','language'), 'get');
        $post['gid'] = Jaws_XSS::defilter($post['gid'], true);
        $post['pid'] = empty($pid)? Jaws_XSS::defilter($post['pid'], true) : $pid;

        $pModel = $this->gadget->model->load('Page');
        $gModel = $this->gadget->model->load('Group');
        $tModel = $this->gadget->model->load('Translation');
        if ($base_action == 'Pages') {
            $group = $gModel->GetGroup($post['gid']);
            if (Jaws_Error::IsError($group) || empty($group)) {
                return Jaws_HTTPError::Get(404);
            }
        }

        $page_id = empty($post['pid'])? $this->gadget->registry->fetch('default_page') : $post['pid'];
        $page_language = $post['language'];
        if (empty($page_language)) {
            // if page language not set try to load language traslation of page that same as site language
            $page_language = $GLOBALS['app']->GetLanguage();
            if (!$tModel->TranslationExists($page_id, $page_language)) {
                $page_language = null;
            }
        }

        $page = $pModel->GetPage($page_id,  $page_language);
        if (Jaws_Error::IsError($page) || empty($page)) {
            return Jaws_HTTPError::Get(404);
        } else {
            //add static page language to meta language tag
            $this->AddToMetaLanguages($page_language);

            $tpl = $this->gadget->template->load('StaticPage.html');
            $tpl->SetBlock('page');

            if (!$page['published'] &&
                !$GLOBALS['app']->Session->IsSuperAdmin() &&
                $page['user'] !== (int)$GLOBALS['app']->Session->GetAttribute('user'))
            {
                $this->SetTitle(_t('STATICPAGE_TITLE_NOT_FOUND'));
                $tpl->SetVariable('content', _t('STATICPAGE_CONTENT_NOT_FOUND'));
                $tpl->SetBlock('page/title');
                $tpl->SetVariable('title', _t('STATICPAGE_TITLE_NOT_FOUND'));
                $tpl->ParseBlock('page/title');
            } else {
                $this->SetTitle($page['title']);
                $this->AddToMetaKeywords($page['meta_keywords']);
                $this->SetDescription($page['meta_description']);
                $text = $this->gadget->ParseText($page['content']);
                $tpl->SetVariable('content', $text, false);
                if ($page['show_title'] === true) {
                    $tpl->SetBlock('page/title');
                    $tpl->SetVariable('title', $page['title']);
                    $tpl->ParseBlock('page/title');
                }

                if ($this->gadget->registry->fetch('multilanguage') == 'yes') {
                    $translations = $tModel->GetTranslationsOfPage($page['page_id'], true);
                    if (!Jaws_Error::isError($translations) && count($translations)>1) {
                        $tpl->SetBlock('page/translations');
                        $tpl->SetVariable('avail_trans', _t('STATICPAGE_AVAIL_TRANSLATIONS'));
                        foreach ($translations as $trans) {
                            //if ($page['language'] == $trans['language']) continue;
                            $tpl->SetBlock('page/translations/language');
                            $tpl->SetVariable('lang', $trans['language']);
                            if ($base_action == 'Pages') {
                                $param = array('gid' => !empty($group['fast_url'])? $group['fast_url'] : $group['id'],
                                    'pid' => !empty($page['fast_url'])? $page['fast_url'] : $page['page_id'],
                                    'language' => $trans['language']);
                                $tpl->SetVariable('url', $this->gadget->urlMap('Pages', $param));
                            } else {
                                $param = array('pid' => !empty($page['fast_url']) ? $page['fast_url'] : $page['page_id'],
                                    'language' => $trans['language']);
                                $tpl->SetVariable('url', $this->gadget->urlMap('Page', $param));
                            }
                            $tpl->ParseBlock('page/translations/language');
                        }
                        $tpl->ParseBlock('page/translations');
                    }
                }
            }
        }

        // Show Tags
        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $tagsHTML = Jaws_Gadget::getInstance('Tags')->action->load('Tags');
            $tagsHTML->ViewItemTags('StaticPage', 'page', $page['translation_id'], $tpl, 'page');
        }

        $tpl->ParseBlock('page');
        return $tpl->Get();
    }

    /**
     * Displays an individual page
     *
     * @access  public
     * @return  string  XHTML content
     */
    function Pages()
    {
        return $this->Page(null, 'Pages');
    }

    /**
     * Displays groups and pages in a tree view
     *
     * @access  public
     * @return  string  XHTML content
     */
    function PagesTree()
    {
        $tpl = $this->gadget->template->load('StaticPage.html');
        $tpl->SetBlock('pages_tree');
        $tpl->SetVariable('title', _t('STATICPAGE_PAGES_TREE'));

        $pModel = $this->gadget->model->load('Page');
        $gModel = $this->gadget->model->load('Group');
        $groups = $gModel->GetGroups(true);
        if (Jaws_Error::IsError($groups)) {
            return false;
        }

        foreach ($groups as $group) {
            $tpl->SetBlock('pages_tree/g_item');
            $gid = empty($group['fast_url'])? $group['id'] : $group['fast_url'];
            $glink = $GLOBALS['app']->Map->GetURLFor('StaticPage', 'GroupPages', array('gid' => $gid));
            $tpl->SetVariable('group', $group['title']);
            $tpl->SetVariable('glink',  $glink);

            $pages = $pModel->GetPages($group['id']);
            if (!Jaws_Error::IsError($pages)) {
                foreach ($pages as $page) {
                    if ($page['published']) {
                        $tpl->SetBlock('pages_tree/g_item/p_item');
                        $param = array('gid' => empty($group['fast_url'])? $group['id'] : $group['fast_url'],
                            'pid' => empty($page['fast_url'])? $page['base_id'] : $page['fast_url'],
                            'language' => $page['language']);
                        $plink = $GLOBALS['app']->Map->GetURLFor('StaticPage', 'Pages', $param);
                        $tpl->SetVariable('page', $page['title']);
                        $tpl->SetVariable('plink',  $plink);
                        $tpl->ParseBlock('pages_tree/g_item/p_item');
                    }
                }
            }

            $tpl->ParseBlock('pages_tree/g_item');
        }

        $tpl->ParseBlock('pages_tree');
        return $tpl->Get();
    }

}