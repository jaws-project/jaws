<?php
/**
 * Jaws URL Mapping
 *
 * @category   Application
 * @package    Core
 * @author     Jonathan Hernandez  <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_URLMapping
{
    /**
     * UrlMapper Maps Model
     *
     * @var     object
     * @access  private
     */
    private $_MapsModel;

    /**
     * UrlMapper Aliases Model
     *
     * @var     object
     * @access  private
     */
    private $_AliasesModel;

    /**
     * cashed maps
     *
     * @var     array
     * @access  private
     */
    private $_maps = array();

    /**
     * cashed actions maps
     *
     * @var     array
     * @access  private
     */
    private $_actions_maps = array();

    /**
     * URL mapping enabled?
     *
     * @var     bool
     * @access  private
     */
    private $_enabled;

    /**
     * Requested URI
     *
     * @var     string
     * @access  private
     */
    private $_request_uri = '';

    /**
     * URL rewriting enabled?
     *
     * @var     bool
     * @access  private
     */
    private $_use_rewrite;

    /**
     * custom precedence over original?
     *
     * @var     bool
     * @access  private
     */
    private $_custom_precedence;

    /**
     * restrict access to a resource by multi maps?
     *
     * @var     bool
     * @access  private
     */
    private $_restrict_multimap;

    /**
     * using url aliases?
     *
     * @var     bool
     * @access  private
     */
    private $_use_aliases;

    /**
     * Default url extension
     *
     * @var     string
     * @access  private
     */
    private $_extension;

    /**
     * Initializes the Jaws URL Mapping
     *
     * @access  public
     * @param   string  $request_uri    Requested URL
     * @return  bool    True on success, or False on failure
     */
    function Init($request_uri = '')
    {
        $urlMapper = Jaws_Gadget::getInstance('UrlMapper');
        if (Jaws_Error::isError($urlMapper)) {
            Jaws_Error::Fatal($urlMapper->getMessage());
        }

        $this->_MapsModel = Jaws_Gadget::getInstance('UrlMapper')->model->load('Maps');
        if (Jaws_Error::isError($this->_MapsModel)) {
            Jaws_Error::Fatal($this->_MapsModel->getMessage());
        }
        $this->_AliasesModel = Jaws_Gadget::getInstance('UrlMapper')->model->load('Aliases');
        if (Jaws_Error::isError($this->_AliasesModel)) {
            Jaws_Error::Fatal($this->_AliasesModel->getMessage());
        }

        // fetch all registry keys
        $regKeys = $urlMapper->registry->fetchAll();
        $extension = $regKeys['map_extensions'];
        $this->_enabled           = $regKeys['map_enabled'] == 'true';
        $this->_use_rewrite       = $regKeys['map_use_rewrite'] == 'true';
        $this->_use_aliases       = $regKeys['map_use_aliases'] == 'true';
        $this->_custom_precedence = $regKeys['map_custom_precedence'] == 'true';
        $this->_restrict_multimap = $regKeys['map_restrict_multimap'] == 'true';
        if (!empty($extension) && $extension{0} != '.') {
            $extension = '.'.$extension;
        }
        $this->_extension = $extension;

        if (empty($request_uri)) {
            // ?\d+$ for force browsers to update cached file e.g. (?12345)
            $this->_request_uri = preg_replace(
                array('/^index\.php[\/|\?]?/iu', '/\?\d+$/u'),
                '',
                Jaws_Utils::getRequestURL()
            );
        } elseif (strpos($request_uri, 'http') !== false) {
            //prepare it manually
            if (false !== $strPos = stripos($request_uri, BASE_SCRIPT)) {
                $strPos = $strPos + strlen(BASE_SCRIPT);
                $this->_request_uri = substr($request_uri, $strPos);
            }
        } else {
            $this->_request_uri = $request_uri;
        }

        // fetch apptype
        $params = explode('/', $this->_request_uri);
        if (false !== $apptype_key = array_search('apptype', $params)) {
            jaws()->request->update('apptype', $params[$apptype_key + 1], 'get');
            unset($params[$apptype_key], $params[$apptype_key+1]);
        }
        // decode url parts
        $this->_request_uri = implode('/', array_map('rawurldecode', $params));

        //Moment.. first check if we are running on aliases_mode
        if ($this->_use_aliases && $realURI = $this->_AliasesModel->GetAliasPath($this->_request_uri)) {
            $this->_request_uri = str_ireplace(BASE_SCRIPT, '', $realURI);
        }

        // load maps
        if ($this->_enabled) {
            $maps = $this->_MapsModel->GetMaps();
            if (Jaws_Error::IsError($maps)) {
                return false;
            }

            foreach ($maps as $map) {
                $this->_actions_maps[$map['gadget']][$map['action']][] = $map['map'];
                $this->_maps[$map['gadget']][$map['map']] = array(
                    'params'      => null,
                    'action'      => $map['action'],
                    'map'         => $map['map'],
                    'regexp'      => $map['regexp'],
                    'extension'   => $map['extension'],
                    'regexp_vars' => array_keys(unserialize($map['vars_regexps'])),
                    'custom_map'    => $map['custom_map'],
                    'custom_regexp' => $map['custom_regexp'],
                );
            }
        }

        return true;
    }

    /**
     * Parses a QUERY URI and if its valid it extracts the values from
     * it and creates $_GET variables for each value.
     *
     * @access  public
     * @return  bool    True on success, or False on failure
     */
    function Parse()
    {
        if (!$this->_enabled && !is_array($this->_maps)) {
            return false;
        }

        $request = Jaws_Request::getInstance();
        //If no path info is given but request method is post
        if (empty($this->_request_uri) && $_SERVER['REQUEST_METHOD'] == 'POST') {
            return true;
        }

        if (strpos($this->_request_uri, '=') !== false) {
            return true;
        }

        $params = explode('/', $this->_request_uri);
        $matched_but_ignored = false;
        $reqOptions = explode('.',  $this->_request_uri);
        $requestedURL = array_shift($reqOptions);
        if (count($reqOptions) % 2) {
            // adding real extension to request url
            $requestedURL = $requestedURL. '.' . array_pop($reqOptions);
        }

        foreach ($this->_maps as $gadget => $maps) {
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
                        $custom = true;
                    } else {
                        $route  = $map['map'];
                        $regexp = $map['regexp'];
                        $custom = false;
                    }

                    $url = $requestedURL;
                    $ext = ($map['extension'] == '.')? $this->_extension : $map['extension'];
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
                        $request->update('gadget', $gadget, 'get');
                        $request->update('action', $map['action'], 'get');
                        for ($i = 0; $i < count($reqOptions); $i=$i+2) {
                            $request->update(rawurldecode($reqOptions[$i]), rawurldecode($reqOptions[$i+1]));
                        }

                        // Params
                        if (isset($map['params']) && is_array($map['params'])) {
                            foreach ($map['params'] as $key => $value) {
                                $request->update($key, $value, 'get');
                            }
                        }

                        // Variables
                        preg_match_all('#{(\w+)}#si', $route, $matches_vars);
                        if (is_array($matches_vars)) {
                            array_shift($matches);
                            foreach ($matches as $key => $value) {
                                $request->update($matches_vars[1][$key], rawurldecode($value), 'get');
                            }
                        }

                        return true;
                    }
                } // for
            } //foreach maps
        } // foreach gadgets

        if ($matched_but_ignored) {
            return false;
        }

        /**
         * OK, no alias and map found, so lets parse the path directly.
         * The first rule: it should have at least one value (the gadget name)
         */
        $params_count = count($params);
        if ($params_count >= 1) {
            if (!$this->_restrict_multimap ||
                !$this->_enabled || !isset($params[1]) ||
                !isset($this->_actions_maps[$params[0]][$params[1]]))
            {
                $request->update('gadget', $params[0], 'get');
                if (isset($params[1])) {
                    $request->update('action', $params[1], 'get');
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
                        $request->update($params[$i], $params[$i+1], 'get');
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Does the reverse stuff for an URL map. It gets all the params i
     * as an array and converts all the stuff to an URL map
     *
     * @access  public
     * @param   string  $gadget     Gadget name
     * @param   string  $action     Action name
     * @param   array   $params     Parameters of action
     * @param   array   $options    URL options(restype, mode, ...)
     * @return  string  The real URL map (aka jaws permalink)
     */
    function GetURLFor($gadget, $action='', $params = array(), $options = array())
    {
        // absolute or relative URL
        $abs_url = isset($options['absolute'])? (bool)$options['absolute'] : false;
        // URL extension(true: default extension, false: disable extension, custom extension)
        $extension = isset($options['extension'])? $options['extension'] : true;
        unset($options['absolute'], $options['extension']);

        $params_vars = array_keys($params);
        if ($this->_enabled && isset($this->_actions_maps[$gadget][$action])) {
            foreach ($this->_actions_maps[$gadget][$action] as $map) {
                $map = $this->_maps[$gadget][$map];
                if ($this->_custom_precedence && !empty($map['custom_map'])) {
                    $url = $map['custom_map'];
                } else {
                    $url = $map['map'];
                }

                // all params variables must exist in regexp variables
                $not_exist_vars = array_diff($params_vars, $map['regexp_vars']);
                if (!empty($not_exist_vars)) {
                    continue;
                }

                // set map variables by params values
                foreach ($params as $key => $value) {
                    if (!is_null($value)) {
                        $value = implode('/', array_map('rawurlencode', explode('/', $value)));
                        // prevent encode comma
                        $value = str_replace('%2C', ',', $value);
                        $url = str_replace('{' . $key . '}', $value, $url);
                    }
                }

                // remove not fill optional part of map
                do {
                    $rpl_url = $url;
                    $url = preg_replace('$\[[[:alnum:]\./\-\_]*\{\w+\}[[:alnum:]\./\-\_]*\]$u', '', $url);
                } while ($rpl_url != $url);
                $url = str_replace(array('[', ']'), '', $url);

                if (!preg_match('#{\w+}#si', $url)) {
                    if (!$this->_use_rewrite) {
                        $url = 'index.php/' . $url;
                    }

                    if ($extension) {
                        if ($extension === true) {
                            $url.= ($map['extension'] == '.')? $this->_extension : $map['extension'];
                        } else {
                            $url.= $extension;
                        }
                    }

                    // preparing options
                    foreach ($options as $opKey => $opValue) {
                        $url.= rawurlencode($opKey). '.'. rawurlencode($opValue);
                    }

                    break;
                }
                $url = '';
            }

            if (!empty($url)) {
                return ($abs_url? $GLOBALS['app']->getSiteURL('/') : '') . $url;
            }
        }

        if ($this->_use_rewrite) {
            $url = $gadget . '/'. $action;
        } elseif (!$this->_enabled) {
            $url = 'index.php?' .$gadget . '/'. $action;
        } else {
            $url = 'index.php/' .$gadget . '/'. $action;
        }

        // // merging options and params
        $params = array_merge($params, $options);

        if (is_array($params)) {
            //Params should be in pairs
            foreach ($params as $key => $value) {
                $value = implode('/', array_map('rawurlencode', explode('/', $value)));
                $url.= '/' . $key . '/' . $value;
            }
        }

        return ($abs_url? $GLOBALS['app']->getSiteURL('/') : '') . $url;
    }

}