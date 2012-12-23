<?php
/**
 * StaticPage Gadget
 *
 * @category   Gadget
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPageHTML extends Jaws_Gadget_HTML
{
    /**
     * Excutes the default action, currently displaying the default page
     *
     * @access  public
     * @return  string  XHTML content
     */
    function DefaultAction()
    {
        return $this->Page($this->gadget->GetRegistry('default_page'));
    }

    /**
     * Builds an individual page
     *
     * @access  public
     * @param   int     $page_id        Page ID (optional)
     * @param   string  $base_action    Determines the map to be used (Page/Pages)
     * @return  string  XHTML content
     */
    function Page($page_id = null, $base_action = 'Page')
    {
        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $request =& Jaws_Request::getInstance();
        $post = $request->get(array('gid', 'pid','language'), 'get');
        $post['gid'] = $xss->defilter($post['gid'], true);
        $post['pid'] = $xss->defilter($post['pid'], true);

        $model = $GLOBALS['app']->LoadGadget('StaticPage', 'Model');
        if ($base_action == 'Pages') {
            $group = $model->GetGroup($post['gid']);
            if (Jaws_Error::IsError($group) || empty($group)) {
                require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
                return Jaws_HTTPError::Get(404);
            }
        }

        $page_id = is_null($page_id)? $post['pid'] : $page_id;
        $page_language = $post['language'];
        if (empty($page_language)) {
            // if page language not set try to load language traslation of page that same as site language
            $page_language = $GLOBALS['app']->GetLanguage();
            if (!$model->TranslationExists($page_id, $page_language)) {
                $page_language = null;
            }
        }

        $page = $model->GetPage($page_id,  $page_language);
        if (Jaws_Error::IsError($page) || empty($page)) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(404);
        } else {
            //add static page language to meta language tag
            $this->AddToMetaLanguages($page_language);

            $tpl = new Jaws_Template('gadgets/StaticPage/templates/');
            $tpl->Load('StaticPage.html');
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
                $text = $this->gadget->ParseText($page['content'], 'StaticPage');
                $tpl->SetVariable('content', $text, false);
                if ($page['show_title'] === true) {
                    $tpl->SetBlock('page/title');
                    $tpl->SetVariable('title', $page['title']);
                    $tpl->ParseBlock('page/title');
                }

                if ($this->gadget->GetRegistry('multilanguage') == 'yes') {
                    $translations = $model->GetTranslationsOfPage($page['page_id'], true);
                    if (!Jaws_Error::isError($translations) && count($translations)>1) {
                        $tpl->SetBlock('page/translations');
                        $tpl->SetVariable('avail_trans', _t('STATICPAGE_AVAIL_TRANSLATIONS'));
                        foreach ($translations as $trans) {
                            //if ($page['language'] == $trans['language']) continue;
                            $tpl->SetBlock('page/translations/language');
                            $tpl->SetVariable('lang', $trans['language']);
                            if ($base_action = 'Pages') {
                                $param = array('gid' => !empty($group['fast_url'])? $group['fast_url'] : $group['id'],
                                               'pid' => !empty($page['fast_url'])? $page['fast_url'] : $page['page_id'],
                                               'language' => $trans['language']);
                                $tpl->SetVariable('url', $this->gadget->GetURLFor('Pages', $param));
                            } else {
                                $param = array('pid' => !empty($page['fast_url']) ? $page['fast_url'] : $page['page_id'],
                                               'language' => $trans['language']);
                                $tpl->SetVariable('url', $this->gadget->GetURLFor('Page', $param));
                            }
                            $tpl->ParseBlock('page/translations/language');
                        }
                        $tpl->ParseBlock('page/translations');
                    }
                }
            }
        }
        $tpl->ParseBlock('page');

        return $tpl->Get();
    }

    /**
     * Displays an index of available groups
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GroupsList()
    {
        $layoutGadget = $GLOBALS['app']->LoadGadget('StaticPage', 'LayoutHTML');
        return $layoutGadget->GroupsList();
    }

    /**
     * Displays an index of available pages
     *
     * @access  public
     * @return  string  XHTML content
     */
    function GroupPages()
    {
        $request =& Jaws_Request::getInstance();
        $gid = $request->get('gid', 'get');

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $gid = $xss->defilter($gid, true);

        $layoutGadget = $GLOBALS['app']->LoadGadget('StaticPage', 'LayoutHTML');
        $result = $layoutGadget->GroupPages($gid);
        if (!$result) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            $result = Jaws_HTTPError::Get(404);
        }

        return $result;
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
        $tpl = new Jaws_Template('gadgets/StaticPage/templates/');
        $tpl->Load('StaticPage.html');
        $tpl->SetBlock('pages_tree');
        $tpl->SetVariable('title', _t('STATICPAGE_PAGES_TREE'));

        $model = $GLOBALS['app']->LoadGadget('StaticPage', 'Model');
        $groups = $model->GetGroups(true);
        if (Jaws_Error::IsError($groups)) {
            return false;
        }

        foreach ($groups as $group) {
            $tpl->SetBlock('pages_tree/g_item');
            $gid = empty($group['fast_url'])? $group['id'] : $group['fast_url'];
            $glink = $GLOBALS['app']->Map->GetURLFor('StaticPage', 'GroupPages', array('gid' => $gid));
            $tpl->SetVariable('group', $group['title']);
            $tpl->SetVariable('glink',  $glink);

            $pages = $model->GetPages($group['id']);
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