<?php
/**
 * PiwiSmart.php - Class to manage the smart includes
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2005
 * <c> Piwi
 */
class Piwi
{
    /**
     * Creates a widget and returns the object
     *
     * @param  string  $widget Widget name
     * @param  string  N number of params
     * @return object  A piwi object
     * @access public
     */
    static function &createWidget($widget)
    {
        $file = '';
        if (file_exists(PIWI_PATH."/Widget/Bin/{$widget}.php")) {
            $file = PIWI_PATH."/Widget/Bin/{$widget}.php";
        } elseif (file_exists(PIWI_PATH."/Widget/Container/{$widget}.php")) {
            $file = PIWI_PATH."/Widget/Container/{$widget}.php";
        } elseif (file_exists(PIWI_PATH."/Widget/Misc/{$widget}.php")) {
            $file = PIWI_PATH."/Widget/Misc/{$widget}.php";
        } else {
            die ("[PIWI] - Sorry but the widget {$widget} does not exists");
        }
        require_once $file;

        $widgetObj = null;

        //Get object required params
        $widgetReqParams = defined(strtoupper($widget)."_REQ_PARAMS") ? constant(strtoupper($widget)."_REQ_PARAMS") : 3;
        $numargs = func_num_args() - 1;
        $arg_list = func_get_args();
        array_shift($arg_list);
        if ($numargs < $widgetReqParams) {
            for ($i = $numargs; $i < $widgetReqParams; $i++) {
                $arg_list[$i] = null;
            }
        } else {
            $widgetReqParams = ($numargs < 10) ? $numargs : 3;
        }

        switch($widgetReqParams) {
        case 0:
            $widgetObj = new $widget();
            break;
        case 1:
            $widgetObj = new $widget($arg_list[0]);
            break;
        case 2:
            $widgetObj = new $widget($arg_list[0], $arg_list[1]);
            break;
        case 3:
            $widgetObj = new $widget($arg_list[0], $arg_list[1], $arg_list[2]);
            break;
        case 4:
            $widgetObj = new $widget($arg_list[0], $arg_list[1], $arg_list[2], $arg_list[3]);
            break;
        case 5:
            $widgetObj = new $widget($arg_list[0], $arg_list[1], $arg_list[2], $arg_list[3],
                                      $arg_list[4]);
            break;
        case 6:
            $widgetObj = new $widget($arg_list[0], $arg_list[1], $arg_list[2], $arg_list[3],
                                      $arg_list[4], $arg_list[5]);
            break;
        case 7:
            $widgetObj = new $widget($arg_list[0], $arg_list[1], $arg_list[2], $arg_list[3],
                                      $arg_list[4], $arg_list[5], $arg_list[6]);
            break;
        case 8:
            $widgetObj = new $widget($arg_list[0], $arg_list[1], $arg_list[2], $arg_list[3],
                                      $arg_list[4], $arg_list[5], $arg_list[6], $arg_list[7]);
            break;
        case 9:
            $widgetObj = new $widget($arg_list[0], $arg_list[1], $arg_list[2], $arg_list[3],
                                      $arg_list[4], $arg_list[5], $arg_list[6], $arg_list[7],
                                      $arg_list[8]);
            break;
        }
        return $widgetObj;
    }

    /**
     * Manages the 'global' conf that is an array with global configuration: strings, basic settings,
     * so we automate some work for the developer
     *
     * It receives:
     *
     *   - First param can be:
     *     - An array with many keys and values, developer can fidn the keys and values of each widget on
     *       the PHP file (like Button.php)
     *     - A string: for GET and SET actions
     *   - An action: 
     *     - SAVE: Save the new global configuration
     *     - GET:  Get a key
     *
     * @access  public
     * @param   mixed  $firstParam  Can be an array or a string
     * @param   string $action      Action to execute
     */
    static function managePiwiConf($firstParam, $action) 
    {
        static $piwiConf;

        $action = strtoupper($action);
        switch($action) {
        case 'EXPORT':
            if (!is_array($firstParam)) {
                $firstParam = array();
            }
            
            if (!isset($piwiConf)) {
                $piwiConf = $firstParam;
            }
            break;
        case 'ADD':
            if (!is_array($firstParam)) {
                break;
            }
            
            if (!isset($piwiConf)) {
                $piwiConf = $array();;
            } else {
                foreach($firstParam as $key => $value) {
                    if (is_string($key) && !isset($piwiConf[$key])) {
                        $piwiConf[$key] = $value;
                    }
                }
            }
            break;
        case 'GET':
            if (!isset($piwiConf)) {
                return '';
            }

            if (!is_array($piwiConf)) {
                return '';
            }

            if (!is_string($firstParam)) {
                return '';
            }

            if (isset($piwiConf[$firstParam])) {
                return $piwiConf[$firstParam];
            } else {
                return '';
            }
            break;
        }
    }

    /**
     * Is a simple wrapper of managePiwiConf($data, 'EXPORT').
     *
     * @access  public
     * @param   array   $data Array Conf
     */
    static function exportConf($data) 
    {
        Piwi::managePiwiConf($data, 'EXPORT');
    }

    /**
     * A simple wrapper for managePiwiConf('key', 'GET')
     *
     * @access  public
     * @param   string   $key  Key name
     * @retrun  string   The value of the key or an empty string if anything goes wrong
     */
    static function getVarConf($key)
    {
        return Piwi::managePiwiConf($key, 'GET');
    }

    /**
     * Wrapper for managePiwiConf($data, 'SAVE'). Adds keys to piwiConf
     *
     * @access  public
     * @param   array    $data Array Conf
     */
    static function addExtraConf($data) 
    {
        Piwi::managePiwiConf($data, 'ADD');
    }

    /**
     * Manage id actions
     *
     * @access  public
     * @param   string  $param  String param (can be the name or id of a widget)
     * @param   string  $action Action to do
     */
    static function managePiwiIds($param, $action)
    {
        static $piwi_usedIds;

        if (!isset($piwi_usedIds)) {
            $piwi_usedIds = array();
        }

        switch($action) {
        case 'CHECK':
            return isset($piwi_usedIds[$param]);
            break;
        case 'GENERATE':
            return $param . '_' . uniqid(mt_rand(), true);
            break;
        case 'REGISTER':
            $piwi_usedIds[$param] = true;
            break;
        }
    }

    /**
     * Returns true/false if id exists
     *
     * @access  public
     * @param   string  $id Name
     * @return  boolean Exists / Not exists
     */
    function idExists($id) 
    {
        return Piwi::managePiwiIds($id, 'CHECK');       
    }

    /**
     * Generates an automatic id from a given prefix
     *
     * @access  public
     * @param   string  $prefix   Prefix to be used
     * @return  string  New id
     */
    function generateId($prefix)
    {
        return Piwi::managePiwiIds($prefix, 'GENERATE');
    }

    /**
     * Registers an id
     *
     * @access  public
     * @param   string  $id ID
     */
    static function registerId($id)
    {
        Piwi::managePiwiIds($id, 'REGISTER');
    }
}