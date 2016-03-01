<?php
/**
 * Class that deals like a wrapper between Jaws and pear/Net_FTP
 *
 * @category   FTP
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_FTP
{
    /**
     * The host to connect to
     *
     * @access  private
     * @var     string
     */
    private $_host;

    /**
     * The port for ftp-connection (standard is 21)
     *
     * @access  private
     * @var     int
     */
    private $_port = 21;

    /**
     * The username for login
     *
     * @access  private
     * @var     string
     */
    private $_username;

    /**
     * The password for login
     *
     * @access  private
     * @var     string
     */
    private $_password;

    /**
     * The base dir
     *
     * @access  private
     * @var     string
     */
    private $_root;

    /**
     * Determine whether to use passive-mode (true) or active-mode (false)
     *
     * @access  private
     * @var     bool
     */
    private $_passive;

    /**
     * This holds the Net_FTP instance
     *
     * @access  private
     * @var     resource
     */
    private $_ftp;

    /**
     * Constructor
     *
     * @access  private
     * @return  void
     */
    private function __construct($options = array())
    {
        require_once PEAR_PATH. 'Net/FTP.php';
        $this->_ftp = new Net_FTP();

        $this->_host     = isset($options['host'])? $options['host'] : '';
        $this->_port     = isset($options['port'])? $options['port'] : 21;
        $this->_passive  = isset($options['passive'])? $options['passive'] : true;
        $this->_username = isset($options['username'])? $options['username'] : '';
        $this->_password = isset($options['password'])? $options['password'] : '';
        $this->_root     = isset($options['root'])? $options['root'] : '';
    }

    /**
     * Get a Jaws_FTP instance
     *
     * @access  public
     * @param   string $instance    Jaws_FTP instance name
     * @param   array  $options     FTP connection options
     * @return  object Jaws_DB instance
     */
    static function getInstance($instance = 'default', $options = array())
    {
        static $instances;
        if (!isset($instances)) {
            $instances = array();
        }
        if (!isset($instances[$instance])) {
            if ($instance == 'default') {
                $defaultOptions = $GLOBALS['app']->Registry->fetchAll('Settings');
                $options['host']     = $defaultOptions['ftp_host'];
                $options['port']     = $defaultOptions['ftp_port'];
                $options['passive']  = $defaultOptions['ftp_mode'] == 'passive';
                $options['username'] = $defaultOptions['ftp_user'];
                $options['password'] = $defaultOptions['ftp_pass'];
                $options['root']     = $defaultOptions['ftp_root'];
            }
            $instances[$instance] = new Jaws_FTP($options);
        }

        return $instances[$instance];
    }

    /**
     * This function generates the FTP-connection
     *
     * @access  public
     * @param   string $host    (optional) The hostname
     * @param   int    $port    (optional) The port
     * @return  mixed           True on success, otherwise Jaws_Error
     */
    function connect($host = null, $port = null)
    {
        if (isset($host)) {
            $this->_host = $host;
        }
        if (isset($port)) {
            $this->_port = $port;
        }

        $this->_ftp->setHostname($this->_host);
        $this->_ftp->setPort($this->_port);
        $res = $this->_ftp->connect();
        if (PEAR::isError($res)) {
            return new Jaws_Error('Error while connecting to server '.$this->_host.' on '.$this->_port.'.',
                                  __FUNCTION__);
        }

        return true;
    }

    /**
     * This function close the FTP-connection
     *
     * @access  public
     * @return  mixed Returns true on success, Jaws_Error on failure
     */
    function disconnect()
    {
        $res = $this->_ftp->disconnect();
        if (PEAR::isError($res)) {
            return new Jaws_Error('Error while disconnecting from server '.$this->_host,
                                  __FUNCTION__);
        }

        return true;
    }

    /**
     * This logges you into the ftp-server.
     *
     * @access  public
     * @param   string $username  (optional) The username to use 
     * @param   string $password  (optional) The password to use
     * @return  mixed             True on success, otherwise Jaws_Error
     */
    function login($username = null, $password = null)
    {
        if (isset($username)) {
            $this->_username = $username;
        }
        if (isset($password)) {
            $this->_password = $password;
        }

        $this->_ftp->setUsername($this->_username);
        $this->_ftp->setPassword($this->_password);
        $res = $this->_ftp->login();
        if (PEAR::isError($res)) {
            return new Jaws_Error('Error while login into server.',
                                  __FUNCTION__);
        }

        return true;
    }

    /**
     * This changes the currently used directory
     *
     * @access  public
     * @param   string $dir  The directory to go to.
     * @return  mixed        True on success, otherwise Jaws_Error
     */
    function cd($dir)
    {
        $res = $this->_ftp->cd($dir);
        if (PEAR::isError($res)) {
            return new Jaws_Error($res->getMessage(),
                                  __FUNCTION__);
        }

        return true;
    }

    /**
     * Shows you the actual path on the server
     *
     * @access  public
     * @return  mixed        The actual path or Jaws_Error
     */
    function pwd()
    {
        $res = $this->_ftp->pwd();
        if (PEAR::isError($res)) {
            return new Jaws_Error($res->getMessage(),
                                  __FUNCTION__);
        }

        return $res;
    }

    /**
     * This works similar to the mkdir-command on your local machine.
     *
     * @access  public
     * @param   string $dir       Absolute or relative dir-path
     * @param   bool   $recursive (optional) Create all needed directories
     * @return  mixed             True on success, otherwise Jaws_Error
     */
    function mkdir($dir, $recursive = false)
    {
        $res = $this->_ftp->mkdir($dir, $recursive);
        if (PEAR::isError($res)) {
            return new Jaws_Error($res->getMessage(),
                                  __FUNCTION__);
        }

        return true;
    }

    /**
     * This method will try to chmod the file specified on the server Currently.
     *
     * @access  public
     * @param   mixed   $target        The file or array of files to set permissions for
     * @param   int     $permissions   The mode to set the file permissions to
     * @return  mixed                  True if successful, otherwise Jaws_Error
     */
    function chmod($target, $permissions)
    {
        $res = $this->_ftp->chmod($target, $permissions);
        if (PEAR::isError($res)) {
            return new Jaws_Error($res->getMessage(),
                                  __FUNCTION__);
        }

        return $res;
    }

    /**
     * Rename or move a file or a directory from the ftp-server
     *
     * @access  public
     * @param   string $remote_from The remote file or directory original to rename or move
     * @param   string $remote_to The remote file or directory final to rename or move
     * @return  bool $res True on success, otherwise Jaws_Error
     */
    function rename($remote_from, $remote_to) 
    {
        $res = $this->_ftp->rename($remote_from, $remote_to);
        if (PEAR::isError($res)) {
            return new Jaws_Error($res->getMessage(),
                                  __FUNCTION__);
        }

        return true;
    }

    /**
     * This method will delete the given file or directory ($path) from the server
     *
     * @access  public
     * @param   string $path      The absolute or relative path to the file / directory.
     * @param   bool   $recursive (optional)
     * @return  mixed             True on success, otherwise Jaws_Error
     */
    function rm($path, $recursive = false)
    {
        $res = $this->_ftp->rm($path, $recursive);
        if (PEAR::isError($res)) {
            return new Jaws_Error($res->getMessage(),
                                  __FUNCTION__);
        }

        return true;
    }

    /**
     * This function will download a file from the ftp-server.
     *
     * @access  public
     * @param   string $remote_file The absolute or relative path to the file to download
     * @param   string $local_file  The local file to put the downloaded in
     * @param   bool   $overwrite   (optional) Whether to overwrite existing file
     * @param   int    $mode        (optional) Either FTP_ASCII or FTP_BINARY
     * @return  mixed               True on success, otherwise Jaws_Error
     */
    function get($remote_file, $local_file, $overwrite = false, $mode = null)
    {
        $res = $this->_ftp->get($remote_file, $local_file, $overwrite, $mode);
        if (PEAR::isError($res)) {
            return new Jaws_Error($res->getMessage(),
                                  __FUNCTION__);
        }

        return true;
    }

    /**
     * This function will upload a file to the ftp-server.
     *
     * @access  public
     * @param   string $local_file  The local file to upload
     * @param   string $remote_file The absolute or relative path to the file to upload to
     * @param   bool   $overwrite   (optional) Whether to overwrite existing file
     * @param   int    $mode        (optional) Either FTP_ASCII or FTP_BINARY
     * @return  mixed               True on success, otherwise Jaws_Error
     */
    function put($local_file, $remote_file, $overwrite = false, $mode = null)
    {
        $res = $this->_ftp->put($local_file, $remote_file, $overwrite, $mode);
        if (PEAR::isError($res)) {
            return new Jaws_Error($res->getMessage(),
                                  __FUNCTION__);
        }

        return true;
    }

    /**
     * Set the transfer-mode. You can use the predefined constants FTP_ASCII or FTP_BINARY.
     *
     * @access  public
     * @param   int    $mode  The mode to set
     * @return  mixed         True on success, otherwise Jaws_Error
     */
    function setMode($mode)
    {
        $res = $this->_ftp->setMode($mode);
        if (PEAR::isError($res)) {
            return new Jaws_Error($res->getMessage(),
                                  __FUNCTION__);
        }

        return true;
    }

    /**
     * Set the transfer-method to passive mode
     *
     * @access  public
     * @return  void
     */
    function setPassive()
    {
        $this->_passive = true;
        $this->_ftp->setPassive();
    }

    /**
     * Set the transfer-method to active mode
     *
     * @access  public
     * @return  void
     */
    function setActive()
    {
        $this->_passive = false;
        $this->_ftp->setActive();
    }

    /**
     * Copy directories/files recursively
     *
     * @access  public
       @param   string  $source         Path to the source file or directory
       @param   bool    $self_include   Include top directory level
     * @return  void
     */
    function copy($source, $self_include = true)
    {
        if (is_dir($source)) {
            if (false !== $hDir = @opendir($source)) {
                if ($self_include) {
                    $result = $this->mkdir(basename($source), true);
                    if (Jaws_Error::IsError($result)) {
                        return $result;
                    }

                    $this->cd(basename($source));
                }

                while(false !== ($file = @readdir($hDir))) {
                    if($file == '.' || $file == '..') {
                        continue;
                    }

                    $result = $this->copy($source. DIRECTORY_SEPARATOR . $file);
                    if (Jaws_Error::IsError($result)) {
                        return $result;
                    }
                }

                $this->cd('..');
                closedir($hDir);
            }
        } else {
            $result = $this->put($source, basename($source), true);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        return $result;
    }

}