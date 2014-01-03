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
class SysInfo_Model_PHPInfo extends Jaws_Gadget_Model
{
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
}