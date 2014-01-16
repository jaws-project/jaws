<?php
/**
 * Class to manage Jaws Layout
 *
 * @category   Layout
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Layout
{
    /**
     * Model that will be used to get data
     *
     * @var    LayoutJaws_Model
     * @access  private
     */
    var $_Model;

    /**
     * Template that will be used to print the data
     *
     * @var    Jaws_Template
     * @access  private
     */
    var $_Template;

    /**
     * Array that will have the meta tags
     *
     * @var     array
     * @access  private
     */
    var $_HeadMeta = array();

    /**
     * Array that will have the links meta tags
     *
     * @var     array
     * @access  private
     */
    var $_HeadLink = array();

    /**
     * Array that will have the JS links
     *
     * @var     array
     * @access  private
     */
    var $_ScriptLink = array();

    /**
     * Array that will contain other info/text
     * that has to go into the <head> part
     *
     * @var     array
     * @access  private
     */
    var $_HeadOther = array();

    /**
     * Current section
     *
     * @access  private
     * @var string
     */
    var $_Section = '';

    /**
     * Returns the current URI location (without BASE_SCRIPT's value)
     *
     * @access  private
     * @var     string
     */
    var $_CurrentLocation;

    /**
     * Page title
     *
     * @access  private
     * @var     string
     */
    var $_Title = null;

    /**
     * Page description
     *
     * @access  private
     * @var     string
     */
    var $_Description = null;

    /**
     * Page languages
     *
     * @access  private
     * @var     array
     */
    var $_Languages = array();

    /**
     * Site attributes
     *
     * @access  private
     * @var     array
     */
    private $attributes = array();

    /**
     * Initializes the Layout
     *
     * @access  public
     */
    function Jaws_Layout()
    {
        // fetch all registry keys related to site attributes
        $this->attributes = $GLOBALS['app']->Registry->fetchAll('Settings', false);
        //parse default site keywords
        $this->attributes['site_keywords'] = array_map(
            'Jaws_UTF8::trim',
            array_filter(explode(',', $this->attributes['site_keywords']))
        );

        // set default site language
        $this->_Languages[] = $GLOBALS['app']->GetLanguage();

        $this->_Model = Jaws_Gadget::getInstance('Layout')->model->load('Layout');
        if (Jaws_Error::isError($this->_Model)) {
            Jaws_Error::Fatal("Can't load layout model");
        }
    }

    /**
     * Gets the current section
     *
     * @access  public
     * @return  string Current section
     */
    function GetSectionName()
    {
        return $this->_Section;
    }

    /**
     * Loads layout template
     *
     * @access  public
     * @param   string  $layout_path  Optional layout file path
     * @param   string  $layout_file  Optional layout file name
     * @return  void
     */
    function Load($layout_path = '', $layout_file = '')
    {
        if ($this->attributes['site_status'] == 'disabled' &&
           (JAWS_SCRIPT != 'admin' || $GLOBALS['app']->Session->Logged()) &&
           !$GLOBALS['app']->Session->IsSuperAdmin()
        ) {
            $data = Jaws_HTTPError::Get(503);
            terminate($data, 503);
        }

        $favicon = $this->attributes['site_favicon'];
        if (!empty($favicon)) {
            switch (pathinfo(basename($favicon), PATHINFO_EXTENSION) ) {
                case 'svg':
                    $this->AddHeadLink($favicon, 'icon', 'image/svg');
                    break;

                case 'png':
                    $this->AddHeadLink($favicon, 'icon', 'image/png');
                    break;

                case 'ico':
                    $this->AddHeadLink($favicon, 'icon', 'image/vnd.microsoft.icon');
                    break;

                case 'gif':
                    $this->AddHeadLink($favicon, 'icon', 'image/gif');
                    break;
            }
        }

        if (empty($layout_path)) {
            $theme = $GLOBALS['app']->GetTheme();
            if (!$theme['exists']) {
                Jaws_Error::Fatal('Theme '. $theme['name']. ' doesn\'t exists.');
            }

            $layout_path = $theme['path'];
        }

        $this->_Template = new Jaws_Template();
        $this->_Template->Load(empty($layout_file)? 'layout.html' : $layout_file, $layout_path);
        $this->_Template->SetBlock('layout');

        $direction = _t('GLOBAL_LANG_DIRECTION');
        $dir  = $direction == 'rtl' ? ".$direction" : '';
        $browser  = $GLOBALS['app']->GetBrowserFlag();
        $browser  = empty($browser)? '' : ".$browser";
        $base_url = $GLOBALS['app']->GetSiteURL('/');

        $this->_Template->SetVariable('base_url', $base_url);
        $this->_Template->SetVariable('skip_to_content', _t('GLOBAL_SKIP_TO_CONTENT'));
        $this->_Template->SetVariable('.dir', $dir);
        $this->_Template->SetVariable('.browser', $browser);
        $this->_Template->SetVariable('site-url', $base_url);
        $this->_Template->SetVariable('site-direction', $direction);
        $this->_Template->SetVariable('site-name',      $this->attributes['site_name']);
        $this->_Template->SetVariable('site-slogan',    $this->attributes['site_slogan']);
        $this->_Template->SetVariable('site-comment',   $this->attributes['site_comment']);
        $this->_Template->SetVariable('site-author',    $this->attributes['site_author']);
        $this->_Template->SetVariable('site-license',   $this->attributes['site_license']);
        $this->_Template->SetVariable('site-copyright', $this->attributes['site_copyright']);
        $cMetas = @unserialize($this->attributes['site_custom_meta']);
        if (!empty($cMetas)) {
            foreach ($cMetas as $cMeta) {
                $this->AddHeadMeta($cMeta[0], $cMeta[1]);
            }
        }

        $this->_Template->SetVariable('encoding', 'utf-8');
        $this->_Template->SetVariable('loading-message', _t('GLOBAL_LOADING'));
    }

    /**
     * Loads the template for head of control panel
     *
     * @access  public
     */
    function LoadControlPanelHead()
    {
        $this->AddScriptLink('libraries/mootools/core.js?'. JAWS_VERSION);
        $this->AddScriptLink('include/Jaws/Resources/Ajax.js?'. JAWS_VERSION);
        $this->AddHeadLink(
            'gadgets/ControlPanel/Resources/style.css?'. JAWS_VERSION,
            'stylesheet',
            'text/css'
        );

        $favicon = $this->attributes['site_favicon'];
        if (!empty($favicon)) {
            $this->AddHeadLink($favicon, 'icon', 'image/png');
        }

        $this->_Template = new Jaws_Template();
        $this->_Template->Load('Layout.html', 'gadgets/ControlPanel/Templates');
        $this->_Template->SetBlock('layout');

        $base_url = $GLOBALS['app']->GetSiteURL('/');
        $this->_Template->SetVariable('BASE_URL', $base_url);
        $this->_Template->SetVariable('skip_to_content', _t('GLOBAL_SKIP_TO_CONTENT'));
        $this->_Template->SetVariable('admin_script', BASE_SCRIPT);
        $this->_Template->SetVariable('site-name',      $this->attributes['site_name']);
        $this->_Template->SetVariable('site-slogan',    $this->attributes['site_slogan']);
        $this->_Template->SetVariable('site-copyright', $this->attributes['site_copyright']);
        $this->_Template->SetVariable('control-panel', _t('GLOBAL_CONTROLPANEL'));
        $this->_Template->SetVariable('loading-message', _t('GLOBAL_LOADING'));
        $this->_Template->SetVariable('navigate-away-message', _t('CONTROLPANEL_UNSAVED_CHANGES'));
        $this->_Template->SetVariable('encoding', 'utf-8');
    }

    /**
     * Loads the template for controlpanel
     *
     * @param   string  $gadget Gadget name
     * @access  public
     */
    function LoadControlPanel($gadget)
    {
        $this->_Template->SetBlock('layout/login-info', false);
        $this->_Template->SetVariable('logged-in-as', _t('CONTROLPANEL_LOGGED_IN_AS'));
        $uInfo = $GLOBALS['app']->Session->GetAttributes('username', 'nickname', 'avatar', 'email');
        $this->_Template->SetVariable('username', $uInfo['username']);
        $this->_Template->SetVariable('nickname', $uInfo['nickname']);
        $this->_Template->SetVariable('email',    $uInfo['email']);
        $this->_Template->SetVariable('avatar',   $uInfo['avatar']);
        $this->_Template->SetVariable('site-url', $GLOBALS['app']->GetSiteURL());
        $this->_Template->SetVariable('view-site', _t('GLOBAL_VIEW_SITE'));

        if ($GLOBALS['app']->Session->GetPermission('Users', 'default_admin, EditAccountInformation')) {
            $uAccoun =& Piwi::CreateWidget('Link',
                                           $uInfo['nickname'],
                                           BASE_SCRIPT . '?gadget=Users&amp;action=MyAccount');
        } else {
            $uAccoun =& Piwi::CreateWidget('Label', $uInfo['nickname']);
        }

        $this->_Template->SetVariable('my-account', $uAccoun->Get());
        $this->_Template->SetVariable('logout', _t('GLOBAL_LOGOUT'));
        $this->_Template->SetVariable('logout-url', $GLOBALS['app']->Map->GetURLFor('Users', 'Logout'));
        $this->_Template->ParseBlock('layout/login-info');

        // Set the header thingie for each gadget and the response box
        if (isset($gadget) && ($gadget != 'ControlPanel')){
            $gInfo  = Jaws_Gadget::getInstance($gadget);
            $docurl = null;
            if (!Jaws_Error::isError($gInfo)) {
                $docurl = $gInfo->GetDoc();
            }
            $gname = _t(strtoupper($gadget) . '_TITLE');
            $this->_Template->SetBlock('layout/cptitle');
            $this->_Template->SetVariable('admin_script', BASE_SCRIPT);
            $this->_Template->SetVariable('cp-title', _t('GLOBAL_CONTROLPANEL'));
            $this->_Template->SetVariable('cp-title-separator', _t('GLOBAL_CONTROLPANEL_TITLE_SEPARATOR'));
            $this->_Template->SetVariable('title-name', $gname);
            $this->_Template->SetVariable('icon-gadget', 'gadgets/'.$gadget.'/Resources/images/logo.png');
            $this->_Template->SetVariable('title-gadget', $gadget);
            
            // help icon
            if (!empty($docurl) && !is_null($docurl)) {
                $this->_Template->SetBlock('layout/cptitle/documentation');
                $this->_Template->SetVariable('src', 'gadgets/ControlPanel/Resources/images/help.png');
                $this->_Template->SetVariable('alt', _t('GLOBAL_HELP'));
                $this->_Template->SetVariable('url', $docurl);
                $this->_Template->ParseBlock('layout/cptitle/documentation');
            }

            $this->_Template->ParseBlock('layout/cptitle');
        }

        if ($this->attributes['site_status'] == 'disabled') {
            $this->_Template->SetBlock('layout/warning');
            $this->_Template->SetVariable('warning', _t('GLOBAL_WARNING_OFFLINE'));
            $this->_Template->ParseBlock('layout/warning');
        }

        $responses = $GLOBALS['app']->Session->PopLastResponse();
        if ($responses) {
            foreach ($responses as $msg_id => $response) {
                $this->_Template->SetBlock('layout/msgbox');
                $this->_Template->SetVariable('text', $response['text']);
                $this->_Template->SetVariable('type', $response['type']);
                $this->_Template->SetVariable('msg-id', $msg_id);
                $this->_Template->ParseBlock('layout/msgbox');
            }
        }
    }

    /**
     * Changes the site-title with something else
     *
     * @access  public
     * @param   string  $title  New title
     */
    function SetTitle($title)
    {
        $this->_Title = strip_tags($title);
    }

    /**
     * Gets the site-title
     *
     * @access  public
     * @return  string  site-title
     */
    function GetTitle()
    {
        return $this->_Title;
    }

    /**
     * Assign the right head's title
     *
     * @access  public
     */
    function PutTitle()
    {
        if (!empty($this->_Title)) {
            $pageTitle = array($this->_Title, $this->attributes['site_name']);
        } else {
            $slogan = $this->attributes['site_slogan'];
            $pageTitle   = array();
            $pageTitle[] = $this->attributes['site_name'];
            if (!empty($slogan)) {
                $pageTitle[] = $slogan;
            }
        }
        $pageTitle = implode(' ' . $this->attributes['site_title_separator'].' ', $pageTitle);
        $this->_Template->ResetVariable('site-title', $pageTitle, 'layout');
    }

    /**
     * Changes the site-description with something else
     *
     * @access  public
     * @param   string  $desc  New description
     */
    function SetDescription($desc)
    {
        $this->_Description = strip_tags($desc);
    }

    /**
     * Assign the right page's description
     *
     * @access  public
     */
    function PutDescription()
    {
        if (empty($this->_Description)) {
            $this->_Description = $this->attributes['site_description'];
        }
        $this->_Template->ResetVariable('site-description', $this->_Description, 'layout');
    }

    /**
     * Add keywords to meta keywords tag
     *
     * @access  public
     * @param   string  $keywords  page keywords
     */
    function AddToMetaKeywords($keywords)
    {
        if (!empty($keywords)) {
            $keywords = array_map('Jaws_UTF8::trim', explode(',', $keywords));
            $this->attributes['site_keywords'] = array_merge($this->attributes['site_keywords'], $keywords);
        }
    }

    /**
     * Assign the site keywords
     *
     * @access  public
     */
    function PutMetaKeywords()
    {
        $this->_Template->ResetVariable(
            'site-keywords',
            strip_tags(implode(', ', $this->attributes['site_keywords'])),
            'layout'
        );
    }

    /**
     * Add a language to meta language tag
     *
     * @access  public
     * @param   string  $language  Language
     */
    function AddToMetaLanguages($language)
    {
        if (!empty($language)) {
            if (!in_array($language, $this->_Languages)) {
                $this->_Languages[] = $language;
            }
        }
    }

    /**
     * Assign the site languages
     *
     * @access  public
     */
    function PutMetaLanguages()
    {
        $this->_Template->ResetVariable('site-languages',
                                        strip_tags(implode(',', $this->_Languages)),
                                        'layout');
    }

    /**
     * Returns the items that should be displayed in the layout
     *
     * @access  public
     * @return  array   Items according to BASE_SCRIPT
     */
    function GetLayoutItems()
    {
        if (JAWS_SCRIPT == 'index') {
            return $this->_Model->GetLayoutItems(
                $GLOBALS['app']->Session->GetAttribute('layout'),
                true
            );
        }
        $items = array();
        $items[] = array('id'            => null,
                         'gadget'        => '[REQUESTEDGADGET]',
                         'gadget_action' => '[REQUESTEDACTION]',
                         'display_when'  => '*',
                         'section'       => 'main',
                         );
        return $items;
    }

    /**
     * Is gadget item displayable?
     *
     * @access  public
     * @return  bool
     */
    function IsDisplayable($gadget, $action, $display_when, $index)
    {
        $displayWhen = array_filter(explode(',', $display_when));
        if ($display_when == '*' || ($index && in_array('index', $displayWhen))) {
            return true;
        }

        foreach ($displayWhen as $item) {
            $gActions = explode(';', $item);
            $g = array_shift($gActions);
            if ($g == $gadget) {
                if (empty($gActions) || in_array($action, $gActions)) {
                    return true;
                }
                break;
            }
        }

        return false;
    }

    /**
     * Look for the available gadgets and put them on the template
     *
     * @access  public
     */
    function Populate($req_result = '', $onlyMainAction = false)
    {
        $default_acl = (JAWS_SCRIPT == 'index')? 'default' : 'default_admin';
        $items = $this->GetLayoutItems();
        if (!Jaws_Error::IsError($items)) {
            // temporary store page title/description
            $title = $this->_Title;
            $description = $this->_Description;

            foreach ($items as $item) {
                if ($this->_Section != $item['section']) {
                    if (!empty($this->_Section)) {
                        $this->_Template->SetVariable('ELEMENT', $contentString);
                        $this->_Template->ParseBlock('layout/' . $this->_Section);
                        $this->_Section = '';
                    }
                    if (!$this->_Template->BlockExists('layout/' . $item['section'])) {
                        continue;
                    }
                    $this->_Section = $item['section'];
                    $this->_Template->SetBlock('layout/' . $this->_Section);
                    $currentContent = $this->_Template->GetCurrentBlockContent();
                    $this->_Template->SetCurrentBlockContent('{ELEMENT}');
                    $contentString  = '';
                }

                $content = '';
                if ($item['gadget'] == '[REQUESTEDGADGET]') {
                    $content = $req_result;
                } elseif (!$onlyMainAction) {
                    if ($this->IsDisplayable($GLOBALS['app']->mainGadget,
                                             $GLOBALS['app']->mainAction,
                                             $item['display_when'],
                                             $GLOBALS['app']->mainIndex))
                    {
                        if ($GLOBALS['app']->Session->GetPermission($item['gadget'], $default_acl)) {
                            $content = $this->PutGadget(
                                $item['gadget'],
                                $item['gadget_action'],
                                unserialize($item['action_params']),
                                $item['action_filename']
                            );
                        }
                    }
                }

                if (!empty($content)) {
                    $contentString .= str_replace('{ELEMENT}', $content, $currentContent)."\n\n\n";
                }
            }

            // restore stored title/description because layout action can't them
            $this->_Title = $title;
            $this->_Description = $description;

            if (!empty($this->_Section)) {
                $this->_Template->SetVariable('ELEMENT', $contentString);
                $this->_Template->ParseBlock('layout/' . $this->_Section);
            }
        }
    }

    /**
     * Put a gadget on the template
     *
     * @access  public
     * @param   string  $gadget  Gadget to put
     * @param   string  $action  Action to execute
     * @param   mixed   $params  Action's params
     */
    function PutGadget($gadget, $action, $params = null, $filename = '')
    {
        $output = '';
        $enabled = Jaws_Gadget::IsGadgetEnabled($gadget);
        if (Jaws_Error::isError($enabled) || $enabled != 'true') {
            $GLOBALS['log']->Log(JAWS_LOG_NOTICE, "Gadget $gadget is not enabled");
            return $output;
        }

        if (!Jaws_Gadget::IsGadgetUpdated($gadget)) {
            $GLOBALS['log']->Log(
                JAWS_LOG_NOTICE,
                'Trying to populate '. $gadget.
                ' in layout, but looks that it is not installed/upgraded'
            );
            return $output;
        }

        jaws()->http_response_code(200);
        $goGadget = Jaws_Gadget::getInstance($gadget)->action->load($filename);
        if (!Jaws_Error::isError($goGadget)) {
            if (method_exists($goGadget, $action)) {
                $GLOBALS['app']->requestedGadget = $gadget;
                $GLOBALS['app']->requestedAction = $action;
                $GLOBALS['app']->requestedActionMode = ACTION_MODE_LAYOUT;
                if (is_null($params)) {
                    $output = $goGadget->$action();
                } else {
                    $output = call_user_func_array(array($goGadget, $action), $params);
                }
            } else {
                $GLOBALS['log']->Log(JAWS_LOG_ERROR, "Action $action in $gadget's Actions dosn't exist.");
            }
        }

        if (Jaws_Error::isError($output)) {
            $GLOBALS['log']->Log(JAWS_LOG_ERROR, 'In '.$gadget.'::'.$action.','.$output->GetMessage());
            $output = '';
        } elseif (jaws()->http_response_code() !== 200) {
            $output = '';
        }

        return $output;
    }

    /**
     * Get the HTML code of the head content.
     *
     * @access  public
     */
    function GetHeaderContent(&$headLinks, &$headScripts, &$headMeta, &$headOther)
    {
        $headContent = '';
        // if not passed array of head links
        $headLinks = array_key_exists('rel', $headLinks)? array($headLinks) : $headLinks;
        // if not passed array of head scripts
        $headScripts = array_key_exists('href', $headScripts)? array($headScripts) : $headScripts;

        // meta
        foreach ($headMeta as $meta) {
            if ($meta['use_http_equiv']) {
                $meta_add = 'http-equiv="' . $meta['use_http_equiv'] . '"';
            } else {
                $meta_add = 'name="' . $meta['name'] . '"';
            }

            $headContent.= '<meta ' . $meta_add . ' content="' . $meta['content'] . '" />' . "\n";
        }

        // link
        foreach ($headLinks as $link) {
            $title = '';
            $headContent.= '<link rel="' . $link['rel'] . '"';
            if (!empty($link['media'])) {
                $headContent.= ' media="' . $link['media'] . '"';
            }
            if (!empty($link['type'])) {
                $headContent.= ' type="' . $link['type'] . '"';
            }
            if (!empty($link['href'])) {
                $headContent.= ' href="' . $link['href'] . '"';
            }
            if (!empty($link['title'])) {
                $headContent.= ' title="' . $link['title'] . '"';
            }
            $headContent.= ' />' . "\n";
        }

        //script
        foreach ($headScripts as $link) {
            $headContent.= '<script type="' . $link['type'] . '" src="' . $link['href'] . '"></script>' . "\n";
        }

        // other
        foreach ($headOther as $element) {
            $headContent .= $element . "\n";
        }

        return $headContent;
    }

    /**
     * Shows the HTML of the Layout.
     *
     * @access  public
     */
    function Get($raw_content = false)
    {
        // Set Headers
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        $this->AddHeadMeta('generator', 'Jaws Project (http://jaws-project.com)');
        $use_rewrite = $GLOBALS['app']->Registry->fetch('map_use_rewrite', 'UrlMapper') == 'true';
        $use_rewrite = $use_rewrite && (JAWS_SCRIPT == 'index');
        $this->AddHeadMeta(
            'application-name',
            ($use_rewrite? '' : BASE_SCRIPT).':'.
            $GLOBALS['app']->mainGadget.':'.
            $GLOBALS['app']->mainAction
        );
        $headContent = $this->GetHeaderContent($this->_HeadLink, $this->_ScriptLink, $this->_HeadMeta, $this->_HeadOther);

        if (!empty($headContent)) {
            $this->_Template->SetBlock('layout/head');
            $this->_Template->SetVariable('ELEMENT', $headContent);
            $this->_Template->ParseBlock('layout/head');
        }

        if (JAWS_SCRIPT == 'index') {
            $this->PutTitle();
            $this->PutDescription();
            $this->PutMetaKeywords();
            $this->PutMetaLanguages();
        }

        // parse template an show the HTML
        $this->_Template->ParseBlock('layout');
        if ($raw_content) {
            return $this->_Template->Get();
        } else {
            $content = $this->_Template->Get();
            if ($GLOBALS['app']->GZipEnabled()) {
                if (false == strpos($GLOBALS['app']->GetBrowserEncoding(), 'x-gzip')) {
                    jaws()->request->update('restype', 'gzip');
                } else {
                    jaws()->request->update('restype', 'x-gzip');
                }
            }
            return $content;
        }
    }

    /**
     * Add a meta tag
     *
     * @access  public
     * @param   string  $name           Key of the meta tag
     * @param   string  $content        Value of the key
     * @param   bool    $use_http_equiv Use the equiv of HTTP
     */
    function AddHeadMeta($name, $content, $use_http_equiv = false)
    {
        $this->_HeadMeta[$name]['name']    = $name;
        $this->_HeadMeta[$name]['content'] = $content;
        $this->_HeadMeta[$name]['use_http_equiv'] = $use_http_equiv;
    }

    /**
     * Add a HeadLink
     *
     * @access  public
     * @param   string  $link  The HREF
     * @param   string  $rel   The REL that will be associated
     * @param   string  $type  Type of HeadLink
     * @param   string  $title Title of the HeadLink
     * @param   string  $media Media type, screen, print or such
     * @return  array   array include head link information
     */
    function AddHeadLink($link, $rel = 'stylesheet', $type = 'text/css', $title = '', $media = '')
    {
        $version = '';
        $hashedLink = md5($link);
        if (!isset($this->_HeadLink[$hashedLink])) {
            if ($rel == 'stylesheet') {
                $link = ltrim($link);
                $fileName = basename($link);
                $filePath = substr($link , 0, - strlen($fileName));
                $version  = strstr($fileName, '?');
                $fileName = substr($fileName, 0, strlen($fileName)-strlen($version));
                $fileExtn = strrchr($fileName, '.');
                $fileName = substr($fileName, 0, -strlen($fileExtn));

                $prefix = (_t('GLOBAL_LANG_DIRECTION') == 'rtl')? '.rtl' : '';
                $link = $filePath. $fileName. $prefix. $fileExtn;
                if (!empty($prefix) && !@file_exists($link)) {
                    $link = $filePath . $fileName . $fileExtn;
                }
            }

            $this->_HeadLink[$hashedLink] = array(
                'href'  => $link.$version,
                'rel'   => $rel,
                'type'  => $type,
                'title' => $title,
                'media' => $media,
            );
        }

        return $this->_HeadLink[$hashedLink];
    }

    /**
     * Add a Javascript source
     *
     * @access  public
     * @param   string  $href   The path for the source.
     * @param   bool    $standanlone for use in static load
     * @param   string  $type   The mime type.
     * @return  array   array include head script information
     */
    function AddScriptLink($href, $type = 'text/javascript')
    {
        $sLink = array(
            'href' => $href,
            'type' => $type,
        );

        $this->_ScriptLink[md5($href)] = $sLink;
        return $sLink;
    }

    /**
     * Add other info to the head tag
     *
     * @access  public
     * @param   string  $text Text to add.
     * @return  null
     * @since   0.6
     */
    function addHeadOther($text)
    {
        $this->_HeadOther[] = $text;
    }

}