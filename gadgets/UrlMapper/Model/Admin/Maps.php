<?php
/**
 * UrlMapper Core Gadget
 *
 * @category   GadgetModel
 * @package    UrlMapper
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2006-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapper_Model_Admin_Maps extends UrlMapper_Model_Maps
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
        $mapsTable = Jaws_ORM::getInstance()->table('url_maps');
        $mapsTable->select('map', 'regexp', 'extension', 'vars_regexps', 'custom_map', 'custom_regexp', 'order');
        $result = $mapsTable->where('id', $id)->fetchRow();
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
     * @return  mixed   Map route or Jaws_Error on error
     */
    function GetMapByParams($gadget, $action)
    {
        $mapsTable = Jaws_ORM::getInstance()->table('url_maps');
        $mapsTable->select(
            'id:integer', 'gadget', 'map', 'regexp', 'extension', 'vars_regexps',
            'custom_map', 'custom_regexp');
        $mapsTable->where('gadget', $gadget)->and()->where('action', $action);
        $result = $mapsTable->fetchRow();
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
        $mapsTable = Jaws_ORM::getInstance()->table('url_maps');
        $mapsTable->select('id:integer', 'map', 'extension');
        $mapsTable->where('gadget', $gadget)->and()->where('action', $action);
        $result = $mapsTable->orderBy('order asc')->fetchAll();
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
        $mapsTable = Jaws_ORM::getInstance()->table('url_maps');
        $mapsTable->select('gadget', 'action')->where('gadget', $gadget);
        $mapsTable->groupBy('gadget', 'action')->orderBy('gadget', 'action');
        return $mapsTable->fetchColumn(1);
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
        $file = ROOT_JAWS_PATH . 'gadgets/' . $gadget . '/Map.php';
        if (file_exists($file)) {
            $maps = array();
            include_once $file;
            foreach ($maps as $order => $map) {
                $vars_regexps = array();
                $vars_regexps = isset($map[2])? $map[2] : $vars_regexps;
                if (preg_match_all('#{(\w+)}#si', $map[1], $matches)) {
                    foreach ($matches[1] as $m) {
                        if (!isset($vars_regexps[$m])) {
                            $vars_regexps[$m] = '[[:alnum:]_\.-]+';
                        }
                    }
                }

                $res = $this->AddMap($gadget,
                    $map[0],
                    $map[1],
                    isset($map[3])? $map[3] : '.',
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
        $file = ROOT_JAWS_PATH. "gadgets/$gadget/Map.php";
        $maps = array();
        if (@include($file)) {
            $now = time();
            foreach ($maps as $order => $map) {
                $eMap = $this->GetMapByParams($gadget, $map[0]);
                if (Jaws_Error::IsError($eMap)) {
                    return $eMap;
                }

                $vars_regexps = array();
                $vars_regexps = isset($map[2])? $map[2] : $vars_regexps;
                if (preg_match_all('#{(\w+)}#si', $map[1], $matches)) {
                    foreach ($matches[1] as $m) {
                        if (!isset($vars_regexps[$m])) {
                            $vars_regexps[$m] = '[[:alnum:]_\.-]+';
                        }
                    }
                }

                if (empty($eMap)) {
                    $res = $this->AddMap($gadget,
                        $map[0],
                        $map[1],
                        isset($map[3])? $map[3] : '.',
                        $vars_regexps,
                        $order + 1,
                        $now
                    );
                    if (Jaws_Error::IsError($res)) {
                        return $res;
                    }
                } else {
                    $res = $this->UpdateMap(
                        $eMap['id'],
                        $eMap['custom_map'],
                        $vars_regexps,
                        $order + 1,
                        $map[1],
                        isset($map[3])? $map[3] : '.',
                        $now
                    );
                    if (Jaws_Error::IsError($res)) {
                        return $res;
                    }
                }
            }

            // remove outdated maps
            $res = $this->DeleteGadgetMaps($gadget, $now);
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
        $regexp = str_replace('/', '\/', (string)$map);
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
            $regexp = '@^' . $regexp . '$@u';
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
    function AddMap($gadget, $action, $map, $extension = '.', $vars_regexps = null, $order = 0, $time = '')
    {
        //for compatible with old versions
        $extension = ($extension == 'index.php')? '' : $extension;
        if (!empty($extension) && $extension[0] != '.') {
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
        $params['insert_time'] = empty($time)? time() : $time;
        $params['update_time'] = $params['insert_time'];

        $mapsTable = Jaws_ORM::getInstance()->table('url_maps');
        $result = $mapsTable->insert($params)->exec();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($this::t('ERROR_MAP_NOT_ADDED'));
        }

        return true;
    }

    /**
     * Updates map route of the map
     *
     * @access  public
     * @param   int     $id             Map ID
     * @param   string  $custom_map     Custom_map to use (foo/bar/{param}/{param2}...)
     * @param   array   $vars_regexps   Array of regexp validators
     * @param   int     $order          Sequence number of the map
     * @param   string  $map            Map to use (foo/bar/{param}/{param2}...)
     * @param   string  $extension      Extension of default map
     * @param   string  $time           Create/Update time
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function UpdateMap($id, $custom_map, $vars_regexps, $order,
                       $map, $map_extension = '.', $time = ''
    ) {
        if (!empty($map_extension) && $map_extension[0] != '.') {
            $map_extension = '.'.$map_extension;
        }

        if (is_null($vars_regexps)) {
            $result = $this->GetMap($id);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }

            if (empty($result)) {
                return Jaws_Error::raiseError($this::t('NO_MAPS'),  __FUNCTION__);
            }

            $vars_regexps = unserialize($result['vars_regexps']);
        }

        $params = array();
        $params['map'] = $map;
        $params['regexp'] = $this->GetMapRegExp($map, $vars_regexps);
        $params['extension'] = $map_extension;
        $params['custom_map']    = $custom_map;
        $params['custom_regexp'] = $this->GetMapRegExp($custom_map, $vars_regexps);
        $params['vars_regexps']  = serialize($vars_regexps);
        $params['order']         = $order;
        $params['update_time']    = empty($time)? time() : $time;

        $mapsTable = Jaws_ORM::getInstance()->table('url_maps');
        return $mapsTable->update($params)->where('id', (int)$id)->exec();
    }

    /**
     * Updates custom map route of the map
     *
     * @access  public
     * @param   int     $id             Map ID
     * @param   string  $custom_map     Custom_map to use (foo/bar/{param}/{param2}...)
     * @param   int     $order          Sequence number of the map
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function UpdateCustomMap($id, $custom_map, $order)
    {
        $dbMap = $this->GetMap($id);
        if (Jaws_Error::IsError($dbMap)) {
            return $dbMap;
        }

        if (empty($dbMap)) {
            return Jaws_Error::raiseError($this::t('NO_MAPS'),  __FUNCTION__);
        }

        $params = array();
        $params['custom_map']    = $custom_map;
        $params['custom_regexp'] = $this->GetMapRegExp($custom_map, unserialize($dbMap['vars_regexps']));
        $params['order']         = $order;
        $params['update_time']   = time();

        return Jaws_ORM::getInstance()
            ->table('url_maps')
            ->update($params)
            ->where('id', (int)$id)
            ->exec();
    }

    /**
     * Deletes all maps related to a gadget
     *
     * @access  public
     * @param   string  $gadget Gadget name
     * @param   string  $time   Time condition
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function DeleteGadgetMaps($gadget, $time = 0)
    {
        $mapsTable = Jaws_ORM::getInstance()->table('url_maps');
        $mapsTable->delete()->where('gadget', $gadget);
        if (!empty($time)) {
            $mapsTable->and()->where('update_time', $time, '<');
        }
        $result = $mapsTable->exec();
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }
}