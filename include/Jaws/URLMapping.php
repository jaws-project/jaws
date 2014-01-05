<?php
/**
 * Jaws URL Mapping
 *
 * @category   Application
 * @package    Core
 * @author     Jonathan Hernandez  <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_URLMapping
{
    /**
     * Model that will be used to get data
     *
     * @var    UrlMapper Maps Model
     * @access  private
     */
    var $_MapsModel;

    /**
     * Model that will be used to get data
     *
     * @var    UrlMapper Aliases Model
     * @access  private
     */
    var $_AliasesModel;

    var $_map = array();
    var $_delimiter = '@';
    var $_enabled;
    var $_request_uri = '';
    var $_use_rewrite;
    var $_custom_precedence;
    var $_restrict_multimap;
    var $_use_aliases;
    var $_extension;

    /**
     * Initializes the Jaws URL Mapping
     *
     * @access  public
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
        $enabled = $regKeys['map_enabled'] == 'true';
        $extension = $regKeys['map_extensions'];
        $this->_enabled = $enabled && strpos(strtolower($_SERVER['SERVER_SOFTWARE']), 'iis') === false;
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

        //Moment.. first check if we are running on aliases_mode
        if ($this->_use_aliases && $realURI = $this->_AliasesModel->GetAliasPath($this->_request_uri)) {
            $this->_request_uri = str_ireplace(BASE_SCRIPT, '', $realURI);
        }

        $params = explode('/', $this->_request_uri);
        if (false !== $apptype_key = array_search('apptype', $params)) {
            jaws()->request->update('apptype', $params[$apptype_key + 1], 'get');
            unset($params[$apptype_key], $params[$apptype_key+1]);
        }
        $this->_request_uri = implode('/', array_map('rawurldecode', $params));

        // load maps
        if ($this->_enabled) {
            $maps = $this->_MapsModel->GetMaps();
            if (Jaws_Error::IsError($maps)) {
                return false;
            }

            foreach ($maps as $map) {
                $this->_map[$map['gadget']][$map['action']][] = array(
                                    'map'         => $map['map'],
                                    'params'      => null,
                                    'regexp'      => $map['regexp'],
                                    'extension'   => $map['extension'],
                                    'regexp_vars' => array_keys(unserialize($map['vars_regexps'])),
                                    'custom_map'       => $map['custom_map'],
                                    'custom_regexp'    => $map['custom_regexp'],
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
    function Parse()
    {
        if (!$this->_enabled && !is_array($this->_map)) {
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
                            $custom = true;
                        } else {
                            $route  = $map['map'];
                            $regexp = $map['regexp'];
                            $custom = false;
                        }

                        $url = $this->_request_uri;
                        $ext = $map['extension'];
                        $ext = ($ext == '.')? $this->_extension : $ext;
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
                            $request->update('action', $action, 'get');

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
     * @param   bool    $abs_url    Absolute or relative URL
     * @return  string  The real URL map (aka jaws permalink)
     */
    function GetURLFor($gadget, $action='', $params = array(), $abs_url = false)
    {
        $params_vars = array_keys($params);
        if ($this->_enabled && isset($this->_map[$gadget][$action])) {
            foreach ($this->_map[$gadget][$action] as $map) {
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
                    $value = implode('/', array_map('rawurlencode', explode('/', $value)));
                    $url = str_replace('{' . $key . '}', $value, $url);
                }

                // remove not fill optional part of map
                do {
                    $rpl_url = $url;
                    $url = preg_replace('$\[[[:alnum:]\./-]*{\w+}[[:alnum:]\./-]*\]$u', '', $url);
                } while ($rpl_url != $url);
                $url = str_replace(array('[', ']'), '', $url);

                if (!preg_match('#{\w+}#si', $url)) {
                    if (!$this->_use_rewrite) {
                        $url = 'index.php/' . $url;
                    }

                    $ext = $map['extension'];
                    $url.= ($ext == '.')? $this->_extension : $ext;
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