<?php
/**
 * Jaws URL Mapping
 *
 * @category   Application
 * @package    Core
 * @author     Jonathan Hernandez  <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_URLMapping
{
    /**
     * Model that will be used to get data
     *
     * @var    UrlMapperModel
     * @access  private
     */
    var $_Model;

    var $_map = array();
    var $_delimiter = '@';
    var $_enabled;
    var $_use_file;
    var $_use_rewrite;
    var $_custom_precedence;
    var $_restrict_multimap;
    var $_use_aliases;
    var $_extension;

    /**
     * Constructor
     * Initializes the map, just pass null to a param if you want
     * to use the default values
     *
     * @param   bool    $enabled        When true uses maps
     * @param   bool    $use_file       When true it uses maps files
     * @param   bool    $use_rewrite    Set to true if you're using
     *                                  mod_rewrite (don't show ? in url)
     * @param   string  $use_aliases    When true it parses aliases in each 'Parse' request
     * @param   string  $extension      Extension URL maps should append or parse
     * @access  public
     */
    function Jaws_URLMapping($enabled = null, $use_rewrite = null, $use_aliases = null, $extension = null)
    {
        if ($enabled === null) {
            $enabled = ($GLOBALS['app']->Registry->Get('/map/enabled') == 'true');
        }

        if ($use_rewrite === null) {
            $use_rewrite = ($GLOBALS['app']->Registry->Get('/map/use_rewrite') == 'true');
        }

        if ($use_aliases === null) {
            $use_aliases = ($GLOBALS['app']->Registry->Get('/map/use_aliases') == 'true');
        }

        if ($extension === null) {
            $extension = $GLOBALS['app']->Registry->Get('/map/extensions');
        }

        $this->_enabled = $enabled && strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'iis') === false;
        $this->_use_rewrite       = $use_rewrite;
        $this->_use_aliases       = $use_aliases;
        $this->_custom_precedence = $GLOBALS['app']->Registry->Get('/map/custom_precedence') == 'true';
        $this->_restrict_multimap = $GLOBALS['app']->Registry->Get('/map/restrict_multimap') == 'true';
        if (!empty($extension) && $extension{0} != '.') {
            $extension = '.'.$extension;
        }
        $this->_extension = $extension;

        $this->_Model = $GLOBALS['app']->loadGadget('UrlMapper', 'Model');
        if (Jaws_Error::isError($this->_Model)) {
            Jaws_Error::Fatal($this->_Model->getMessage());
        }
    }

    /**
     * Resets the map
     *
     * @access  public
     */
    function Reset()
    {
        $this->_map = array();
    }

    /**
     * Adds a map
     * Deprecated: in next major version will be removed
     *
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   string  $map        Map (e.g. '/blog/view/{id}')
     * @param   string  $extension  Extension of mapped utl (e.g. html, xml, ....)
     * @param   array   $reqs       Array with the validation for each var
     * @param   array   $extraparms Array with the extra params with its default values
     * @param   bool    $custom Is it a custom map? (defined by user)
     * @access  public
     */
    function Connect($gadget, $action, $map, $extension = '', $reqs = null, $extraparams = null)
    {
        $GLOBALS['maps'][] = array($action, $map, $extension, $reqs);
    }

    /**
     * Loads the maps
     *
     * @access  public
     */
    function Load()
    {
        if ($this->_enabled) {
            $maps = $this->_Model->GetMaps();
            if (Jaws_Error::IsError($maps)) {
                return false;
            }

            foreach ($maps as $map) {
                $this->_map[$map['gadget']][$map['action']][] = array(
                                    'map'       => $map['map'],
                                    'params'    => null,
                                    'regexp'    => $map['regexp'],
                                    'extension' => $map['extension'],
                                    'custom_map'       => $map['custom_map'],
                                    'custom_regexp'    => $map['custom_regexp'],
                                    'custom_extension' => $map['custom_extension'],
                );
            }
        }
    }

    /**
     * Parses a QUERY URI and if its valid it extracts the values from
     * it and creates $_GET variables for each value.
     *
     * @param   string  $path   Query URI
     */
    function Parse($path = '')
    {
        if (!$this->_enabled && !is_array($this->_map)) {
            return false;
        }

        if (empty($path)) {
            $path = $this->getPathInfo();
        } elseif (strpos($path, 'http') !== false) {
            //prepare it manually
            $strPos = stripos($path, BASE_SCRIPT);
            if ($strPos != false) {
                $strPos = $strPos + strlen(BASE_SCRIPT);
                $path   = substr($path, $strPos);
            }
        }

        $path = rawurldecode($path);
        //If it has a slash at the start or end, remove it
        $path = trim($path, '/');

        //Moment.. first check if we are running on aliases_mode
        if ($this->_use_aliases && $realPath = $this->_Model->GetAliasPath($path)) {
            $path = str_ireplace(BASE_SCRIPT, '', $realPath);
        }

        //If no path info is given but count($_POST) > 0?
        if (empty($path) && count($_POST) > 0) {
            return true;
        }

        if (strpos($path, '=') !== false) {
            return true;
        }

        $request =& Jaws_Request::getInstance();
        //Lets check HTTP headers to see if user is trying to login
        if ($request->get('gadget', 'post') == 'ControlPanel' && $request->get('action', 'post') == 'Login') {
            $request->set('get', 'gadget', 'ControlPanel');
            $request->set('get', 'action', 'Login');
            return true;
        }

        $params = explode('/', $path);
        $path = implode('/', array_map('rawurlencode', $params));
        $matched_but_ignored = false;
        foreach ($this->_map as $gadget => $actions) {
            foreach ($actions as $action => $maps) {
                foreach ($maps as $map) {
                    $use_custom = !$this->_custom_precedence;
                    $has_custom = !empty($map['custom_map']);
                    for ($i = 1; $i <= 2; $i++) {
                        $use_custom = !$use_custom;
                        if ($use_custom) {
                            if (!$has_custom) {
                                continue;
                            }

                            $route  = $map['custom_map'];
                            $regexp = $map['custom_regexp'];
                            $ext    = $map['custom_extension'];
                            $custom = true;
                        } else {
                            $route  = $map['map'];
                            $regexp = $map['regexp'];
                            $ext    = $map['extension'];
                            $custom = false;
                        }

                        $url = $path;
                        $ext = empty($ext)? $this->_extension : $ext;
                        if (substr($url, - strlen($ext)) == $ext) {
                            $url = substr($url, 0, - strlen($ext));
                        }

                        if (preg_match($regexp, $url, $matches) == 1) {
                            if ($this->_restrict_multimap) {
                                if ($this->_custom_precedence && $has_custom && !$custom) {
                                    $matched_but_ignored = true;
                                    continue;
                                }
                                if (!$this->_custom_precedence && $custom) {
                                    $matched_but_ignored = true;
                                    continue;
                                }
                            }

                            // Gadget/Action
                            $request->set('get', 'gadget', $gadget);
                            $request->set('get', 'action', $action);

                            // Params
                            if (isset($map['params']) && is_array($map['params'])) {
                                foreach ($map['params'] as $key => $value) {
                                    $request->set('get', $key, $value);
                                }
                            }

                            // Variables
                            preg_match_all('#{(\w+)}#si', $route, $matches_vars);
                            if (is_array($matches_vars)) {
                                foreach ($matches_vars[1] as $key => $value) {
                                    $request->set('get', $value, rawurldecode($matches[$key + 1]));
                                }
                            }

                            return true;
                        }
                    } // for
                } //foreach maps
            } // foreach actions
        }

        if ($matched_but_ignored) {
            return false;
        }

        /**
         * Ok, no alias and map found, so lets parse the path directly.
         * The first rule: it should have at least one value (the gadget name)
         */
        $params_count = count($params);
        if ($params_count >= 1) {
            if (!$this->_restrict_multimap ||
                !$this->_enabled || !isset($params[1]) ||
                !isset($this->_map[$params[0]][$params[1]]))
            {
                $request->set('get', 'gadget', $params[0]);
                if (isset($params[1])) {
                    $request->set('get', 'action', $params[1]);
                }

                /**
                 * If we have a request via POST we should take those values, not the GET ones
                 * However, I'm not pretty sure if we should allow gadget and action being passed
                 * with /, cause officially (HTTP) you can't do that (params are passed via & not /)
                 *
                 * Next params following gadget/action should be parsed only if they come from a
                 * GET request
                 */
                //Ok, next values should be formed in pairs
                $params = array_slice($params, 2);
                $params_count = count($params);
                if ($params_count % 2 == 0) {
                    for ($i = 0; $i < $params_count; $i += 2) {
                        $request->set('get', $params[$i], $params[$i+1]);
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Returns the prefix URI
     *
     * @access  public
     * @param   string  $option   Can be:
     *
     *          - site_url: Will take what is in /config/url
     *          - uri_location: Will use the URI location (NO HTTP protocol defined)
     *          - nothing: Use nothing
     * @return  string URI prefix
     */
    function GetURIPrefix($option)
    {
        static $site_url;

        switch($option) {
        case 'site_url':
            if (isset($site_url)) {
                return $site_url;
            }
            $site_url = $GLOBALS['app']->getSiteURL('/');
            return $site_url;
            break;
        case 'uri_location':
            return $GLOBALS['app']->GetURILocation();
            break;
        }
        return '';
    }

    /**
     * Does the reverse stuff for an URL map. It gets all the params i
     * as an array and converts all the stuff to an URL map
     *
     * @access  public
     * @param   string  $gadget   Gadget's name
     * @param   string  $action   Gadget's action name
     * @param   array   $params   Params that the URL map requires
     * @param   bool    $useExt   Append the extension? (if there's)
     * @param   mixed   URIPrefix Prefix to use: site_url (config/url), uri_location or false for nothing
     * @return  string  The real URL map (aka jaws permalink)
     */
    function GetURLFor($gadget, $action='', $params = null, $useExt = true, $URIPrefix = false)
    {
        if ($this->_enabled && isset($this->_map[$gadget][$action])) {
            foreach ($this->_map[$gadget][$action] as $map) {
                if ($this->_custom_precedence && !empty($map['custom_map'])) {
                    $url = $map['custom_map'];
                    $ext = $map['custom_extension'];
                } else {
                    $url = $map['map'];
                    $ext = $map['extension'];
                }
                if (is_array($params)) {
                    foreach ($params as $key => $value) {
                        $value = implode('/', array_map('rawurlencode', explode('/', $value)));
                        $url = str_replace('{' . $key . '}', $value, $url);
                    }
                }

                if (!preg_match('#{\w+}#si', $url)) {
                    if (!$this->_use_rewrite) {
                        $url = 'index.php/' . $url;
                    }
                    if ($useExt) {
                        $url .= empty($ext)? $this->_extension : $ext;
                    }
                    break;
                }
            }

            return $this->GetURIPrefix($URIPrefix) . $url;
        }

        if ($this->_use_rewrite) {
            $url = $gadget . '/'. $action;
        } elseif (!$this->_enabled) {
            $url = 'index.php?' .$gadget . '/'. $action;
        } else {
            $url = 'index.php/' .$gadget . '/'. $action;
        }
        if (is_array($params)) {
            //Params should be in pairs
            foreach ($params as $key => $value) {
                $value = implode('/', array_map('rawurlencode', explode('/', $value)));
                $url.= '/' . $key . '/' . $value;
            }
        }

        return $this->GetURIPrefix($URIPrefix) . $url;
    }

    /**
     * Returns the PATH_INFO or simulates it
     *
     * @access  private
     * @return  string   PATH_INFO (empty or with a trailing dash)
     */
    function getPathInfo()
    {
        if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
            $uri = $_SERVER['PHP_SELF'] . '?' .$_SERVER['QUERY_STRING'];
        } else {
            $uri = '';
        }

        if (!empty($uri)) {
            if (!$this->_use_rewrite) {
                $dotPosition = stripos($uri, BASE_SCRIPT);
                if ($dotPosition !== false) {
                    $pathInfo = substr($uri, $dotPosition + strlen(BASE_SCRIPT));
                } else {
                    $qsnPosition = stripos($uri, '?');
                    if ($qsnPosition !== false) {
                        $pathInfo = substr($uri, $qsnPosition);
                    }
                }
            }

            if (!isset($pathInfo)) {
                $base_uri = $GLOBALS['app']->GetSiteURL('', true);
                if ($base_uri == substr($uri, 0, strlen($base_uri))) {
                    $pathInfo = substr($uri, strlen($base_uri));
                }
            }
        }

        $pathInfo = isset($pathInfo)? ltrim((string)$pathInfo, '/?') : '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $pathInfo == BASE_SCRIPT) {
            $pathInfo = '';
        }

        return $pathInfo;
    }

}