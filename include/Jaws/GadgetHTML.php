<?php
/**
 * Jaws Gadgets : HTML part
 *
 * @category   Gadget
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_GadgetHTML extends Jaws_Gadget
{
    /**
     * Are we running Ajax?
     *
     * @access  private
     * @var     bool
     */
    var $_usingAjax = false;

    /**
     * Constructor
     *
     * @access  public
     * @param   string $gadget Gadget's name(same as the filesystem name)
     * @return  void
     */
    function Jaws_GadgetHTML($gadget)
    {
        parent::Jaws_Gadget($gadget);
        if (APP_TYPE == 'web') {
            // Add ShowGadgetInfo action
            $this->StandaloneAction('ShowGadgetInfo','');

            // Add Ajax actions.
            $this->StandaloneAction('Ajax', '');
            $this->StandaloneAdminAction('Ajax', '');

            // Add _404 as normal action
            $this->NormalAction('_404');
        }
    }

    /**
     * Refactor Init, Jaws_GadgetHTML::Init() loads the Piwi stuff
     *
     * @access  public
     * @param   string $gadget Gadget's name(same as the filesystem name)
     * @return  void
     */
    function Init($gadget)
    {
        $this->Jaws_GadgetHTML($gadget);
    }

    /**
     * Adds a layout action
     *
     * @access  protected
     * @param   string  $action Action
     * @param   string  $name Action's name
     * @param   string  $description Action's description
     */
    function LayoutAction($action, $name, $description = null)
    {
        $this->AddAction($action, 'LayoutAction', $name, $description);
    }

    /**
     * Adds a standalone action
     *
     * @access  protected
     * @param   string  $action Action
     * @param   string  $name Action's name
     * @param   string  $description Action's description
     */
    function StandaloneAction($action, $name = null, $description = null)
    {
        $this->AddAction($action, 'StandaloneAction', $name, $description);
    }

    /**
     * Adds a standalone/admin action
     *
     * @access  protected
     * @param   string  $action Action
     * @param   string  $name Action's name
     * @param   string  $description Action's description
     */
    function StandaloneAdminAction($action, $name = null, $description = null)
    {
        $this->AddAction($action, 'StandaloneAdminAction', $name, $description);
    }

    /**
     * Verifies if action is a standalone
     *
     * @access  public
     * @param   string  $action to Verify
     * @return  bool    True if action is standalone, if not, returns false
     */
    function IsStandAlone($action)
    {
        if ($this->IsValidAction($action)) {
            return isset($this->_ValidAction['StandaloneAction'][$action]);
        }
        return false;
    }

    /**
     * Verifies if action is a standalone of controlpanel
     *
     * @access  public
     * @param   string  $action to Verify
     * @return  bool    True if action is standalone of the controlpanel if not, returns false
     */
    function IsStandAloneAdmin($action)
    {
        $actionmode = '';
        if ($this->IsValidAction($action)) {
            return isset($this->_ValidAction['StandaloneAdminAction'][$action]);
        }
        return false;
    }

    /**
     * Ajax Admin stuff
     * This method should be overridden by gadgets if a more complex operation
     * is required, and then called with an array of objects to be provided
     * to the client, like this:
     *
     * <code>
     * <?php
     * function Ajax()
     * {
     *     $objects = array();
     *     $objects[] = new GadgetAPI();
     *     $objects[] = new OtherAPI();
     *
     *     return parent::InitAjax($objects);
     * }
     * ?>
     * </code>
     *
     * @access  public
     */
    function Ajax()
    {
        $name = $this->GetName();
        require_once JAWS_PATH . 'include/Jaws/Ajax.php';

        if (JAWS_SCRIPT == 'admin') {
            $model = $GLOBALS['app']->LoadGadget($name, 'AdminModel');
            require_once JAWS_PATH.'gadgets/' . $name . '/AdminAjax.php';
            $ajaxClass = $name . 'AdminAjax';
        } else {
            $model = $GLOBALS['app']->LoadGadget($name, 'Model');
            require_once JAWS_PATH.'gadgets/' . $name . '/Ajax.php';
            $ajaxClass = $name . 'Ajax';
        }

        $objAjax = new $ajaxClass($model);
        $request =& Jaws_Request::getInstance();
        if (isset($_GET['client'])) {
            $this->InitAjax($objAjax);
        } else {
            $output = '';
            $method = $request->get('method', 'get');
            $params = $request->getAll('post');
            $output = call_user_func_array(array($objAjax, $method), $params);
            return Jaws_UTF8::json_encode($output);
        }
    }

    /**
     * Provides the Javascript interface for a gadget.
     *
     *
     * @access  public
     * @param   array   $objects    An array of objects to provide to the client.
     * @return  string  The reply.
     * @since   0.6
     */
    function InitAjax($object = null)
    {
        if (is_object($object)) {
            // Load the JPSpan library
            require_once JAWS_PATH . 'libraries/jpspan/JPSpan.php';
            require_once JAWS_PATH . 'libraries/jpspan/JPSpan/Server/PostOffice.php';

            // Create a server object, set the URL to submit to, and export object.
            $server = new JPSpan_Server_Postoffice();
            $server->setServerUrl(BASE_SCRIPT.'?gadget='.$this->_Name.'&action=Ajax');
            $server->addHandler($object);

            // Display the client code.
            define('JPSPAN_INCLUDE_COMPRESS', true);
            $client = $server->displayClient();

            header('Content-type: text/javascript; charset: UTF-8');
            header('Content-Type: application/x-javascript');
            header("Vary: Accept-Encoding");
            //2592000 = 30 * 24 * 3600
            header('Cache-Control: max-age=2592000, public, must-revalidate');
            //header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 2592000) . ' GMT');
            if ($GLOBALS['app']->GZipEnabled()) {
                $client = @gzencode($client, COMPRESS_LEVEL, FORCE_GZIP);
                header('Content-Length: '.strlen($client));
                header('Content-Encoding: '.(strpos($GLOBALS['app']->GetBrowserEncoding(), 'x-gzip')!== false? 'x-gzip' : 'gzip'));
            } else {
                header('Content-Length: '.strlen($client));
            }

            echo $client;
            exit;
        }

        // Yeah, so it's a hack.
        return "alert('The ".$this->GetName()." gadget does not provide a Javascript interface.')";
    }

    /**
     * Overloads Jaws_Gadget::IsValid. Difference: Checks that the gadget (HTML) file exists
     *
     * @access  public
     * @param   string  $gadget Gadget's Name
     * @return  bool    Returns true if the gadget is valid, otherwise will finish the execution
     */
    function IsValid($gadget)
    {
        // Check if file exists
        // Hack until we decide if $gadget.php will be a proxy file
        if (!file_exists(JAWS_PATH . 'gadgets/'.$gadget.'/HTML.php')) {
            Jaws_Error::Fatal('Gadget file doesn\'t exists');
        }

        parent::IsValid($gadget);
    }

    /**
     * Ajax the gadget adding the basic script links to build the interface
     *
     * @access  protected
     * @param   string  $file       Optional The gadget can require a special JS file,
     *                              it should be located under gadgets/$gadget/resources/$file
     * @param   string  $version    Optional File version
     */
    function AjaxMe($file = '', $version = '')
    {
        $this->_usingAjax = true;
        $name = $this->GetName();
        $GLOBALS['app']->Layout->AddScriptLink('include/Jaws/Ajax/Ajax.js');
        $GLOBALS['app']->Layout->AddScriptLink(BASE_SCRIPT.'?gadget='.
                                               $name.
                                               '&amp;action=Ajax&amp;client&amp;json');

        if (!empty($file)) {
            $GLOBALS['app']->Layout->AddScriptLink('gadgets/'.
                                                   $name.
                                                   '/resources/'.
                                                   $file.
                                                   (empty($version)? '' : "?$version"));
        }

        $config = array(
            'DATAGRID_PAGER_FIRSTACTION' => 'javascript: firstValues(); return false;',
            'DATAGRID_PAGER_PREVACTION'  => 'javascript: previousValues(); return false;',
            'DATAGRID_PAGER_NEXTACTION'  => 'javascript: nextValues(); return false;',
            'DATAGRID_PAGER_LASTACTION'  => 'javascript: lastValues(); return false;',
            'DATAGRID_DATA_ONLOADING'    => 'showWorkingNotification;',
            'DATAGRID_DATA_ONLOADED'     => 'hideWorkingNotification;',
        );
        Piwi::addExtraConf($config);
    }

    /**
     * Return the 404 message (page not found)
     *
     * @access  protected
     */
    function _404()
    {
        require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
        return Jaws_HTTPError::Get(404);
    }

    /**
     * Sets the browser's title (<title></title>)
     *
     * @access  public
     * @param   string  $title  Browser's title
     */
    function SetTitle($title)
    {
        //Set title in case we are no running on standalone..
        if (isset($GLOBALS['app']->Layout)) {
            $GLOBALS['app']->Layout->SetTitle($title);
        }
    }

    /**
     * Sets the browser's title (<title></title>)
     *
     * @access  public
     * @param   string  $title  Browser's title
     */
    function SetDescription($desc)
    {
        //Set description in case we are no running on standalone..
        if (isset($GLOBALS['app']->Layout)) {
            $GLOBALS['app']->Layout->SetDescription($desc);
        }
    }

    /**
     * Add keywords to meta keywords tag
     *
     * @access  public
     * @param   string  $keywords
     */
    function AddToMetaKeywords($keywords)
    {
        //Add keywords in case we are no running on standalone..
        if (isset($GLOBALS['app']->Layout)) {
            $GLOBALS['app']->Layout->AddToMetaKeywords($keywords);
        }
    }

    /**
     * Add a language to meta language tag
     *
     * @access  public
     * @param   string  $language  Language
     */
    function AddToMetaLanguages($language)
    {
        //Add language in case we are no running on standalone..
        if (isset($GLOBALS['app']->Layout)) {
            $GLOBALS['app']->Layout->AddToMetaLanguages($language);
        }
    }

    /**
     * Returns the state of usingAjax
     *
     * @access  public
     * @return  bool
     */
    function usingAjax()
    {
        return $this->_usingAjax;
    }

    /**
     * Search in map and return its url if found
     *
     * @access  protected
     * @param   string     $action    Gadget's action name
     * @param   array      $params    Params that the URL map requires
     * @param   array      $params    Params that the URL map requires
     * @param   bool       $useExt    Append the extension? (if there's)
     * @param   mixed      URIPrefix  Prefix to use: site_url (config/url), uri_location or false for nothing
     * @return  string     The mapped URL
     */
    function GetURLFor($action='', $params = null, $useExt = true, $URIPrefix = false)
    {
        return $GLOBALS['app']->Map->GetURLFor($this->_Name, $action, $params, $useExt, $URIPrefix);
    }

}