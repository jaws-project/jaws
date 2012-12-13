<?php
/**
 * SysInfo Gadget
 *
 * @category   GadgetModel
 * @package    SysInfo
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class SysInfoModel extends Jaws_Gadget_Model
{
    /**
     * Gets database server information
     *
     * @access  public
     * @param   int     $iType  Type of information
     * @return  string  Database server information
     */
    function GetDBServerInfo($iType = 0)
    {
        static $dbInfo;
        if (!isset($dbInfo)) {
            $dbInfo = $GLOBALS['db']->getDatabaseInfo();
        }
        
        switch ($iType) {
            case 0:
                return $dbInfo['driver']. '/'.
                       (empty($dbInfo['version'])? '-' : $dbInfo['version']);
            case 1:
                return $dbInfo['host']. '/'.
                       (empty($dbInfo['port'])? '-' : $dbInfo['port']). '/'.
                       $dbInfo['name']. '/'.
                       (empty($dbInfo['prefix'])? '-' : $dbInfo['prefix']);
        }
    }

    /**
     * Gets loaded extension
     *
     * @access  public
     * @return  string  list of loaded extensions
     */
    function GetLoadedExtensions()
    {
        $modules = get_loaded_extensions();
        sort($modules);
        return implode(", ", $modules);
    }

    /**
     * Gets directory permission
     *
     * @access  public
     * @param   string  $path   Diretory path
     * @return  string  Full permissions of directory
     */
    function GetPermission($path)
    {
        $path = JAWS_PATH . $path;
        $perms = @decoct(@fileperms($path) & 0777);
        if (strlen($perms) < 3) {
            return '---------';
        }

        $str = '';
        for ($i = 0; $i < 3; $i ++) {
            $str .= ($perms[$i] & 04) ? 'r' : '-';
            $str .= ($perms[$i] & 02) ? 'w' : '-';
            $str .= ($perms[$i] & 01) ? 'x' : '-';
        }

        return $str;
    }

    /**
     * Gets equivalent string of error_reporting
     *
     * @access  public
     * @param   int      $error return of error_reporting function
     * @return  string   Equivalent string of error_reporting
     */
    function GetErrorLevelString($error)
    {
        $level_names = array(
                        E_ALL             => 'E_ALL',
                        E_ERROR           => 'E_ERROR',
                        E_WARNING         => 'E_WARNING',
                        E_PARSE           => 'E_PARSE',
                        E_NOTICE          => 'E_NOTICE',
                        E_CORE_ERROR      => 'E_CORE_ERROR',
                        E_CORE_WARNING    => 'E_CORE_WARNING',
                        E_COMPILE_ERROR   => 'E_COMPILE_ERROR',
                        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
                        E_USER_ERROR      => 'E_USER_ERROR',
                        E_USER_WARNING    => 'E_USER_WARNING',
                        E_USER_NOTICE     => 'E_USER_NOTICE',
                        );
        if (defined('E_STRICT')) {
            $level_names[E_STRICT] = 'E_STRICT';
        }

        $levels = array();
        foreach ($level_names as $level => $name) {
            if (($error & $level) == $level) {
                $error = $error & ~$level;
                $levels[] = $name;
            }
        }

        return implode(', ', $levels);
    }

    /**
     * Gets list of loaded Apache modules
     *
     * @access  public
     * @return  string  Comma separated apache modules
     */
    function GetApacheModules()
    {
        if (strpos(strtolower(php_sapi_name()), 'apache') !== false && function_exists('apache_get_modules')) {
            $modules = @apache_get_modules();
            sort($modules);
            return implode(', ', $modules);
        }

        return '';
    }

    /**
     * Gets some system item information
     *
     * @access  public
     * @return  array   System information
     */
    function GetSysInfo()
    {
        $apache_modules = $this->GetApacheModules();
        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        return array(
                    array('title' => 'Operating System',
                          'value' => php_uname()),
                    array('title' => 'Web Server',
                          'value' => $xss->filter($_SERVER['SERVER_SOFTWARE'])),
                    array('title' => 'Server API/Loaded modules',
                          'value' => php_sapi_name(). (empty($apache_modules)? '' : ('/'.$apache_modules))),
                    array('title' => 'PHP Version',
                          'value' => phpversion()),
                    array('title' => 'Loaded PHP Extensions',
                          'value' => $this->GetLoadedExtensions()),
                    array('title' => 'Database Driver/Version',
                          'value' => $this->GetDBServerInfo(0)),
                    array('title' => 'Database Host/Port/Name/Prefix',
                          'value' => $this->GetDBServerInfo(1)),
                    array('title' => 'Free/Total disk space',
                          'value' => JAWS_UTILS::FormatSize(@disk_free_space(JAWS_PATH)). '/' .
                                     JAWS_UTILS::FormatSize(@disk_total_space(JAWS_PATH))),
                    array('title' => 'Jaws Version/Codename',
                          'value' => JAWS_VERSION . '/' . JAWS_VERSION_CODENAME),
                    array('title' => 'User Agent',
                          'value' => $xss->filter($_SERVER['HTTP_USER_AGENT'])),
                );
    }

    /**
     * Gets some PHP settings
     *
     * @access  public
     * @return  array   Some PHP settings
     */
    function GetPHPInfo()
    {
        return array(
                    array('title' => 'Safe mode',
                          'value' => ((bool) ini_get('safe_mode'))? 'On' : 'Off'),
                    array('title' => 'Open basedir',
                          'value' => ($res = ini_get('open_basedir'))? $res : 'None'),
                    array('title' => 'Allow URL fopen/include',
                          'value' => (ini_get('allow_url_fopen')? 'On' : 'Off'). '/' .
                                     (ini_get('allow_url_include')? 'On' : 'Off')),
                    array('title' => 'Display errors',
                          'value' => (((bool) ini_get('display_errors'))? 'On' : 'Off'). '/' .
                                     $this->GetErrorLevelString(error_reporting())),
                    array('title' => 'Max execution/input time',
                          'value' => (($res = ini_get('max_execution_time'))? "{$res}s" : 'None'). '/' .
                                     (($res = ini_get('max_input_time'))? "{$res}s" : 'None')),
                    array('title' => 'Memory limit',
                          'value' => (($res = ini_get('memory_limit'))? $res : 'None')),
                    array('title' => 'File uploads/max size/post size',
                          'value' => (((bool) ini_get('file_uploads'))? 'On' : 'Off'). '/' .
                                     (($res = ini_get('upload_max_filesize'))? $res : 'None'). '/' .
                                     (($res = ini_get('post_max_size'))? $res : 'None')),
                    array('title' => 'Magic quotes',
                          'value' => ((bool) ini_get('magic_quotes_gpc'))? 'On' : 'Off'),
                    array('title' => 'Register globals',
                          'value' => ((bool) ini_get('register_globals'))? 'On' : 'Off'),
                    array('title' => 'Output buffering/handler',
                          'value' => (((bool) ini_get('output_buffering'))? 'On' : 'Off'). '/' .
                                     (($res = ini_get('output_handler'))? $res : 'Default')),
                    array('title' => 'Upload tmp dir',
                          'value' => ini_get('upload_tmp_dir')? ini_get('upload_tmp_dir') : 'None'),
                    array('title' => 'System tmp dir',
                          'value' => sys_get_temp_dir()),
                    array('title' => 'Session save path',
                          'value' => ($res = ini_get('session.save_path'))? $res : 'None'),
                    array('title' => 'Disabled functions',
                          'value' => ($res = ini_get('disable_functions'))? implode(', ', explode(',', $res)) : 'None'),
                );
    }

    /**
     * Gets some info around your Jaws
     *
     * @access  public
     * @return  array   Jaws information
     */
    function GetJawsInfo()
    {
        $jms = $GLOBALS['app']->LoadGadget('Jms', 'AdminModel');
        $theme          = $GLOBALS['app']->GetTheme();
        $coreGadgets    = $jms->GetGadgetsList(true, true, true);
        $gadgets        = $jms->GetGadgetsList(false, true, true);
        $outdateGadgets = $jms->GetGadgetsList(null, true, false);
        $plugins        = $jms->GetPluginsList(true);

        return array(
                    array('title' => "Core gadgets",
                          'value' => implode(", ", array_keys($coreGadgets))),
                    array('title' => "Gadgets",
                          'value' => implode(", ", array_keys($gadgets))),
                    array('title' => "Outdated gadgets",
                          'value' => implode(", ", array_keys($outdateGadgets))),
                    array('title' => "Plugins",
                          'value' => implode(", ", array_keys($plugins))),
                    array('title' => "Default gadget",
                          'value' => $this->GetRegistry('main_gadget', 'Settings')),
                    array('title' => "Authentication method",
                          'value' => $$this->GetRegistry('auth_method', 'Users')),
                    array('title' => "Mailer",
                          'value' => $this->GetRegistry('mailer', 'Settings')),
                    array('title' => "FTP",
                          'value' => $this->GetRegistry('ftp_enabled', 'Settings')),
                    array('title' => "Proxy",
                          'value' => $this->GetRegistry('proxy_enabled', 'Settings')),
                    array('title' => "Default theme",
                          'value' => $theme['name']),
                    array('title' => "Encryption",
                          'value' => $this->GetRegistry('crypt_enabled', 'Policy')),
                    array('title' => "GZip compression",
                          'value' => $this->GetRegistry('gzip_compression', 'Settings')),
                    array('title' => "WWW-Authentication",
                          'value' => $this->GetRegistry('http_auth', 'Settings')),
                    array('title' => "URL mapping",
                          'value' => $GLOBALS['app']->Registry->Get('/map/enabled')),
                    array('title' => "Use rewrite",
                          'value' => $GLOBALS['app']->Registry->Get('/map/use_rewrite')),
                );
    }

    /**
     * Gets permissions on some Jaws directories
     *
     * @access  public
     * @return  array   Directories permissions
     */
    function GetDirsPermissions()
    {
        return array(
                    array('title' => '/',
                          'value' => $this->GetPermission('')),
                    array('title' => '/config',
                          'value' => $this->GetPermission('config')),
                    array('title' => '/data',
                          'value' => $this->GetPermission('data')),
                    array('title' => '/data/themes',
                          'value' => $this->GetPermission('data/themes')),
                    array('title' => '/gadgets',
                          'value' => $this->GetPermission('gadgets')),
                    array('title' => '/images',
                          'value' => $this->GetPermission('images')),
                    array('title' => '/include',
                          'value' => $this->GetPermission('include')),
                    array('title' => '/install',
                          'value' => $this->GetPermission('install')),
                    array('title' => '/languages',
                          'value' => $this->GetPermission('languages')),
                    array('title' => '/libraries',
                          'value' => $this->GetPermission('libraries')),
                    array('title' => '/plugins',
                          'value' => $this->GetPermission('plugins')),
                    array('title' => '/upgrade',
                          'value' => $this->GetPermission('upgrade')),
                );
    }

}