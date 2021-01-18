<?php
/**
 * Database Stage
 *
 * @category   Application
 * @package    InstallStage
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Helgi �ormar �orbj�rnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Installer_Database extends JawsInstaller
{
    /**
     * Default values.
     * @var string
     * @access protected
     */
    var $_Defaults = array(
        'host'   => 'localhost',
        'driver' => '',
        'user'   => '',
        'isdba'  => '',
        'path'   => '',
        'name'   => 'jaws',
        'prefix' => '',
        'port'   => '',
    );

    /**
     * Builds the installer page.
     *
     * @access  public
     * @return  string A block of valid XHTML to display an introduction and form.
     */
    function Display()
    {
        $jconfig = dirname(dirname(dirname(__FILE__))) . '/config/JawsConfig.php';
        if (Jaws_FileManagement_File::file_exists($jconfig)) {
            @include $jconfig;
        }

        // Get values
        $values = $this->_Defaults;
        foreach ($this->_Defaults as $name => $value) {
            if (isset($_SESSION['install']['Database'][$name])) {
                $values[$name] = $_SESSION['install']['Database'][$name];
            } elseif (isset($db[$name])) {
                $values[$name] = $db[$name];
            }
        }
        $values['isdba'] = !empty($values['isdba']) && $values['isdba'] == 'true';

        $data = array();
        if (isset($_SESSION['install']['data']['Database'])) {
            $data = $_SESSION['install']['data']['Database'];
        }

        $tpl = new Jaws_Template(false, false);
        $tpl->Load('display.html', 'stages/Database/templates');
        $tpl->SetBlock('Database');

        $tpl->setVariable('db_info',   $this->t('DB_INFO'));
        $tpl->setVariable('db_notice', $this->t('DB_NOTICE'));
        $tpl->SetVariable('next',      Jaws::t('NEXT'));

        if ($_SESSION['secure']) {
            $JCrypt = Jaws_Crypt::getInstance(
                array(
                    'pvt_key' => $_SESSION['pvt_key'],
                    'pub_key' => $_SESSION['pub_key'],
                )
            );
            if (!Jaws_Error::IsError($JCrypt)) {
                $tpl->SetVariable('pubkey', $JCrypt->getPublic());
                $tpl->SetVariable('func_onsubmit', 'EncryptPassword(this)');
            } else {
                $_SESSION['secure'] = false;
                $tpl->SetVariable('func_onsubmit', 'true');
            }
        } else {
            $_SESSION['pub_key'] = '';
            $_SESSION['pvt_key'] = '';
            $tpl->SetVariable('func_onsubmit', 'true');
        }

        $fields = 0;
        if (!isset($data['host'])) {
            $fields++;
            $tpl->SetBlock('Database/host');
            $tpl->setVariable('lbl_host',  $this->t('DB_HOST'));
            $tpl->setVariable('host_info', $this->t('DB_HOST_INFO', 'localhost'));
            $tpl->SetVariable('host', $values['host']);
            $tpl->ParseBlock('Database/host');
        }

        if (!isset($data['user'])) {
            $fields++;
            $tpl->SetBlock('Database/user');
            $tpl->setVariable('lbl_user',    $this->t('DB_USER'));
            $tpl->setVariable('is_db_admin', $this->t('DB_IS_ADMIN'));
            $tpl->SetVariable('user', $values['user']);
            $tpl->SetVariable('isdba_checked', $values['isdba']? 'checked="checked"' : '');
            $tpl->ParseBlock('Database/user');
        }

        if (!isset($data['password'])) {
            $fields++;
            $tpl->SetBlock('Database/password');
            $tpl->setVariable('lbl_pass', $this->t('DB_PASS'));
            $tpl->SetVariable('dbpass', '');
            $tpl->ParseBlock('Database/password');
        }

        if (!isset($data['name'])) {
            $fields++;
            $tpl->SetBlock('Database/name');
            $tpl->setVariable('lbl_db_name', $this->t('DB_NAME'));
            $tpl->SetVariable('name', $values['name']);
            $tpl->ParseBlock('Database/name');
        }

        if (!isset($data['path'])) {
            $fields++;
            $tpl->SetBlock('Database/path');
            $tpl->setVariable('lbl_db_path', $this->t('DB_PATH'));
            $tpl->setVariable('path_info',   $this->t('DB_PATH_INFO'));
            $tpl->SetVariable('path', $values['path']);
            $tpl->ParseBlock('Database/path');
        }

        if (!isset($data['port'])) {
            $fields++;
            $tpl->SetBlock('Database/port');
            $tpl->setVariable('lbl_port',  $this->t('DB_PORT'));
            $tpl->setVariable('port_info', $this->t('DB_PORT_INFO'));
            $tpl->SetVariable('port', $values['port']);
            $tpl->ParseBlock('Database/port');
        }

        if (!isset($data['prefix'])) {
            $fields++;
            $tpl->SetBlock('Database/prefix');
            $tpl->setVariable('lbl_prefix',  $this->t('DB_PREFIX'));
            $tpl->setVariable('prefix_info', $this->t('DB_PREFIX_INFO'));
            $tpl->SetVariable('prefix', $values['prefix']);
            $tpl->ParseBlock('Database/prefix');
        }

        // drivers
        if (!isset($data['driver'])) {
            $fields++;
            $tpl->SetBlock('Database/drivers');
            $tpl->setVariable('lbl_driver',  $this->t('DB_DRIVER'));

            $drivers = array(
                'mysqli' => array('ext' => 'mysqli',    'title' => 'MySQLi (4.1.3 and above)'),
                'mysql'  => array('ext' => 'mysql',     'title' => 'MySQL'),
                'pgsql'  => array('ext' => 'pgsql',     'title' => 'PostgreSQL'),
                'oci8'   => array('ext' => 'oci8',      'title' => 'Oracle'),
                'mssql'  => array('ext' => 'mssql',     'title' => 'MSSQL Server'),
                'sqlsrv' => array('ext' => 'sqlsrv',    'title' => 'MSSQL Server(Microsoft Driver)'),
                'ibase'  => array('ext' => 'interbase', 'title' => 'Interbase/Firebird'),
                'sqlite' => array('ext' => 'sqlite',    'title' => 'SQLite 2'),
                /* These databases either haven't been tested or are kown not to work.
                'fbsql'  => 'Frontbase',
                */
            );

            $modules = get_loaded_extensions();
            $modules = array_map('strtolower', $modules);
            foreach ($drivers as $driver => $driver_info) {
                _log(JAWS_DEBUG,"Checking if ".$driver_info['title']. "(".$driver_info['ext'].") driver is available");
                if (!in_array($driver_info['ext'], $modules)) {
                    $available = false;
                    //However... mssql support exists in some Linux distros with the sybase package
                    if ($driver_info['ext'] == 'mssql' && function_exists('mssql_connect')) {
                        $available = true;
                    }
                    
                    if ($available === false) {
                        _log(JAWS_DEBUG,"Driver ".$driver_info['title']. "(".$driver_info['ext'].") is NOT available");
                        continue;
                    }
                }
                _log(JAWS_DEBUG,"Driver ".$driver_info['title']. "(".$driver_info['ext'].") is available");
                $tpl->setBlock('Database/drivers/driver');
                $tpl->setVariable('d_name', $driver);
                $tpl->setVariable('d_realname', $driver_info['title']);
                if ($values['driver'] == $driver) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = '';
                }
                $tpl->setVariable('d_selected', $selected);
                $tpl->ParseBlock('Database/drivers/driver');
            }
            $tpl->ParseBlock('Database/drivers');
        }

        if ($fields === 0 && !isset($GLOBALS['message'])) {
            $_SESSION['install']['Database']['skip'] = '1';
           header('Location: index.php');
        }

        $tpl->ParseBlock('Database');

        return $tpl->Get();
    }

    /**
     * Validates any data provided to the stage.
     *
     * @access  public
     * @return  bool|Jaws_Error  Returns either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function Validate()
    {
        $request = Jaws_Request::getInstance();
        $post = $request->fetch(array('host', 'user', 'name', 'path', 'port'), 'post');
        if (isset($_SESSION['install']['data']['Database'])) {
            $post = $_SESSION['install']['data']['Database'] + $post;
        }

        if (isset($post['path']) && $post['path'] !== '' && !is_dir($post['path'])) {
            _log(JAWS_DEBUG,"The database path must be exists");
            return new Jaws_Error($this->t('DB_RESPONSE_PATH'), 0, JAWS_ERROR_WARNING);
        }

        if (isset($post['port']) && $post['port'] !== '' && !is_numeric($post['port'])) {
            _log(JAWS_DEBUG,"The port can only be a numeric value");
            return new Jaws_Error($this->t('DB_RESPONSE_PORT'), 0, JAWS_ERROR_WARNING);
        }

        if (!empty($post['host']) && !empty($post['user']) && !empty($post['name'])) {
            return true;
        }

        _log(JAWS_DEBUG,"You must fill in all the fields apart from table prefix and port");
        return new Jaws_Error($this->t('DB_RESPONSE_INCOMPLETE'), 0, JAWS_ERROR_WARNING);
    }

    /**
     * Does any actions required to finish the stage, such as DB queries.
     *
     * @access  public
     * @return  bool|Jaws_Error  Either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function Run()
    {
        $keys = array_keys($this->_Defaults);
        $keys[] = 'dbpass';
        $request = Jaws_Request::getInstance();
        $post = $request->fetch($keys, 'post');
        $post['dbpass'] = $request->fetch('dbpass', 'post', false);
        $request->reset();

        if (isset($_SESSION['install']['data']['Database'])) {
            $post = $_SESSION['install']['data']['Database'] + $post;
        }

        if ($_SESSION['secure']) {
            require_once ROOT_JAWS_PATH . 'include/Jaws/Crypt.php';
            $JCrypt =  Jaws_Crypt::getInstance(
                array(
                    'pvt_key' => $_SESSION['pvt_key'],
                    'pub_key' => $_SESSION['pub_key'],
                )
            );
            if (!Jaws_Error::isError($JCrypt)) {
                $post['dbpass'] = $JCrypt->decrypt($post['dbpass']);
            } else {
                return $JCrypt;
            }
        }

        if (substr($post['prefix'], -1) == '_') {
            $prefix = $post['prefix'];
        } elseif (strlen($post['prefix']) > 0) {
            $prefix = $post['prefix'] . '_';
        } else {
            $prefix = $post['prefix'];
        }

        if (!empty($post['path'])) {
            $post['path'] = rtrim($post['path'], "\\/"). '/';
        }

        $_SESSION['install']['Database'] = array(
            'user'     => trim($post['user']),
            'password' => $post['dbpass'],
            'isdba'    => !empty($post['isdba'])? 'true' : 'false',
            'name'     => trim($post['name']),
            'path'     => trim($post['path']),
            'host'     => trim($post['host']),
            'port'     => trim($post['port']),
            'prefix'   => $prefix,
            'driver'   => $post['driver'],
        );

        // Connect to database
        require_once ROOT_JAWS_PATH . 'include/Jaws/DB.php';
        $objDatabase = Jaws_DB::getInstance('default', $_SESSION['install']['Database']);
        if (Jaws_Error::IsError($objDatabase)) {
            _log(JAWS_DEBUG,"There was a problem connecting to the database.");
            return new Jaws_Error($this->t('DB_RESPONSE_CONNECT_FAILED'), 0, JAWS_ERROR_WARNING);
        }

        $variables = array();
        $variables['timestamp'] = Jaws_DB::getInstance()->date();

        $result = Jaws_DB::getInstance()->installSchema('Resources/schema/schema.xml', $variables);
        _log(JAWS_DEBUG,"Installing core schema");
        if (Jaws_Error::isError($result)) {
            _log(JAWS_DEBUG,$result->getMessage());
            return $result;
        }

        // Create application
        require_once ROOT_JAWS_PATH . 'include/Jaws.php';
        $jawsApp = Jaws::getInstance();
        $jawsApp->registry->init();
        $jawsApp->loadPreferences(array('language' => $_SESSION['install']['language']), false);
        Jaws_Translate::getInstance()->LoadTranslation('Install', JAWS_COMPONENT_INSTALL);

        // registry keys
        $result = $jawsApp->registry->insertAll(
            array(
                array('version', JAWS_VERSION),
                array('gadgets_installed_items', ','),
                array('gadgets_disabled_items', ','),
                array('gadgets_autoload_items', ','),
                array('plugins_installed_items', ','),
            )
        );

        if (Jaws_Error::isError($result)) {
            _log(JAWS_DEBUG,$result->getMessage());
        }

        $gadgets = array(
            'UrlMapper', 'Settings', 'ControlPanel',
            'Components', 'Layout', 'Users', 'Policy',
        );

        foreach ($gadgets as $gadget) {
            $objGadget = Jaws_Gadget::getInstance($gadget);
            if (Jaws_Error::IsError($objGadget)) {
                _log(JAWS_DEBUG,"There was a problem installing core gadget: ".$gadget);
                return $objGadget;
            }

            $installer = $objGadget->installer->load();
            $result = $installer->InstallGadget();
            if (Jaws_Error::IsError($result)) {
                _log(JAWS_DEBUG,"There was a problem installing core gadget: ".$gadget);
                return $result;
            }
        }

        return true;
    }

}