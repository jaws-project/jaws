<?php
require_once JAWS_PATH . 'gadgets/UrlMapper/Model.php';
/**
 * UrlMapper Core Gadget
 *
 * @category   GadgetModel
 * @package    UrlMapper
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapperAdminModel extends UrlMapperModel
{
    /**
     * Returns only the map route of a certain map
     *
     * @access  public
     * @param   int     $id Map's ID
     * @return  string  Map route
     */
    function GetMap($id)
    {
        $params = array();
        $params['id'] = $id;

        $sql = '
            SELECT
                [map], [regexp], [extension], [vars_regexps],
                [custom_map], [custom_regexp], [custom_extension], [order]
            FROM [[url_maps]]
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return $result;
    }

    /**
     * Returns only the map route of given params
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @param   string  $action Action name
     * @param   string  $map    Action map
     * @return  mixed   Map route or Jaws_Error on error
     */
    function GetMapByParams($gadget, $action, $map)
    {
        $params = array();
        $params['gadget'] = $gadget;
        $params['action'] = $action;
        $params['map']    = $map;

        $sql = '
            SELECT
                [id], [gadget], [map], [regexp], [extension], [vars_regexps],
                [custom_map], [custom_regexp], [custom_extension]
            FROM [[url_maps]]
            WHERE
                [gadget] = {gadget}
              AND
                [action] = {action}
              AND
                [map] = {map}';

        $result = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return $result;
    }

    /**
     * Returns maps of a certain gadget/action stored in DB
     *
     * @access  public
     * @param   string  $gadget   Gadget's name (FS name)
     * @param   string  $action   Gadget's action to use
     * @return  array   List of custom maps
     */
    function GetActionMaps($gadget, $action)
    {
        $params = array();
        $params['gadget'] = $gadget;
        $params['action'] = $action;

        $sql = '
            SELECT
                [id], [map], [extension]
            FROM [[url_maps]]
            WHERE
                [gadget] = {gadget}
              AND
                [action] = {action}
            ORDER BY [order] ASC';

        $result = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        return $result;
    }

    /**
     * Returns mapped actions of a certain gadget
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @return  array   List of actions
     */
    function GetGadgetActions($gadget)
    {
        $params = array();
        $params['gadget'] = $gadget;

        $sql = '
            SELECT [gadget], [action]
            FROM [[url_maps]]
            WHERE [gadget] = {gadget}
            GROUP BY [gadget], [action]
            ORDER BY [gadget], [action]';

        $result = $GLOBALS['db']->queryCol($sql, $params, null, 1);
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        return $result;
    }

    /**
     * Adds all of gadget maps
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function AddGadgetMaps($gadget)
    {
        $file = JAWS_PATH . 'gadgets/' . $gadget . '/Map.php';
        if (file_exists($file)) {
            // Deprecated: use $GLOBALS to fetch maps of old gadgets
            $maps = array();
            $GLOBALS['maps'] = array();
            include_once $file;
            if(!empty($GLOBALS['maps'])) {
                $maps = $GLOBALS['maps'];
            }
            foreach ($maps as $order => $map) {
                $vars_regexps = array();
                $vars_regexps = isset($map[3])? $map[3] : $vars_regexps;
                if (preg_match_all('#{(\w+)}#si', $map[1], $matches)) {
                    foreach ($matches[1] as $m) {
                        if (!isset($vars_regexps[$m])) {
                            $vars_regexps[$m] = '\w+';
                        }
                    }
                }

                $res = $this->AddMap($gadget,
                                     $map[0],
                                     $map[1],
                                     isset($map[2])? $map[2] : '',
                                     $vars_regexps,
                                     $order + 1);
                if (Jaws_Error::IsError($res)) {
                    return $res;
                }
            }

            unset($GLOBALS['maps']);
        }

        return true;
    }

    /**
     * Updates all of gadget maps
     *
     * @access  public
     * @param   string  $gadget  Gadget name
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function UpdateGadgetMaps($gadget)
    {
        $file = JAWS_PATH . 'gadgets/' . $gadget . '/Map.php';
        if (file_exists($file)) {
            // Deprecated: use $GLOBALS to fetch maps of old gadgets
            $maps = array();
            $GLOBALS['maps'] = array();
            include $file;
            if(!empty($GLOBALS['maps'])) {
                $maps = $GLOBALS['maps'];
            }

            $now = $GLOBALS['db']->Date();
            foreach ($maps as $order => $map) {
                $eMap = $this->GetMapByParams($gadget, $map[0], $map[1]);
                if (Jaws_Error::IsError($eMap)) {
                    return $eMap;
                }

                $vars_regexps = array();
                $vars_regexps = isset($map[3])? $map[3] : $vars_regexps;
                if (preg_match_all('#{(\w+)}#si', $map[1], $matches)) {
                    foreach ($matches[1] as $m) {
                        if (!isset($vars_regexps[$m])) {
                            $vars_regexps[$m] = '\w+';
                        }
                    }
                }

                if (empty($eMap)) {
                    $res = $this->AddMap($gadget,
                                         $map[0],
                                         $map[1],
                                         isset($map[2])? $map[2] : '',
                                         $vars_regexps,
                                         $order + 1,
                                         $now);
                    if (Jaws_Error::IsError($res)) {
                        return $res;
                    }
                } else {
                    $res = $this->UpdateMap($eMap['id'],
                                            $eMap['custom_map'],
                                            $eMap['custom_extension'],
                                            $vars_regexps,
                                            $order + 1,
                                            $map[1],
                                            isset($map[2])? $map[2] : '',
                                            $now);
                    if (Jaws_Error::IsError($res)) {
                        return $res;
                    }
                }
            }

            // remove outdated maps
            $res = $this->RemoveGadgetMaps($gadget, $now);
            if (Jaws_Error::IsError($res)) {
                return $res;
            }
        }

        return true;
    }

    /**
     * Gets regular expression to detect map
     *
     * @access  public
     * @param   string  $map            Map to use (foo/bar/{param}/{param2}...)
     * @param   array   $vars_regexps   Array of regexp validators
     * @return  string  Regular expression
     */
    function GetMapRegExp($map, $vars_regexps)
    {
        $regexp = str_replace('/', '\/', $map);
        if (!empty($regexp)) {
            // generate regular expression for optional part
            while(preg_match('@\[([^\]\[]+)\]@', $regexp)) {
                $regexp = preg_replace('@\[([^\]\[]+)\]@','(?:$1|)', $regexp);
            }

            if (is_array($vars_regexps) && !empty($vars_regexps)) {
                foreach ($vars_regexps as $k => $v) {
                    $regexp = str_replace('{' . $k . '}', '(' . $v . ')', $regexp);
                }
            }

            // Adding delimiter to regular expression
            $regexp = str_replace('@', '\\@', $regexp);
            $regexp = '@^' . $regexp . '$@';
        }

        return $regexp;
    }

    /**
     * Adds a new custom map
     *
     * @access  public
     * @param   string  $gadget         Gadget name (FS name)
     * @param   string  $action         Gadget action to use
     * @param   string  $map            Map to use (foo/bar/{param}/{param2}...)
     * @param   string  $extension      Extension of map
     * @param   array   $vars_regexps   Array of regexp validators
     * @param   int     $order          Sequence number of the map
     * @param   string  $time           Create/Update time
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function AddMap($gadget, $action, $map, $extension = '', $vars_regexps = null, $order = 0, $time = '')
    {
        //for compatible with old versions
        $extension = ($extension == 'index.php')? '' : $extension;
        if (!empty($extension) && $extension{0} != '.') {
            $extension = '.'.$extension;
        }

        if ($this->MapExists($gadget, $action, $map, $extension)) {
            return true;
        }

        // map's regular expression
        $regexp = $this->GetMapRegExp($map, $vars_regexps);

        $params = array();
        $params['gadget']    = $gadget;
        $params['action']    = $action;
        $params['map']       = $map;
        $params['regexp']    = $regexp;
        $params['extension'] = $extension;
        $params['vars_regexps'] = serialize($vars_regexps);
        $params['order']     = $order;
        $params['time']      = empty($time)? $GLOBALS['db']->Date() : $time;

        $sql = '
            INSERT INTO [[url_maps]]
                ([gadget], [action], [map], [regexp], [extension], [vars_regexps], [order],
                 [createtime], [updatetime])
            VALUES
                ({gadget}, {action}, {map}, {regexp}, {extension}, {vars_regexps}, {order},
                 {time}, {time})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('URLMAPPER_ERROR_MAP_NOT_ADDED'), _t('URLMAPPER_NAME'));
        }

        return true;
    }

    /**
     * Updates map route of the map
     *
     * @access  public
     * @param   int     $id                 Map ID
     * @param   string  $custom_map         Custom_map to use (foo/bar/{param}/{param2}...)
     * @param   string  $custom_extension   Extension of custom map
     * @param   array   $vars_regexps       Array of regexp validators
     * @param   int     $order              Sequence number of the map
     * @param   string  $map                Map to use (foo/bar/{param}/{param2}...)
     * @param   string  $extension          Extension of default map
     * @param   string  $time               Create/Update time
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function UpdateMap($id, $custom_map, $custom_extension, $vars_regexps, $order,
        $map = '', $map_extension = '', $time = '')
    {
        if (!empty($map_extension) && $map_extension{0} != '.') {
            $map_extension = '.'.$map_extension;
        }

        if (!empty($custom_extension) && $custom_extension{0} != '.') {
            $custom_extension = '.'.$custom_extension;
        }

        if (is_null($vars_regexps)) {
            $result = $this->GetMap($id);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            if (empty($result)) {
                return Jaws_Error::raiseError(_t('URLMAPPER_NO_MAPS'),
                                              __FUNCTION__);
            }

            $vars_regexps = unserialize($result['vars_regexps']);
        }

        $map_regexp = '';
        if (empty($map)) {
            $regexp    = '[regexp]';
            $extension = '[extension]';
        } else {
            $regexp    = '{regexp}';
            $extension = '{extension}';

            // default map's regular expression
            $map_regexp = $this->GetMapRegExp($map, $vars_regexps);

        }

        // custom map's regular expression
        $custom_regexp = $this->GetMapRegExp($custom_map, $vars_regexps);

        $params = array();
        $params['id'] = $id;
        $params['regexp']           = $map_regexp;
        $params['extension']        = $map_extension;
        $params['custom_map']       = $custom_map;
        $params['custom_regexp']    = $custom_regexp;
        $params['custom_extension'] = $custom_extension;
        $params['vars_regexps']     = serialize($vars_regexps);
        $params['order']            = $order;
        $params['time']             = empty($time)? $GLOBALS['db']->Date() : $time;

        $sql = "
            UPDATE [[url_maps]] SET
                [regexp]           = $regexp,
                [extension]        = $extension,
                [custom_map]       = {custom_map},
                [custom_regexp]    = {custom_regexp},
                [custom_extension] = {custom_extension},
                [vars_regexps]     = {vars_regexps},
                [order]            = {order},
                [updatetime]       = {time}
            WHERE [id] = {id}";

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Deletes all maps related to a gadget
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @param   string  $time   Time condition
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function RemoveGadgetMaps($gadget, $time = '')
    {
        $params = array();
        $params['gadget'] = $gadget;
        $params['time']   = $time;

        $sql = '
            DELETE FROM [[url_maps]]
            WHERE
                [gadget] = {gadget}';

        if (!empty($time)) {
            $sql .= ' AND [updatetime] < {time}';
        }

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Adds a new alias
     *
     * @access  public
     * @param   string  $alias  Alias value
     * @param   string  $url    Real URL
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function AddAlias($alias, $url)
    {
        if (trim($alias) == '' || trim($url) == '') {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_NOT_ADDED'), _t('URLMAPPER_NAME'));
        }

        $params = array();
        $params['real']  = $url;
        $params['alias'] = $alias;
        $params['hash']  = md5($alias);

        if ($this->AliasExists($params['hash'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_ALREADY_EXISTS'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_ALREADY_EXISTS'), _t('URLMAPPER_NAME'));
        }

        $sql = '
            INSERT INTO [[url_aliases]]
                ([real_url], [alias_url], [alias_hash])
            VALUES
                ({real}, {alias}, {hash})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_NOT_ADDED'), _t('URLMAPPER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ALIAS_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates the alias
     *
     * @access  public
     * @param   int     $id     Alias ID
     * @param   string  $alias  Alias value
     * @param   string  $url    Real URL
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function UpdateAlias($id, $alias, $url)
    {
        if (trim($alias) == '' || trim($url) == '') {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_NOT_UPDATED'), _t('URLMAPPER_NAME'));
        }

        if ($url{0} == '?') {
            $url = substr($url, 1);
        }

        $params  = array();
        $params['id']    = $id;
        $params['real']  = $url;
        $params['alias'] = $alias;
        $params['hash']  = md5($alias);

        $sql = '
            SELECT
                [alias_hash]
            FROM [[url_aliases]]
            WHERE [id] = {id}';
        $result = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_NOT_UPDATED'), _t('URLMAPPER_NAME'));
        }

        if ($result != $params['hash']) {
            if ($this->AliasExists($params['hash'])) {
                $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_ALREADY_EXISTS'), RESPONSE_ERROR);
                return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_ALREADY_EXISTS'), _t('URLMAPPER_NAME'));
            }
        }

        $sql = '
            UPDATE [[url_aliases]] SET
                [real_url] = {real},
                [alias_url] = {alias},
                [alias_hash] = {hash}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_NOT_UPDATED'), _t('URLMAPPER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ALIAS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes the alias
     *
     * @access  public
     * @param   int     $id  Alias ID
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function DeleteAlias($id)
    {
        $params = array();
        $params['id'] = $id;

        $sql = 'DELETE FROM [[url_aliases]] WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ALIAS_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ALIAS_NOT_DELETED'), _t('URLMAPPER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ALIAS_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Returns the error map
     *
     * @access  public
     * @param   int     $id Error Map ID
     * @return  mixed   Array of Error Map otherwise Jaws_Error
     */
    function GetErrorMap($id)
    {
        $params = array();
        $params['id'] = $id;

        $sql = '
            SELECT
                [url], [code], [new_url], [new_code]
            FROM [[url_errors]]
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return $result;
    }

    /**
     * Adds a new error map
     *
     * @access  public
     * @param   string  $url        source url
     * @param   string  $code       code
     * @param   string  $new_url    destination url
     * @param   string  $new_code   new code
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function AddErrorMap($url, $code, $new_url, $new_code)
    {
        if (trim($url) == '') {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ERRORMAP_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ERRORMAP_NOT_ADDED'), _t('URLMAPPER_NAME'));
        }

        $params = array();
        $params['url'] = $url;
        $params['code'] = $code;
        $params['url_hash'] = md5($url);
        $params['new_url'] = $new_url;
        $params['new_code'] = $new_code;
        $params['now'] = $GLOBALS['db']->Date();

        if ($this->ErrorMapExists($params['url_hash'])) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ERRORMAP_ALREADY_EXISTS'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ERRORMAP_ALREADY_EXISTS'), _t('URLMAPPER_NAME'));
        }

        $sql = '
            INSERT INTO [[url_errors]]
                ([url], [url_hash], [code], [new_url], [new_code], [createtime], [updatetime])
            VALUES
                ({url}, {url_hash}, {code}, {new_url}, {new_code}, {now}, {now})';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ERRORMAP_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ERRORMAP_NOT_ADDED'), _t('URLMAPPER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERRORMAP_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Update the error map
     *
     * @access  public
     * @param   int     $id         error map id
     * @param   string  $url        source url
     * @param   string  $code       code
     * @param   string  $new_url    destination url
     * @param   string  $new_code   new code
     * @return  array   Response array (notice or error)
     */
    function UpdateErrorMap($id, $url, $code, $new_url, $new_code)
    {

        if (trim($url) == '') {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ERRORMAP_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ERRORMAP_NOT_UPDATED'), _t('URLMAPPER_NAME'));
        }

        $params = array();
        $params['id'] = $id;
        $params['url'] = $url;
        $params['code'] = $code;
        $params['url_hash'] = md5($url);
        $params['new_url'] = $new_url;
        $params['new_code'] = $new_code;
        $params['now'] = $GLOBALS['db']->Date();

        $sql = '
            SELECT
                [url_hash]
            FROM [[url_errors]]
            WHERE [id] = {id}';
        $result = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ERRORMAP_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ERRORMAP_NOT_UPDATED'), _t('URLMAPPER_NAME'));
        }

        if ($result != $params['url_hash']) {
            if ($this->ErrorMapExists($params['url_hash'])) {
                $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ERRORMAP_ALREADY_EXISTS'), RESPONSE_ERROR);
                return new Jaws_Error(_t('URLMAPPER_ERROR_ERRORMAP_ALREADY_EXISTS'), _t('URLMAPPER_NAME'));
            }
        }

        $sql = '
            UPDATE [[url_errors]] SET
                [url] = {url},
                [url_hash] = {url_hash},
                [code] = {code},
                [new_url] = {new_url},
                [new_code] = {new_code},
                [updatetime] = {now}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ERRORMAP_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ERRORMAP_NOT_UPDATED'), _t('URLMAPPER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERRORMAP_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes the error map
     *
     * @access  public
     * @param   int     $id     Error map ID
     * @return  array   Response array (notice or error)
     */
    function DeleteErrorMap($id)
    {
        $params = array();
        $params['id'] = $id;

        $sql = 'DELETE FROM [[url_errors]] WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_ERRORMAP_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_ERRORMAP_NOT_DELETED'), _t('URLMAPPER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERRORMAP_DELETED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Get list of error maps
     *
     * @access  public
     * @param   int     $limit
     * @param   int     $offset
     * @return  array   Grid data
     */
    function GetErrorMaps($limit, $offset)
    {
        $sql = '
            SELECT [id], [url], [code], [new_url], [new_code], [hits], [createtime], [updatetime]
            FROM [[url_errors]]
            ORDER BY [createtime]';

        if (!empty($limit)) {
            $res = $GLOBALS['db']->setLimit($limit, $offset);
            if (Jaws_Error::IsError($res)) {
                return new Jaws_Error($res->getMessage(), 'SQL');
            }
        }

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            return array();
        }

        return $result;
    }

    /**
     * Handle HTTP Errors
     *
     * @access  public
     * @param   string  $url
     * @param   int     $code
     * @return  array
     */
    function HandleHttpErrors($url, $code)
    {
        $params = array();
        $params['url_hash'] = md5($url);

        $sql = '
            SELECT
                [id], [new_url], [new_code]
            FROM [[url_errors]]
            WHERE [url_hash] = {url_hash}';

        $errorMap = $GLOBALS['db']->queryRow($sql, $params);
        if (Jaws_Error::IsError($errorMap)) {
            return array();
        }

        // find map for this url error
        if ($errorMap != null) {
            $params = array();
            $params['id'] = $errorMap['id'];

            $sql = "
                UPDATE [[url_errors]] SET
                    [hits] = [hits]+1
                WHERE [id] = {id}";

            $result = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            $redirectData = array();
            $redirectData['url'] = $errorMap['new_url'];
            $redirectData['code'] = $errorMap['code'];
            return ($redirectData);
        }

        $this->AddErrorMap($url, $code, null, null);
        return array();
    }

    /**
     * Updates settings
     *
     * @access  public
     * @param   bool    $enabled        Should maps be used?
     * @param   bool    $use_aliases    Should aliases be used?
     * @param   bool    $precedence     custom map precedence over default map
     * @param   string  $extension      Extension to use
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function SaveSettings($enabled, $use_aliases, $precedence, $extension)
    {
        $res = $this->gadget->SetRegistry('map_enabled', ($enabled === true)? 'true' : 'false');
        $res = $res && $this->gadget->SetRegistry('map_custom_precedence', ($precedence === true)?  'true' : 'false');
        $res = $res && $this->gadget->SetRegistry('map_extensions',  $extension);
        $res = $res && $this->gadget->SetRegistry('map_use_aliases', ($use_aliases === true)? 'true' : 'false');

        if ($res === false) {
            $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_ERROR_SETTINGS_NOT_SAVED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('URLMAPPER_ERROR_SETTINGS_NOT_SAVED'), _t('URLMAPPER_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('URLMAPPER_SETTINGS_SAVED'), RESPONSE_NOTICE);
        return true;
    }

}