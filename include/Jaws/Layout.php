<?php
/**
 * Class to manage Jaws Layout
 *
 * @category   Layout
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
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
     * Requested gadget
     *
     * @access  private
     * @var     string
     */
    var $_RequestedGadget;

    /**
     * Requested gadget's action
     *
     * @access  private
     * @var     string
     */
    var $_RequestedAction;

    /**
     * Current section
     *
     * @access  private
     * @var string
     */
    var $_Section = '';

    /**
     * Current section
     *
     * @access  private
     * @var string
     */
    var $_SectionAttributes = array();

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
     * Page keywords
     *
     * @access  private
     * @var     array
     */
    var $_Keywords = array();

    /**
     * Page languages
     *
     * @access  private
     * @var     array
     */
    var $_Languages = array();

    /**
     * Initializes the Layout
     *
     * @access  public
     */
    function Jaws_Layout()
    {
        //load default site keywords
        $keywords = $GLOBALS['app']->Registry->fetch('site_keywords', 'Settings');
        $this->_Keywords = array_map(array('Jaws_UTF8','trim'), array_filter(explode(',', $keywords)));

        // set default site language
        $this->_Languages[] = $GLOBALS['app']->GetLanguage();

        $this->_Model = $GLOBALS['app']->loadGadget('Layout', 'Model');
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
     * Is current section wide?
     *
     * @access  public
     * @return  bool
     */
    function IsSectionWide()
    {
        return !isset($this->_SectionAttributes['narrow']);
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
        if ($GLOBALS['app']->Registry->fetch('site_status', 'Settings') == 'disabled' &&
           (JAWS_SCRIPT != 'admin' || $GLOBALS['app']->Session->Logged()) &&
           !$GLOBALS['app']->Session->IsSuperAdmin()
        ) {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            echo Jaws_HTTPError::Get(503);
            exit;
        }

        $favicon = $GLOBALS['app']->Registry->fetch('site_favicon', 'Settings');
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

        $this->_Template = new Jaws_Template($layout_path);
        $this->_Template->Load(empty($layout_file)? 'layout.html' : $layout_file);
        $this->_Template->SetBlock('layout');

        $direction = _t('GLOBAL_LANG_DIRECTION');
        $dir  = $direction == 'rtl' ? '.' . $direction : '';
        $brow = $GLOBALS['app']->GetBrowserFlag();
        $brow = empty($brow)? '' : '.'.$brow;
        $base_url = $GLOBALS['app']->GetSiteURL('/');
        $site_url = $GLOBALS['app']->Registry->fetch('site_url', 'Settings');

        $this->_Template->SetVariable('BASE_URL', $base_url);
        $this->_Template->SetVariable('.dir', $dir);
        $this->_Template->SetVariable('.browser', $brow);
        $this->_Template->SetVariable('site-url', empty($site_url)? $base_url : $site_url);
        $this->_Template->SetVariable('site-name',        $GLOBALS['app']->Registry->fetch('site_name', 'Settings'));
        $this->_Template->SetVariable('site-slogan',      $GLOBALS['app']->Registry->fetch('site_slogan', 'Settings'));
        $this->_Template->SetVariable('site-comment',     $GLOBALS['app']->Registry->fetch('site_comment', 'Settings'));
        $this->_Template->SetVariable('site-author',      $GLOBALS['app']->Registry->fetch('site_author', 'Settings'));
        $this->_Template->SetVariable('site-license',     $GLOBALS['app']->Registry->fetch('site_license', 'Settings'));
        $this->_Template->SetVariable('site-copyright',   $GLOBALS['app']->Registry->fetch('copyright', 'Settings'));
        $cMetas = unserialize($GLOBALS['app']->Registry->fetch('custom_meta', 'Settings'));
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
        $this->AddScriptLink('libraries/mootools/core.js');
        $this->AddScriptLink('include/Jaws/Resources/Ajax.js');
        $this->AddHeadLink('gadgets/ControlPanel/resources/public.css', 'stylesheet', 'text/css');
        $this->AddHeadLink(PIWI_URL . 'piwidata/css/default.css', 'stylesheet', 'text/css');

        $favicon = $GLOBALS['app']->Registry->fetch('site_favicon', 'Settings');
        if (!empty($favicon)) {
            $this->AddHeadLink($favicon, 'icon', 'image/png');
        }

        $GLOBALS['app']->LoadGadget('ControlPanel', 'AdminHTML');
        $this->_Template = new Jaws_Template('gadgets/ControlPanel/templates/');
        $this->_Template->Load('Layout.html');
        $this->_Template->SetBlock('layout');

        $base_url = $GLOBALS['app']->GetSiteURL('/');
        $this->_Template->SetVariable('BASE_URL', $base_url);
        $this->_Template->SetVariable('admin_script', BASE_SCRIPT);
        $this->_Template->SetVariable('site-name',        $GLOBALS['app']->Registry->fetch('site_name', 'Settings'));
        $this->_Template->SetVariable('site-slogan',      $GLOBALS['app']->Registry->fetch('site_slogan', 'Settings'));
        $this->_Template->SetVariable('site-copyright',   $GLOBALS['app']->Registry->fetch('copyright', 'Settings'));
        $this->_Template->SetVariable('control-panel', _t('CONTROLPANEL_NAME'));
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

        if ($GLOBALS['app']->Session->GetPermission('Users', 'default_admin, EditAccountInformation')) {
            $uAccoun =& Piwi::CreateWidget('Link',
                                           $uInfo['nickname'],
                                           BASE_SCRIPT . '?gadget=Users&amp;action=MyAccount');
        } else {
            $uAccoun =& Piwi::CreateWidget('Label', $uInfo['nickname']);
        }

        $this->_Template->SetVariable('my-account', $uAccoun->Get());
        $this->_Template->SetVariable('logout', _t('GLOBAL_LOGOUT'));
        $this->_Template->SetVariable('logout-url', BASE_SCRIPT . '?gadget=ControlPanel&amp;action=Logout');
        $this->_Template->ParseBlock('layout/login-info');

        // Set the header thingie for each gadget and the response box
        if (isset($gadget) && ($gadget != 'ControlPanel')){
            $gInfo  = $GLOBALS['app']->loadGadget($gadget, 'Info');
            $docurl = null;
            if (!Jaws_Error::isError($gInfo)) {
                $docurl = $gInfo->GetDoc();
            }
            $gname = _t(strtoupper($gadget) . '_NAME');
            $this->_Template->SetBlock('layout/cptitle');
            $this->_Template->SetVariable('admin_script', BASE_SCRIPT);
            $this->_Template->SetVariable('title-cp', _t('CONTROLPANEL_NAME'));
            $this->_Template->SetVariable('title-name', $gname);
            $this->_Template->SetVariable('icon-gadget', 'gadgets/'.$gadget.'/images/logo.png');
            $this->_Template->SetVariable('title-gadget', $gadget);
            if (!empty($docurl) && !is_null($docurl)) {
                $this->_Template->SetBlock('layout/cptitle/documentation');
                $this->_Template->SetVariable('src', 'images/stock/help-browser.png');
                $this->_Template->SetVariable('alt', _t('GLOBAL_READ_DOCUMENTATION'));
                $this->_Template->SetVariable('url', $docurl);
                $this->_Template->ParseBlock('layout/cptitle/documentation');
            }

            if (_t(strtoupper($gadget).'_ADMIN_MESSAGE') != strtoupper($gadget).'_ADMIN_MESSAGE') {
                $this->_Template->SetBlock('layout/cptitle/description');
                $this->_Template->SetVariable('title-desc', _t(strtoupper($gadget) . '_ADMIN_MESSAGE'));
                $this->_Template->ParseBlock('layout/cptitle/description');
            }
            $this->_Template->ParseBlock('layout/cptitle');
        }

        if ($GLOBALS['app']->Registry->fetch('site_status', 'Settings') == 'disabled') {
            $this->_Template->SetBlock('layout/warning');
            $this->_Template->SetVariable('warning', _t('CONTROLPANEL_OFFLINE_WARNING'));
            $this->_Template->ParseBlock('layout/warning');
        }

        $responses = $GLOBALS['app']->Session->PopLastResponse();
        if ($responses) {
            foreach ($responses as $msg_id => $response) {
                $this->_Template->SetBlock('layout/msgbox');
                $this->_Template->SetVariable('msg-css', $response['css']);
                $this->_Template->SetVariable('msg-txt', $response['message']);
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
     * Assign the right head's title
     *
     * @access  public
     */
    function PutTitle()
    {
        if (!empty($this->_Title)) {
            $pageTitle = array($this->_Title, $GLOBALS['app']->Registry->fetch('site_name', 'Settings'));
        } else {
            $slogan = $GLOBALS['app']->Registry->fetch('site_slogan', 'Settings');
            $pageTitle   = array();
            $pageTitle[] = $GLOBALS['app']->Registry->fetch('site_name', 'Settings');
            if (!empty($slogan)) {
                $pageTitle[] = $slogan;
            }
        }
        $pageTitle = implode(' ' . $GLOBALS['app']->Registry->fetch('title_separator', 'Settings').' ', $pageTitle);
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
            $this->_Description = $GLOBALS['app']->Registry->fetch('site_description', 'Settings');
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
            $keywords = array_map(array('Jaws_UTF8','trim'), explode(',', $keywords));
            $this->_Keywords = array_merge($this->_Keywords, $keywords);
        }
    }

    /**
     * Assign the site keywords
     *
     * @access  public
     */
    function PutMetaKeywords()
    {
        $this->_Template->ResetVariable('site-keywords',
                                        strip_tags(implode(', ', $this->_Keywords)),
                                        'layout');
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
            return $this->_Model->GetLayoutItems(true);
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
    function Populate(&$goGadget, $is_index = false, $req_result = '', $onlyRequestedAction = false)
    {
        $default_acl = (JAWS_SCRIPT == 'index')? 'default' : 'default_admin';
        $this->_RequestedGadget = empty($goGadget)? '': $goGadget->gadget->GetGadget();
        $this->_RequestedAction = empty($goGadget)? '': $goGadget->GetAction();
        $items = $this->GetLayoutItems();
        if (!Jaws_Error::IsError($items)) {
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
                    $this->_SectionAttributes = $this->_Template->GetCurrentBlockAttributes();
                    $currentContent = $this->_Template->GetCurrentBlockContent();
                    $this->_Template->SetCurrentBlockContent('{ELEMENT}');
                    $contentString  = '';
                }

                $content = '';
                if ($item['gadget'] == '[REQUESTEDGADGET]') {
                    $content = $req_result;
                } elseif (!$onlyRequestedAction) {
                    if ($this->IsDisplayable($this->_RequestedGadget,
                                             $this->_RequestedAction,
                                             $item['display_when'],
                                             $is_index))
                    {
                        if ($GLOBALS['app']->Session->GetPermission($item['gadget'], $default_acl)) {
                            $content = $this->PutGadget($item['gadget'],
                                                        $item['gadget_action'],
                                                        unserialize($item['action_params']),
                                                        $item['action_filename']);
                        }
                    }
                }

                if (!empty($content)) {
                    $contentString .= str_replace('{ELEMENT}', $content, $currentContent)."\n\n\n";
                }
            }
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
        $enabled = $GLOBALS['app']->Registry->fetch('enabled', $gadget);
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

        if (JAWS_SCRIPT == 'admin') {
            $this->AddHeadLink('gadgets/'.$gadget.'/resources/style.css',
                               'stylesheet',
                               'text/css',
                               '',
                               null,
                               true);
            $goGadget = $GLOBALS['app']->loadGadget($gadget, 'AdminHTML');
            if (!Jaws_Error::isError($goGadget)) {
                $goGadget->SetAction($action);
                $output = $goGadget->Execute();
            }
        } else {
            if (empty($filename)) {
                // DEPRECATED: will be removed after all jaws official gadget converted
                $goGadget = $GLOBALS['app']->loadGadget($gadget, 'LayoutHTML');
            } else {
                $goGadget = $GLOBALS['app']->loadGadget($gadget, 'HTML', $filename);
            }
            if (!Jaws_Error::isError($goGadget)) {
                if (method_exists($goGadget, $action)) {
                    if (is_array($params)) {
                        $output = call_user_func_array(array($goGadget, $action), $params);
                    } else {
                        $output = $goGadget->$action($params);
                    }
                } else {
                    $GLOBALS['log']->Log(JAWS_LOG_ERROR, "Action $action in $gadget's HTML dosn't exist.");
                }
            } else {
                $GLOBALS['log']->Log(
                    JAWS_LOG_ERROR,
                    "$gadget is missing the HTML. Jaws can't execute Layout " .
                    "actions if the file doesn't exists"
                );
            }
        }

        if (Jaws_Error::isError($output)) {
            $GLOBALS['log']->Log(JAWS_LOG_ERROR, 'In '.$gadget.'::'.$action.','.$output->GetMessage());
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

        $this->AddHeadMeta('generator', 'Jaws 0.8 (http://www.jaws-project.com)');
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
                $content = gzencode($content, COMPRESS_LEVEL, FORCE_GZIP);
                header('Content-Length: '.strlen($content));
                header('Content-Encoding: '.(strpos($GLOBALS['app']->GetBrowserEncoding(), 'x-gzip')!== false? 'x-gzip' : 'gzip'));
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
     * @param   string  $href  The HREF
     * @param   string  $rel   The REL that will be associated
     * @param   string  $type  Type of HeadLink
     * @param   bool    $checkInTheme Check if resource exists in the current theme directory
     * @param   string  $title Title of the HeadLink
     * @param   string  $media Media type, screen, print or such
     * @return  array   array include head link information
     */
    function AddHeadLink($href, $rel = 'stylesheet', $type = 'text/css', $title = '',
                         $direction = null, $checkInTheme = false, $media = '')
    {
        $fileName = basename($href);
        $fileExt  = strrchr($fileName, '.');
        $fileName = substr($fileName, 0, -strlen($fileExt));
        if (substr($href, 0, 1) == '/') {
            $path = substr($href , 1, - strlen($fileName.$fileExt));
        } else {
            $path = substr($href , 0, - strlen($fileName.$fileExt));
        }

        $brow = $GLOBALS['app']->GetBrowserFlag();
        $brow = empty($brow)? '' : '.'.$brow;

        $prefix = '.' . strtolower(empty($direction) ? _t('GLOBAL_LANG_DIRECTION') : $direction);
        if ($prefix !== '.rtl') {
            $prefix = '';
        }

        // First we try to load the css files from the theme dir.
        if ($checkInTheme) {
            $theme = $GLOBALS['app']->GetTheme();
            $gadget = str_replace(array('gadgets/', 'resources/'), '', $path);
            $href = $theme['path'] . $gadget . $fileName . $prefix . $fileExt;
            if (!empty($prefix) && !file_exists($href)) {
                $href = JAWS_PATH . 'gadgets/' . $gadget . 'resources/' . $fileName . $prefix . $fileExt;
                if (!file_exists($href)) {
                    $href = $theme['path'] . $gadget . $fileName . $fileExt;
                }
            }

            if (!file_exists($href)) {
                $href = JAWS_PATH . 'gadgets/' . $gadget . 'resources/' . $fileName . $fileExt;
            }
            $href = str_replace(JAWS_PATH, '', $href);
        } else {
            $href = $path . $fileName . $prefix . $fileExt;
            if (!empty($prefix) && !@file_exists($href)) {
                $href = $path . $fileName . $fileExt;
            }
        }

        $hLinks[] = array(
            'href'  => $href,
            'rel'   => $rel,
            'type'  => $type,
            'title' => $title,
            'media' => $media,
        );
        $this->_HeadLink[] = $hLinks[0];

        $brow_href = substr_replace($href, $brow, strrpos($href, '.'), 0);
        if (!empty($brow) && @file_exists($brow_href)) {
            $hLinks[] = array(
                'href'  => $brow_href,
                'rel'   => $rel,
                'type'  => $type,
                'title' => $title,
                'media' => $media,
            );
            $this->_HeadLink[] = $hLinks[1];
        }

        return $hLinks;
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

    /**
     * Get Requested gadget
     */
    function GetRequestedGadget()
    {
        return $this->_RequestedGadget;
    }

    /**
     * Get Requested action 
     */
    function GetRequestedAction()
    {
        return $this->_RequestedAction;
    }

}