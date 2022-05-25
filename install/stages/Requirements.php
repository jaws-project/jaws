<?php
define('MIN_PHP_VERSION', '5.3.20');
/**
 * Requirements to upgrade jaws.
 *
 * @category   Application
 * @package    InstallStage
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Installer_Requirements extends JawsInstaller
{
    var $_db_drivers = array('mysql'     => 'MySQL',
                             'mysqli'    => 'MySQLi',
                             'pgsql'     => 'PostgreSQL',
                             'oci8'      => 'Oracle',
                             'interbase' => 'Interbase/Firebird',
                             'mssql'     => 'MSSQL Server',
                             'sqlsrv'    => 'MSSQL Server(Microsoft Driver)',
                             'sqlite'    => 'SQLite 2',
                            );

    /**
     * Builds the upgrader page stage
     *
     * @access  public
     * @return  string  A block of valid XHTML to display the requirements
     */
    function Display()
    {
        $tpl = new Jaws_Template(false, false);
        $tpl->load('display.html', 'stages/Requirements/templates');
        $tpl->setBlock('Requirements');

        $tpl->setVariable('requirements', $this->t('REQUIREMENTS'));
        $tpl->setVariable('requirement',  $this->t('REQ_REQUIREMENT'));
        $tpl->setVariable('optional',     $this->t('REQ_OPTIONAL'));
        $tpl->setVariable('recommended',  $this->t('REQ_RECOMMENDED'));
        $tpl->setVariable('directive',    $this->t('REQ_DIRECTIVE'));
        $tpl->setVariable('actual',       $this->t('REQ_ACTUAL'));
        $tpl->setVariable('result',       $this->t('REQ_RESULT'));
        $tpl->SetVariable('prev',         Jaws::t('PREVIOUS'));
        $tpl->SetVariable('next',         Jaws::t('NEXT'));
        if ($this->_check_path(ROOT_DATA_PATH. 'logs', 'rw', '')) {
            $tpl->SetVariable('log_use', $this->t('INTRO_LOG', ROOT_DATA_PATH.'logs/install.txt'));
            $tpl->SetBlock('Requirements/logcheckbox');
            $tpl->SetVariable('checked', !empty($_SESSION['use_log'])? 'checked="checked"' : '');
            $tpl->ParseBlock('Requirements/logcheckbox');
        } else {
            $tpl->SetVariable('log_use', $this->t('INTRO_LOG_ERROR', 'data/logs'));
        }

        $modules = get_loaded_extensions();
        $modules = array_map('strtolower', $modules);

        _log(JAWS_DEBUG,"Checking requirements...");
        // PHP version
        $tpl->setBlock('Requirements/req_item');
        $tpl->setVariable('item', $this->t('REQ_PHP_VERSION'));
        $tpl->setVariable('item_requirement', $this->t('REQ_GREATER_THAN', MIN_PHP_VERSION));
        $tpl->setVariable('item_actual', phpversion());
        if (version_compare(phpversion(), MIN_PHP_VERSION, ">=") == 1) {
            _log(JAWS_DEBUG,"PHP installed version looks ok (>= ".MIN_PHP_VERSION.")");
            $result_txt = '<span style="color: #0b0;">'.$this->t('REQ_OK').'</span>';
        } else {
            _log(JAWS_DEBUG,"PHP installed version (".phpversion().") is not supported");
            $result_txt = '<span style="color: #b00;">'.$this->t('REQ_BAD').'</span>';
        }
        $tpl->setVariable('result', $result_txt);
        $tpl->parseBlock('Requirements/req_item');

        // config directory
        $tpl->setBlock('Requirements/req_item');
        $result = $this->_check_path('config', 'r');
        $tpl->setVariable('item', $this->t('REQ_DIRECTORY', 'config'));
        $tpl->setVariable('item_requirement', $this->t('REQ_READABLE'));
        $tpl->setVariable('item_actual', $this->_get_perms('config'));
        if ($result) {
            _log(JAWS_DEBUG,"config directory has read-permission privileges");
            $result_txt = '<span style="color: #0b0;">'.$this->t('REQ_OK').'</span>';
        } else {
            _log(JAWS_DEBUG,"config directory doesn't have read-permission privileges");
            $result_txt = '<span style="color: #b00;">'.$this->t('REQ_BAD').'</span>';
        }
        $tpl->setVariable('result', $result_txt);
        $tpl->parseBlock('Requirements/req_item');

        // data directory
        $tpl->setBlock('Requirements/req_item');
        $result = $this->_check_path(ROOT_DATA_PATH, 'rw', '');
        $tpl->setVariable('item', $this->t('REQ_DIRECTORY', 'data'));
        $tpl->setVariable('item_requirement', $this->t('REQ_WRITABLE'));
        $tpl->setVariable('item_actual', $this->_get_perms(ROOT_DATA_PATH, ''));
        if ($result) {
            _log(JAWS_DEBUG,"data directory has read and write permission privileges");
            $result_txt = '<span style="color: #0b0;">'.$this->t('REQ_OK').'</span>';
        } else {
            _log(JAWS_DEBUG,"data directory doesn't have read and write permission privileges");
            $result_txt = '<span style="color: #b00;">'.$this->t('REQ_BAD').'</span>';
        }
        $tpl->setVariable('result', $result_txt);
        $tpl->parseBlock('Requirements/req_item');

        // Database drivers
        $tpl->setBlock('Requirements/req_item');
        $tpl->setVariable('item', implode('<br/>', $this->_db_drivers));
        $tpl->setVariable('item_requirement', Jaws::t('YESS'));
        $actual = '';
        $db_state = false;
        foreach (array_keys($this->_db_drivers) as $ext) {
            $db_state = ($db_state || in_array($ext, $modules));
            $actual .= (!empty($actual)? '<br />' : '') . (in_array($ext, $modules)? $ext : '-----');
        }
        $tpl->setVariable('item_actual', $actual);
        if ($db_state) {
            _log(JAWS_DEBUG,"Available database drivers: $actual");
            $result_txt = '<span style="color: #0b0;">'.$this->t('REQ_OK').'</span>';
        } else {
            _log(JAWS_DEBUG,"No database driver found");
            $result_txt = '<span style="color: #b00;">'.$this->t('REQ_BAD').'</span>';
        }
        $tpl->setVariable('result', $result_txt);
        $tpl->parseBlock('Requirements/req_item');

        // XML extension
        $tpl->setBlock('Requirements/req_item');
        $tpl->setVariable('item', $this->t('REQ_EXTENSION', 'XML'));
        $tpl->setVariable('item_requirement', Jaws::t('YESS'));
        $tpl->setVariable('item_actual', (in_array('xml', $modules)? Jaws::t('YESS') : Jaws::t('NOO')));
        if (in_array('xml', $modules)) {
            _log(JAWS_DEBUG,"xml support is enabled");
            $result_txt = '<span style="color: #0b0;">'.$this->t('REQ_OK').'</span>';
        } else {
            _log(JAWS_DEBUG,"xml support is not enabled");
            $result_txt = '<span style="color: #b00;">'.$this->t('REQ_BAD').'</span>';
        }
        $tpl->setVariable('result', $result_txt);
        $tpl->parseBlock('Requirements/req_item');

        // File Upload
        $tpl->setBlock('Requirements/rec_item');
        $tpl->setVariable('item', $this->t('REQ_FILE_UPLOAD'));
        $tpl->setVariable('item_requirement', Jaws::t('YESS'));
        $check = (bool) ini_get('file_uploads');
        $tpl->setVariable('item_actual', ($check ? Jaws::t('YESS'): Jaws::t('NOO')));
        if ($check) {
            _log(JAWS_DEBUG,"PHP accepts file uploads");
            $result_txt = '<span style="color: #0b0;">'.$this->t('REQ_OK').'</span>';
        } else {
            _log(JAWS_DEBUG,"PHP doesn't accept file uploads");
            $result_txt = '<span style="color: #b00;">'.$this->t('REQ_BAD').'</span>';
        }
        $tpl->setVariable('result', $result_txt);
        $tpl->parseBlock('Requirements/rec_item');

        // Safe mode
        $tpl->setBlock('Requirements/rec_item');
        $tpl->setVariable('item', $this->t('REQ_SAFE_MODE'));
        $tpl->setVariable('item_requirement', $this->t('REQ_OFF'));
        $safe_mode = (bool) ini_get('safe_mode');
        $tpl->setVariable('item_actual', ($safe_mode ? $this->t('REQ_ON'): $this->t('REQ_OFF')));
        if ($safe_mode) {
            _log(JAWS_DEBUG,"PHP has safe-mode turned on");
            $result_txt = '<span style="color: #b00;">'.$this->t('REQ_BAD').'</span>';
        } else {
            _log(JAWS_DEBUG,"PHP has safe-mode turned off");
            $result_txt = '<span style="color: #0b0;">'.$this->t('REQ_OK').'</span>';
        }
        $tpl->setVariable('result', $result_txt);
        $tpl->parseBlock('Requirements/rec_item');

        // GD/ImageMagick
        $tpl->setBlock('Requirements/rec_item');
        $tpl->setVariable('item', $this->t('REQ_EXTENSION', 'GD/ImageMagick'));
        $tpl->setVariable('item_requirement', Jaws::t('YESS'));
        $actual  = in_array('gd', $modules)?'GD' : '';
        $actual .= in_array('magickwand', $modules)? ((empty($actual)? '' : ' + ') . 'ImageMagick') : '';
        $actual = empty($actual)? 'No' : $actual;
        $tpl->setVariable('item_actual', $actual);
        if (in_array('gd', $modules) || in_array('magickwand', $modules)) {
            _log(JAWS_DEBUG,"PHP has GD or ImageMagick turned on");
            $result_txt = '<span style="color: #0b0;">'.$this->t('REQ_OK').'</span>';
        } else {
            _log(JAWS_DEBUG,"PHP has GD or ImageMagick turned off");
            $result_txt = '<span style="color: #b00;">'.$this->t('REQ_BAD').'</span>';
        }
        $tpl->setVariable('result', $result_txt);
        $tpl->parseBlock('Requirements/rec_item');

        // Exif extension
        $tpl->setBlock('Requirements/rec_item');
        $tpl->setVariable('item', $this->t('REQ_EXTENSION', 'Exif'));
        $tpl->setVariable('item_requirement', Jaws::t('YESS'));
        $tpl->setVariable('item_actual', (in_array('exif', $modules)? Jaws::t('YESS') : Jaws::t('NOO')));
        if (in_array('exif', $modules)) {
            _log(JAWS_DEBUG,"exif support is enabled");
            $result_txt = '<span style="color: #0b0;">'.$this->t('REQ_OK').'</span>';
        } else {
            _log(JAWS_DEBUG,"exif support is not enabled");
            $result_txt = '<span style="color: #b00;">'.$this->t('REQ_BAD').'</span>';
        }
        $tpl->setVariable('result', $result_txt);
        $tpl->parseBlock('Requirements/rec_item');

        // OpenSSL extension
        $tpl->setBlock('Requirements/rec_item');
        $tpl->setVariable('item', $this->t('REQ_EXTENSION', 'OpenSSL'));
        $tpl->setVariable('item_requirement', Jaws::t('YESS'));
        $tpl->setVariable('item_actual', (in_array('openssl', $modules)? Jaws::t('YESS') : Jaws::t('NOO')));
        if (in_array('openssl', $modules)) {
            _log(JAWS_DEBUG,"openssl extension is loaded");
            $result_txt = '<span style="color: #0b0;">'.$this->t('REQ_OK').'</span>';
        } else {
            _log(JAWS_DEBUG,"openssl extension is not loaded");
            $result_txt = '<span style="color: #b00;">'.$this->t('REQ_BAD').'</span>';
        }
        $tpl->setVariable('result', $result_txt);
        $tpl->parseBlock('Requirements/rec_item');

        $tpl->parseBlock('Requirements');
        return $tpl->get();
    }

    /**
     * Makes all validations to FS and PHP installation
     *
     * @access  public
     * @return  boolean If everything looks OK, we return true otherwise a Jaws_Error
     */
    function Validate()
    {
        $request = Jaws_Request::getInstance();
        $use_log = $request->fetch('use_log', 'post');
        //Set main session-log vars
        if (isset($use_log)) {
            $_SESSION['use_log'] = $use_log === 'yes'? JAWS_DEBUG : false;
        } else {
            unset($_SESSION['use_log']);
        }
        _log(JAWS_DEBUG,"Validating install requirements...");

        if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<') == 1) {
            $text = $this->t('REQ_RESPONSE_PHP_VERSION', MIN_PHP_VERSION);
            $type = JAWS_ERROR_ERROR;
            _log(JAWS_DEBUG,$text);
            return new Jaws_Error($text, 0, $type);
        }

        if (!$this->_check_path('config', 'r')) {
            $text = $this->t('REQ_RESPONSE_DIR_PERMISSION', 'config');
            $type = JAWS_ERROR_ERROR;
        }

        if (!$this->_check_path(ROOT_DATA_PATH, 'rw', '')) {
            if (isset($text)) {
                $text = $this->t('REQ_RESPONSE_DIR_PERMISSION', $this->t('REQ_BAD'));
            } else {
                $text = $this->t('REQ_RESPONSE_DIR_PERMISSION', 'data');
            }
            $type = JAWS_ERROR_ERROR;
        }

        if (isset($text)) {
            _log(JAWS_DEBUG,$text);
            return new Jaws_Error($text, 0, $type);
        }

        $modules = get_loaded_extensions();
        $modules = array_map('strtolower', $modules);

        $db_state = false;
        foreach (array_keys($this->_db_drivers) as $ext) {
            $db_state = ($db_state || in_array($ext, $modules));
        }
        if (!$db_state) {
            $text = $this->t('REQ_RESPONSE_EXTENSION', implode(' | ', array_keys($this->_db_drivers)));
            $type = JAWS_ERROR_ERROR;
            _log(JAWS_DEBUG,$text);
            return new Jaws_Error($text, 0, $type);
        }

        if (!in_array('xml', $modules)) {
            $text = $this->t('REQ_RESPONSE_EXTENSION', 'XML');
            $type = JAWS_ERROR_ERROR;
            _log(JAWS_DEBUG,$text);
            return new Jaws_Error($text, 0, $type);
        }

        return true;
    }

    /**
     * Checks if a path(s) exists
     *
     * @access  private
     * @param   string   $paths         Path(s) to check
     * @param   string   $properties    Properties to use when checking the path
     * @return  boolean  If properties  match the given path(s) we return true, otherwise false
     */
    function _check_path($paths, $properties, $basePath = ROOT_JAWS_PATH)
    {
        $paths = !is_array($paths)? array($paths) : $paths;
        foreach ($paths as $path) {
            $path = $basePath . $path;
            if ($properties == 'rw') {
                if (!is_readable($path) || !Jaws_FileManagement_File::is_writable($path)) {
                    return false;
                }
            } else if ($properties == 'r') {
                if (!is_readable($path) || !is_dir($path)) {
                    return false;
                }
            } else {
                if (!is_dir($path)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get permissions string
     *
     * @access  private
     * @param   string  $paths         Path(s) to check
     * @return  string  permissions string
     */
    function _get_perms($paths, $basePath = ROOT_JAWS_PATH)
    {
        $paths = !is_array($paths)? array($paths) : $paths;
        $paths_perms = array();
        foreach ($paths as $path) {
            $path = $basePath . $path;
            $perms = @decoct(@fileperms($path) & 0777);
            if (strlen($perms) < 3) {
                $paths_perms[] = '---------';
                continue;
            }

            $str = '';
            for ($i = 0; $i < 3; $i ++) {
                $str .= ($perms[$i] & 04) ? 'r' : '-';
                $str .= ($perms[$i] & 02) ? 'w' : '-';
                $str .= ($perms[$i] & 01) ? 'x' : '-';
            }
            $paths_perms[] = $str;
        }

        return (count($paths_perms) == 1)? $paths_perms[0] : $paths_perms;
    }

}