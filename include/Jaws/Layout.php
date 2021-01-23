<?php
/**
 * Class to manage Jaws Layout
 *
 * @category   Layout
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Layout
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * Template that will be used to print the data
     *
     * @var    Jaws_Template
     * @access  private
     */
    var $_Template;

    /**
     * Array that will have the meta/links/scripts tags
     *
     * @var     array   $extraTags
     * @access  private
     */
    private $extraTags = array(
        'metas'   => array('tag' => 'meta',   'single' => true,  'elements' => array()),
        'links'   => array('tag' => 'link',   'single' => true,  'elements' => array()),
        'scripts' => array('tag' => 'script', 'single' => false, 'elements' => array()),
    );

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
     * Layout file name
     *
     * @access  private
     * @var     string
     */
    private $layout_file = 'Layout';

    /**
     * Layout user name
     *
     * @access  private
     * @var     int
     */
    private $layout_user = 0;

    /**
     * Site attributes
     *
     * @access  private
     * @var     array
     */
    private $attributes = array();

    /**
     * Site modified attributes 
     *
     * @access  private
     * @var     array
     */
    private $site_modified_attributes = array();

    /**
     * JavaScript variables
     *
     * @access  private
     * @var     array
     */
    private $variables = array();

    /**
     * Loaded gadgets in layout
     *
     * @access  private
     * @var     array
     */
    private $loaded_layout_gadgets = array();

    /**
     * Initializes the Layout
     *
     * @access  public
     */
    function __construct()
    {
        $this->app = Jaws::getInstance();

        // fetch all registry keys related to site attributes
        $this->attributes = $this->app->registry->fetchAll('Settings', false);
        //parse default site keywords
        $this->attributes['site_keywords'] = array_map(
            'Jaws_UTF8::trim',
            array_filter(explode(',', $this->attributes['site_keywords']))
        );
        $this->attributes['admin_script'] = $this->attributes['admin_script'] ?: 'admin.php';

        if (!array_key_exists('buildnumber', $this->attributes)) {
            $this->attributes['buildnumber'] = date('YmdG');
        }

        // set default site language
        $this->_Languages[] = $this->app->GetLanguage();
        $this->app->define('', 'loadingMessage', Jaws::t('LOADING'));
        $this->app->define('', 'reloadMessage', Jaws::t('RELOAD_MESSAGE'));
        $this->app->define('', 'logged', (bool)$this->app->session->user->logged);
        $this->app->define(
            '',
            'service_worker_enabled',
            @$this->attributes['service_worker_enabled']
        );
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
           (JAWS_SCRIPT != 'admin' || $this->app->session->user->logged) &&
           !$this->app->session->user->superadmin
        ) {
            $data = Jaws_HTTPError::Get(503);
            terminate($data, 503);
        }

        $favicon = $this->attributes['site_favicon'];
        if (!empty($favicon)) {
            $mimes = array(
                'svg' => 'image/svg',
                'png' => 'image/png',
                'ico' => 'image/vnd.microsoft.icon',
                'gif' => 'image/gif',
                'jpg' => 'image/jpeg'
            );
            $ext = pathinfo(basename($favicon), PATHINFO_EXTENSION);
            if (isset($mimes[$ext])) {
                $this->addLink(array('href' => $favicon, 'type' => $mimes[$ext], 'rel' => 'icon'));
            }
        }

        $loadFromTheme = false;
        if (empty($layout_path)) {
            $theme = $this->app->GetTheme();
            if (!$theme['exists']) {
                Jaws_Error::Fatal('Theme '. $theme['name']. ' doesn\'t exists.');
            }

            $loadFromTheme = true;
            if (empty($layout_file)) {
                $layout_user = 0;
                $layout_type = (int)Jaws_Gadget::getInstance('Layout')->session->layout_type;
                $mainRequestGadget = $this->app->mainRequest['gadget'];
                $mainRequestAction = $this->app->mainRequest['action'];

                try {
                    // layout is global? 
                    if (empty($layout_type)) {
                        // select layout file in exception part
                        throw new Exception();
                    }

                    // load users gadget
                    $usersGadget = Jaws_Gadget::getInstance('Users');
                    // index/first page?
                    if ($this->app->mainIndex) {
                        if ($usersGadget->gadget->GetPermission('AccessUserLayout') &&
                            @is_file($theme['path']. 'Index.0.html')
                        ) {
                            // user index/first/dashboard page layout
                            $layout_file = 'Index.0.html';
                            $layout_user = (int)$this->app->session->user->id;
                        } elseif ($usersGadget->gadget->GetPermission('AccessUsersLayout') &&
                            @is_file($theme['path']. 'Index.1.html')
                        ) {
                            // logged users common index/first page layout
                            $layout_file = 'Index.1.html';
                        } else {
                            // select layout file in exception part
                            throw new Exception();
                        }
                    } else {
                        // nested pages(not index/first)
                        if (!$usersGadget->gadget->GetPermission('AccessUsersLayout')) {
                            // select layout file in exception part
                            throw new Exception();
                        }

                        // gadget/action layout for logged users
                        if (@is_file($theme['path']. "$mainRequestGadget.$mainRequestAction.1.html")) {
                            $layout_file = "$mainRequestGadget.$mainRequestAction.1.html";
                        } elseif (@is_file($theme['path']. "$mainRequestGadget.1.html")) {
                            $layout_file = "$mainRequestGadget.1.html";
                        } elseif (@is_file($theme['path']. 'Layout.1.html')) {
                            // logged users common nested pages layout
                            $layout_file = 'Layout.1.html';
                        } else {
                            // select layout file in exception part
                            throw new Exception();
                        }
                    }
                } catch (Exception $e) {
                    // index/first page?
                    if ($this->app->mainIndex) {
                        if (@is_file($theme['path']. 'Index.html')) {
                            // index/first page layout
                            $layout_file = 'Index.html';
                        } else {
                            // default pages layout
                            $layout_file = 'Layout.html';
                        }
                    } else {
                        // gadget/action layout
                        if (@is_file($theme['path']. "$mainRequestGadget.$mainRequestAction.html")) {
                            $layout_file = "$mainRequestGadget.$mainRequestAction.html";
                        } elseif (@is_file($theme['path']. "$mainRequestGadget.html")) {
                            $layout_file = "$mainRequestGadget.html";
                        } else {
                            // default layout
                            $layout_file = 'Layout.html';
                        }
                    }
                }

                $this->layout_user = $layout_user;
                $this->layout_file = basename($layout_file, '.html');
            }
        }

        $this->_Template = new Jaws_Template($loadFromTheme);
        $this->_Template->Load($layout_file, $layout_path);
        $this->_Template->SetBlock('layout');

        $direction = Jaws::t('LANG_DIRECTION');
        $dir  = $direction == 'rtl' ? ".$direction" : '';
        $browser  = $this->app->GetBrowserFlag();
        $browser  = empty($browser)? '' : ".$browser";
        $base_url = $this->app->GetSiteURL('/');

        $this->_Template->SetVariable('base_url', $base_url);
        $this->_Template->SetVariable('skip_to_content', Jaws::t('SKIP_TO_CONTENT'));
        $this->_Template->SetVariable('.dir', $dir);
        $this->_Template->SetVariable('.browser', $browser);
        $this->_Template->SetVariable('site-url', $base_url);
        $this->_Template->SetVariable('site-direction', $direction);
        $this->_Template->SetVariable('admin-script',   $this->attributes['admin_script']);
        $this->_Template->SetVariable('site-name',      $this->attributes['site_name']);
        $this->_Template->SetVariable('site-slogan',    $this->attributes['site_slogan']);
        $this->_Template->SetVariable('site-comment',   $this->attributes['site_comment']);
        $this->_Template->SetVariable('site-author',    $this->attributes['site_author']);
        $this->_Template->SetVariable('site-license',   $this->attributes['site_license']);
        $this->_Template->SetVariable('site-copyright', $this->attributes['site_copyright']);
        $this->_Template->SetVariable('site-buildnumber', $this->attributes['buildnumber']);
        $cMetas = @unserialize($this->attributes['site_custom_meta']);
        if (!empty($cMetas)) {
            foreach ($cMetas as $cMeta) {
                $this->addMeta(
                    array(
                        'name'    => $cMeta[0],
                        'content' => $cMeta[1]
                    )
                );
            }
        }

        $this->_Template->SetVariable('encoding', 'utf-8');
    }

    /**
     * Gets current layout name
     *
     * @access  public
     * @return  string  Layout name
     */
    function GetLayoutName()
    {
        return $this->layout_file;
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
     * Set site-attributes
     *
     * @access  public
     * @param   array   $attributes     Site Attributes
     * @return  void
     */
    function setAttributes($attributes)
    {
        $this->site_modified_attributes = array_merge($this->site_modified_attributes, $attributes);
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

            return $layoutModel->GetLayoutItems($this->layout_file, $this->layout_user, true);
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
                    $item['gadget'] = $this->app->mainRequest['gadget'];
                    $item['action'] = $this->app->mainRequest['action'];
                    $item['params'] = array();
                    $content = $req_result;
                } elseif (!$onlyMainAction) {
                    if ($this->IsDisplayable($this->app->mainRequest['gadget'],
                                             $this->app->mainRequest['action'],
                                             $item['when'],
                                             $this->app->mainIndex))
                    {
                        if ($this->app->session->GetPermission($item['gadget'], $default_acl)) {
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

                $this->_Template->SetVariable('action_mode', strtolower($this->app->requestedActionMode));
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
            $GLOBALS['log']->Log(JAWS_NOTICE, "Gadget $gadget is not enabled");
            return $output;
        }

        if (!Jaws_Gadget::IsGadgetUpdated($gadget)) {
            $GLOBALS['log']->Log(
                JAWS_NOTICE,
                'Trying to populate '. $gadget.
                ' in layout, but looks that it is not installed/upgraded'
            );
            return $output;
        }

        $this->app->http_response_code(200);
        $output = Jaws_Gadget::getInstance($gadget)
            ->action
            ->load()
            ->Execute($action, $params, $section, ACTION_MODE_LAYOUT);
        if (Jaws_Error::isError($output)) {
            $GLOBALS['log']->Log(JAWS_ERROR, 'In '.$gadget.'::'.$action.','.$output->GetMessage());
            $output = '';
        } elseif ($this->app->http_response_code() !== 200) {
            $output = '';
        } else {
            $this->loaded_layout_gadgets[$gadget] = true;
        }

        return $output;
    }

    /**
     * Preparing initialize script
     *
     * @access  public
     * @return  string  Initialize script
     */
    function initializeScript()
    {
        $result = '';
        $allDefines = $this->app->defines();
        foreach ($allDefines as $component => $defines) {
            if (empty($component)) {
                $jsObj = 'jaws';
                $actions = array();
            } else {
                $jsObj = "jaws.$component";
                $objGadget = Jaws_Gadget::getInstance($component);
                $actions = array_keys($objGadget->loaded_actions);
            }

            $result.= "\t$jsObj = {};\n";
            $result.= "\t$jsObj.Actions = ". '$.parseJSON(\''. json_encode($actions, JSON_HEX_APOS). '\');'. "\n";
            $result.= "\t$jsObj.Defines = ". '$.parseJSON(\''. json_encode($defines, JSON_HEX_APOS). '\');'. "\n";
        }

        unset($allDefines['']);
        $result.= "\tjaws.Gadgets = ". '$.parseJSON(\''. json_encode(array_keys($allDefines)). '\');';
        return $result;
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
        $this->addMeta(
            array(
                'name'    => 'generator',
                'content' => 'Jaws Project (http://jaws-project.com)'
            )
        );
        $use_rewrite = $this->app->registry->fetch('map_use_rewrite', 'UrlMapper') == 'true';
        $use_rewrite = $use_rewrite && (JAWS_SCRIPT == 'index');
        $this->addMeta(
            array(
                'name'    => 'application-name',
                'content' => ($use_rewrite? '' : BASE_SCRIPT). ':'.
                    $this->app->mainRequest['gadget']. ':'. $this->app->mainRequest['action']
            )
        );
        // add mandatory javascript links
        array_unshift(
            $this->extraTags['scripts']['elements'],
            array(
                'src'  => 'libraries/jquery/jquery.min.js?'. $this->attributes['buildnumber']
            ),
            array(
                'src'  => 'libraries/bootstrap.fuelux/js/bootstrap.fuelux.min.js?'. $this->attributes['buildnumber']
            ),
            array(
                'src'  => 'include/Jaws/Resources/Jaws.js?' . $this->attributes['buildnumber']
            ),
            array(
                'text' => $this->initializeScript()
            )
        );

        // add meta/link/script tags
        foreach ($this->extraTags as $block => $items) {
            if ($this->_Template->BlockExists("layout/$block")) {
                $tagsStr = '';
                foreach ($items['elements'] as $item) {
                    $inner = '';
                    $tagsStr.= "\n  <{$items['tag']} ";
                    foreach ($item as $attr => $value) {
                        if ($attr == 'text') {
                            $inner = $value;
                            continue;
                        }
                        $tagsStr.= "$attr=\"$value\" ";
                    }
                    $tagsStr.= '>';
                    if (!$items['single']) {
                        if (!empty($inner)) {
                            $tagsStr.= "\n{$inner}\n  ";
                        }
                        $tagsStr.= "</{$items['tag']}>";
                    }
                }
                if (!empty($tagsStr)) {
                    $this->_Template->SetBlock("layout/$block");
                    $this->_Template->SetVariable('elements', $tagsStr);
                    $this->_Template->ParseBlock("layout/$block");
                }
            }
        }

        if (JAWS_SCRIPT == 'index') {
            // set modified site attributes
            foreach ($this->site_modified_attributes as $key => $value) {
                $this->_Template->ResetVariable($key, $value, 'layout');
            }
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
            if ($this->app->GZipEnabled()) {
                $this->app->request->update('restype', 'gzip');
            }
            return $content;
        }
    }

    /**
     * Overloading magic method
     *
     * @access  private
     * @param   string  $method  Method name
     * @param   string  $params  Method parameters
     * @return  mixed   True otherwise Jaws_Error
     */
    function __call($method, $params)
    {
        switch ($method) {
            case 'addMeta':
                $this->extraTags['metas']['elements'][] = $params[0];
                break;

            case 'addLink':
                if (is_string($params[0])) {
                    $params[0] = array(
                        'href' => $params[0]. '?'. $this->attributes['buildnumber'],
                        'type' => 'text/css',
                        'rel'  => 'stylesheet'
                    );
                }
                $this->extraTags['links']['elements'][md5(serialize($params))] = $params[0];
                break;

            case 'addScript':
                if (is_string($params[0])) {
                    $params[0] = array(
                        'src'  => $params[0]. '?'. $this->attributes['buildnumber'],
                    );
                }
                $this->extraTags['scripts']['elements'][md5(serialize($params))] = $params[0];
                break;

            default:
                return Jaws_Error::raiseError(
                    "Call to undefined method Jaws_Layout::$method",
                    __FUNCTION__,
                    JAWS_ERROR_ERROR,
                    1
                );
        }
    }

}