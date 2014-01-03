<?php
/**
 * SysInfo Gadget
 *
 * @category   GadgetModel
 * @package    SysInfo
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2008-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class SysInfo_Model_SysInfo extends Jaws_Gadget_Model
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
        return array(
            array('title' => 'Operating System',
                'value' => @php_uname()),
            array('title' => 'Web Server',
                'value' => Jaws_XSS::filter($_SERVER['SERVER_SOFTWARE'])),
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
                'value' => Jaws_XSS::filter($_SERVER['HTTP_USER_AGENT'])),
        );
    }
}