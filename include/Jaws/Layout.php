<?php
/**
 * Class to manage Jaws Layout
 *
 * @category   Layout
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Layout
{
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
     * Array that will have the forward/deferred load JS links
     *
     * @var     array
     * @access  private
     */
    private $linkScripts = array(
        0 => array(),
        1 => array()
    );

    /**
     * Array that will contain other info/text
     * that has to go into the <head> part
     *
     * @var     array
     * @access  private
     */
    var $_HeadOther = array();

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
     * Layout name
     *
     * @access  private
     * @var     string
     */
    private $layout = 'Layout';

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

        $this->AddHeadLink(
            'libraries/bootstrap.fuelux/css/bootstrap.fuelux.min.css?'. JAWS_VERSION,
            'stylesheet',
            'text/css'
        );
        $this->AddScriptLink('libraries/jquery/jquery.js?'. JAWS_VERSION, false);
        $this->AddScriptLink('libraries/bootstrap.fuelux/js/bootstrap.fuelux.min.js?'. JAWS_VERSION, false);
        $this->AddScriptLink('include/Jaws/Resources/Ajax.js?'. JAWS_VERSION, false);

        $loadFromTheme = false;
        if (empty($layout_path)) {
            $theme = $GLOBALS['app']->GetTheme();
            if (!$theme['exists']) {
                Jaws_Error::Fatal('Theme '. $theme['name']. ' doesn\'t exists.');
            }

            $loadFromTheme = true;
            if (empty($layout_file)) {
                if ($GLOBALS['app']->mainIndex) {
                    if ($GLOBALS['app']->Session->GetPermission('Users', 'ManageDashboard') &&
                        $GLOBALS['app']->Session->GetAttribute('layout') &&
                        @is_file($theme['path']. 'Index.Dashboard.html')
                    ) {
                        $layout_file = 'Index.Dashboard.html';
                    } elseif (@is_file($theme['path']. 'Index.html')) {
                        $layout_file = 'Index.html';
                    } else {
                        $layout_file = 'Layout.html';
                    }
                } else {
                    $gadget = $GLOBALS['app']->mainGadget;
                    $action = $GLOBALS['app']->mainAction;
                    if (@is_file($theme['path']. "$gadget.$action.html")) {
                        $layout_file = "$gadget.$action.html";
                    } elseif (@is_file($theme['path']. "$gadget.html")) {
                        $layout_file = "$gadget.html";
                    } else {
                        $layout_file = 'Layout.html';
                    }
                }
                $this->layout = basename($layout_file, '.html');
            }
        }

        $this->_Template = new Jaws_Template($loadFromTheme);
        $this->_Template->Load($layout_file, $layout_path);
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
        $this->AddScriptLink('libraries/jquery/jquery.js?'. JAWS_VERSION, false);
        $this->AddScriptLink('include/Jaws/Resources/Ajax.js?'. JAWS_VERSION, false);
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
        $this->_Template->SetVariable('logout-url', BASE_SCRIPT . '?gadget=Users&amp;action=Logout');
        $this->_Template->ParseBlock('layout/login-info');

        // Set the header items for each gadget and the response box
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
            $this->_Template->SetVariable('text', $responses[0]['text']);
            $this->_Template->SetVariable('type', $responses[0]['type']);
        }
    }

    /**
     * Gets current layout name
     *
     * @access  public
     * @return  string  Layout name
     */
    function GetLayoutName()
    {
        return $this->layout;
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
            $layoutModel = Jaws_Gadget::getInstance('Layout')->model->load('Layout');
            if (Jaws_Error::isError($layoutModel)) {
                Jaws_Error::Fatal("Can't load layout model");
            }

            return $layoutModel->GetLayoutItems($this->layout, true);
        }

        $items = array();
        $items[] = array(
            'id'       => null,
            'gadget'   => '[REQUESTEDGADGET]',
            'action'   => '[REQUESTEDACTION]',
            'params'   => '',
            'filename' => '',
            'when'     => '*',
            'section'  => 'main',
            'position' => 0
        );
        return $items;
    }

    /**
     * Is gadget item displayable?
     *
     * @access  public
     * @return  bool
     */
    function IsDisplayable($gadget, $action, $when, $index)
    {
        $displayWhen = array_filter(explode(',', $when));
        if ($when == '*' || ($index && in_array('index', $displayWhen))) {
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

            $section = '';
            foreach ($items as $item) {
                $block = 'layout/' . $item['section'];
                if (!$this->_Template->BlockExists($block)) {
                    continue;
                }
                $content = '';
                $this->_Template->SetBlock($block);
                if ($item['gadget'] == '[REQUESTEDGADGET]') {
                    $item['gadget'] = $GLOBALS['app']->mainGadget;
                    $item['action'] = $GLOBALS['app']->mainAction;
                    $item['params'] = array();
                    $content = $req_result;
                } elseif (!$onlyMainAction) {
                    if ($this->IsDisplayable($GLOBALS['app']->mainGadget,
                                             $GLOBALS['app']->mainAction,
                                             $item['when'],
                                             $GLOBALS['app']->mainIndex))
                    {
                        if ($GLOBALS['app']->Session->GetPermission($item['gadget'], $default_acl)) {
                            $item['params'] = unserialize($item['params']);
                            $content = $this->PutGadget(
                                $item['gadget'],
                                $item['action'],
                                $item['params'],
                                $item['filename'],
                                $item['section']
                            );
                        }
                    }
                }

                if (!empty($content)) {
                    // set gadget,action and first parameter for more customizable view
                    $this->_Template->SetVariable('gadget', strtolower($item['gadget']));
                    $this->_Template->SetVariable(
                      'gadget_action',
                      strtolower($item['gadget']. '_'. $item['action'])
                    );
                    if (!empty($item['params'])) {
                      $this->_Template->SetVariable(
                        'gadget_action_params',
                        strtolower($item['gadget']. '_'. $item['action']. '_'. $item['params'][0]));
                    }
                    // set position in section
                    $this->_Template->SetVariable('position', $item['position']);
                    // set action content
                    $this->_Template->SetVariable('ELEMENT', $content."\n");
                }
                $this->_Template->ParseBlock($block, $ignore = empty($content));
            }

            // restore stored title/description because layout action can't them
            $this->_Title = $title;
            $this->_Description = $description;
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
    function PutGadget($gadget, $action, $params = null, $filename = '', $section = '')
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
                $GLOBALS['app']->requestedGadget  = $gadget;
                $GLOBALS['app']->requestedAction  = $action;
                $GLOBALS['app']->requestedSection = $section;
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
        //$headScripts = array_key_exists('href', $headScripts)? array($headScripts) : $headScripts;
        $headScripts = array_merge($headScripts[0], $headScripts[1]);

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
        $headContent = $this->GetHeaderContent(
            $this->_HeadLink,
            $this->linkScripts,
            $this->_HeadMeta,
            $this->_HeadOther
        );

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
     * @param   string  $href       The path for the source
     * @param   bool    $deferred   Deferred load script
     * @param   string  $type       The mime type
     * @return  array   array include head script information
     */
    function AddScriptLink($href, $deferred = true, $type = 'text/javascript')
    {
        $script = array(
            'href' => $href,
            'type' => $type,
        );
        $this->linkScripts[(int)$deferred][md5($href)] = $script;
        return $script;
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