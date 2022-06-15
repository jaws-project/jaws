<?php
require_once PEAR_PATH. 'MDB2.php';
require ROOT_JAWS_PATH . 'include/Jaws/ORM.php';

/**
 * Wrapper of Jaws queries and MDB2
 *
 * @category   Database
 * @package    Core
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_DB
{
    /**
     * The MDB2 object
     *
     * @var     object
     * @access  private
     */
    var $dbc;

    /**
     * The MDB2_Schmae object
     *
     * @var     object
     * @access  private
     */
    var $schema;

    /**
     * The DB prefix for tables
     *
     * @var     string
     * @access  private
     */
    var $_prefix;

    /**
     * The DB driver we are using
     *
     * @var     string
     * @access  private
     */
    var $_driver;

    /**
     * The DB charset
     *
     * @var     string
     * @access  private
     */
    var $_charset;

    /**
     * This user is DB administrator?
     *
     * @var     bool
     * @access  private
     */
    var $_is_dba;

    /**
     * This DB path
     *
     * @var     string
     * @access  private
     */
    var $_db_path;

    /**
     * This DB options
     *
     * @var     array
     * @access  private
     */
     var $_dsn;

    /**
     * Jaws_DB instances
     *
     * @var     array
     * @access  private
     */
    private static $instances = array();

    /**
     * Instance name of loaded object
     *
     * @var     string
     * @access  private
     */
    private $instance = '';

    /**
     * Constructor
     *
     * @access  public
     * @param   array  $options  Database connection options
     * @return  void
     */
    function __construct($options, $instance)
    {
        $this->instance = $instance;
        $options['driver'] = strtolower($options['driver']);
        $this->_dsn = array(
            'phptype'  => $options['driver'],
            'username' => $options['user'],
            'password' => $options['password'],
            'hostspec' => $options['host'],
            'database' => $options['name'],
        );

        //set charset
        $options['charset'] = isset($options['charset'])? $options['charset'] : $this->getUnicodeCharset();
        if (!empty($options['charset'])) {
            $this->_dsn['charset'] = $options['charset'];
        }

        if (!empty($options['port'])) {
            $this->_dsn['port'] = $options['port'];
        }

        $this->_db_path = isset($options['path'])? $options['path'] : '';
        $this->_is_dba  = $options['isdba'] == 'true' ? true : false;
        $this->_driver  = $options['driver'];
        $this->_prefix  = $options['prefix'];
        $this->_charset = $options['charset'];

        $this->connect();

        // Set Assoc as default fetch mode.
        $this->dbc->setFetchMode(MDB2_FETCHMODE_ASSOC);
    }

    /**
     * Get a Jaws_DB instance
     *
     * @access  public
     * @param   array  $options  Database connection options
     * @param   string $instance Jaws_DB instance name
     * @return  object Jaws_DB instance
     */
    static function getInstance($instance = 'default', $options = array(), $wdb_options = array())
    {
        if (!isset(self::$instances[$instance])) {
            if ($instance !== 'default') {
                // try use default instance options if not passed
                $default_options = self::$instances['default']->getDBOptions();
                if ((!isset($options['driver']) ||
                        $options['driver'] == $default_options['driver']
                    ) &&
                    (!isset($options['user']) ||
                        $options['user'] == $default_options['user']
                    ) &&
                    (!isset($options['password']) ||
                        $options['password'] == $default_options['password']
                    ) &&
                    (!isset($options['host']) ||
                        $options['host'] == $default_options['host']
                    ) &&
                    (!isset($options['port']) ||
                        $options['port'] == $default_options['port']
                    ) &&
                    (!isset($options['name']) ||
                        $options['name'] == $default_options['name']
                    )
                ) {
                    $options = array_merge($default_options, $options);
                }
            }

            self::$instances[$instance] = new Jaws_DB($options, $instance);
            if (empty($wdb_options)) {
                self::$instances[$instance.'_write'] = self::$instances[$instance];
            } else {
                $wdb_options = array_merge($options, $wdb_options);
                self::$instances[$instance.'_write'] = new Jaws_DB($wdb_options, $instance);
            }
        }

        return self::$instances[$instance];
    }

    /**
     * Connect to database
     *
     * @access  public
     * @return  mixed   True if connected successfully otherwise Jaws_Error on failure
     */
    function connect()
    {
        $options = array(
            'debug' => false,
            'portability' => (
                MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_EMPTY_TO_NULL ^
                MDB2_PORTABILITY_FIX_CASE ^ MDB2_PORTABILITY_RTRIM
            ),
            'quote_identifier' => true,
        );

        switch ($this->_dsn['phptype']) {
            case 'ibase':
                $options['database_path'] = empty($this->_db_path)? ROOT_DATA_PATH : $this->_db_path;
                $options['portability'] = $options['portability'] | MDB2_PORTABILITY_FIX_CASE;
                break;

            case 'oci8':
                $options['emulate_database'] = false;
                $options['portability'] = $options['portability'] | MDB2_PORTABILITY_FIX_CASE;
                break;

            case 'sqlite':
                $options['database_path'] = empty($this->_db_path)? ROOT_DATA_PATH : $this->_db_path;
                break;

            case 'mssql':
                $options['multibyte_text_field_type'] = $this->Is_FreeTDS_MSSQL_Driver();
                break;
        }

        if ($this->_is_dba) {
            $options['DBA_username'] = $this->_dsn['username'];
            $options['DBA_password'] = $this->_dsn['password'];
        }

        $this->dbc = MDB2::singleton($this->_dsn, $options);
        if (MDB2::isError($this->dbc)) {
            return Jaws_Error::raiseError(
                "Couldn't connect to the database<br />".
                $this->dbc->getMessage(). '<br />'. $this->dbc->getUserinfo(),
                __FUNCTION__
            );
        }

        return true;
    }

    /**
     * Get DB Driver options
     *
     * @access  public
     * @return  array  DB Driver options
     */
    function getDBOptions()
    {
        return array(
            'driver'   => $this->_dsn['phptype'],
            'host'     => $this->_dsn['hostspec'],
            'port'     => isset($this->_dsn['port'])? $this->_dsn['port'] : '',
            'name'     => $this->_dsn['database'],
            'user'     => $this->_dsn['username'],
            'password' => $this->_dsn['password'],
            'charset'  => $this->_dsn['charset'],
            'isdba'    => $this->_is_dba? 'true' : 'false',
            'prefix'   => $this->_prefix,
            'path'     => $this->_db_path,
        );
    }

    /**
     * Get the driver name we are using
     *
     * @access  public
     * @return  string  DB Driver
     */
    function getDriver()
    {
        return $this->_driver;
    }

    /**
     * Get DB server version information
     *
     * @access  public
     * @param   bool    $native determines if the raw version string should be returned
     * @return  string  DB Driver
     */
    function getDBVersion($native = true)
    {
        $dbInfo = $this->dbc->getServerVersion($native);
        return MDB2::isError($dbInfo)? '' : $dbInfo;
    }

    /**
     * Get the Database info
     *
     * @access  public
     * @return  array  DB information and options
     */
    function getDatabaseInfo()
    {
        return array(
            'driver'  => $this->_driver,
            'version' => $this->getDBVersion(),
            'host'    => $this->_dsn['hostspec'],
            'port'    => array_key_exists('port', $this->_dsn)? $this->_dsn['port'] : '',
            'name'    => $this->_dsn['database'],
            'prefix'  => $this->_prefix
        );
    }

    /**
     * Get the DB tables prefix
     *
     * @access  public
     * @return  string  Tables prefix
     */
    function getPrefix()
    {
        return $this->_prefix;
    }

    /**
     * Get the DB charset
     *
     * @access  public
     * @return  string  DB charset
     */
    function getCharset()
    {
        return $this->_charset;
    }

    /**
     * Execute a manipulation query to the database and return any the affected rows
     *
     * @param   string  $query  SQL query string
     * @param   array   $params replace values in the query
     * @param   int     $level  The severity level if error occurred
     * @return  mixed a result handle or MDB2_OK on success, a MDB2 error on failure
     * @access  public
     */
    function query($sql, $options = array())
    {
        $options['error_level'] = isset($options['error_level'])? $options['error_level'] : JAWS_ERROR_ERROR;
        if (isset($options['params']) &&
            (isset($options['parse']) && $options['parse'])
        ) {
            $sql = $this->sqlParse($sql, $options['params']);
        }

        $result = self::$instances[$this->instance . '_write']->dbc->exec($sql);
        return MDB2::isError($result)? $result : (string)$result;
    }

    /**
     * Execute the specified query, fetch the value from the first column of
     * the first row of the result set and then frees
     * the result set.
     *
     * @param   string $query the SELECT query statement to be executed.
     * @param   array $params replace values in the query
     * @param   string $type optional argument that specifies the expected
     *       datatype of the result set field, so that an eventual conversion
     *       may be performed. The default datatype is text, meaning that no
     *       conversion is performed
     * @return  mixed MDB2_OK or field value on success, a MDB2 error on failure
     * @access  public
     */
    function queryOne($sql, $params = array(), $type = null)
    {
        $sql = $this->sqlParse($sql, $params);
        $result = $this->dbc->queryOne($sql, $type);
        if (MDB2::isError($result)) {
            $GLOBALS['log']->Log(JAWS_ERROR_ERROR, $result->getUserInfo(), 2);
            return new Jaws_Error(
                $result->getMessage(),
                $result->getCode(),
                JAWS_ERROR_ERROR,
                1
            );
        }

        if ($type === null) {
            return (string)$result;
        }

        return $result;
    }

    /**
     * Execute the specified query, fetch the values from the first
     * row of the result set into an array and then frees
     * the result set.
     *
     * @param   string $query the SELECT query statement to be executed.
     * @param   array $params replace values in the query
     * @param   array $types optional array argument that specifies a list of
     *       expected datatypes of the result set columns, so that the eventual
     *       conversions may be performed. The default list of datatypes is
     *       empty, meaning that no conversion is performed.
     * @param   int $fetchmode how the array data should be indexed
     * @return  mixed MDB2_OK or data array on success, a MDB2 error on failure
     * @access  public
     */
    function queryRow($sql, $params = array(), $types = null, $fetchmode = MDB2_FETCHMODE_DEFAULT)
    {
        $sql = $this->sqlParse($sql, $params);
        $result = $this->dbc->queryRow($sql, $types, $fetchmode);
        if (MDB2::isError($result)) {
            $GLOBALS['log']->Log(JAWS_ERROR_ERROR, $result->getUserInfo(), 2);
            return new Jaws_Error(
                $result->getMessage(),
                $result->getCode(),
                JAWS_ERROR_ERROR,
                1
            );
        }

        return (array)$result;
    }

    /**
     * Execute the specified query, fetch all the rows of the result set into
     * a two dimensional array and then frees the result set.
     *
     * @param   string $query the SELECT query statement to be executed.
     * @param   array $params replace values in the query
     * @param   array $types optional array argument that specifies a list of
     *       expected datatypes of the result set columns, so that the eventual
     *       conversions may be performed. The default list of datatypes is
     *       empty, meaning that no conversion is performed.
     * @param   int $fetchmode how the array data should be indexed
     * @param   bool    $rekey if set to true, the $all will have the first
     *       column as its first dimension
     * @param   bool    $force_array used only when the query returns exactly
     *       two columns. If true, the values of the returned array will be
     *       one-element arrays instead of scalars.
     * @param   bool    $group if true, the values of the returned array is
     *       wrapped in another array.  If the same key value(in the first
     *       column) repeats itself, the values will be appended to this array
     *       instead of overwriting the existing values.
     * @return  mixed MDB2_OK or data array on success, a MDB2 error on failure
     * @access  public
     */
    function queryAll($sql, $params = array(), $types = null, $fetchmode = MDB2_FETCHMODE_DEFAULT,
        $rekey = false, $force_array = false, $group = false)
    {
        $sql = $this->sqlParse($sql, $params);
        $result = $this->dbc->queryAll($sql, $types, $fetchmode, $rekey, $force_array, $group);
        if (MDB2::isError($result)) {
            $GLOBALS['log']->Log(JAWS_ERROR_ERROR, $result->getUserInfo(), 2);
            return new Jaws_Error(
                $result->getMessage(),
                $result->getCode(),
                JAWS_ERROR_ERROR,
                1
            );
        }

        return (array)$result;
    }

    /**
     * Execute the specified query, fetch the value from the first column of
     * each row of the result set into an array and then frees the result set.
     *
     * @param   string $query the SELECT query statement to be executed.
     * @param   array $params replace values in the query
     * @param   string $type optional argument that specifies the expected
     *       datatype of the result set field, so that an eventual conversion
     *       may be performed. The default datatype is text, meaning that no
     *       conversion is performed
     * @param   int $colnum the row number to fetch
     * @return  mixed MDB2_OK or data array on success, a MDB2 error on failure
     * @access  public
     */
    function queryCol($sql, $params = array(), $type = null, $colnum = 0)
    {
        $sql = $this->sqlParse($sql, $params);
        $result = $this->dbc->queryCol($sql, $type, $colnum);
        if (MDB2::isError($result)) {
            $GLOBALS['log']->Log(JAWS_ERROR_ERROR, $result->getUserInfo(), 2);
            return new Jaws_Error(
                $result->getMessage(),
                $result->getCode(),
                JAWS_ERROR_ERROR,
                1
            );
        }

        return (array)$result;
    }

    /**
     * returns the DB unicode charset
     *
     * @return  string
     * @access  public
     */
    function getUnicodeCharset()
    {
        switch ($this->_dsn['phptype']) {
            case 'mysql':
            case 'mysqli':
                return 'utf8';
            case 'pgsql':
                return 'UNICODE';
            case 'oci8':
                return 'UTF8';
            case 'sqlsrv':
                return 'UTF-8';
            default:
                return '';
        }
    }

    /**
     * returns the auto-increment ID if supported or $id
     *
     * @param   arrayed $id value as returned by getBeforeId()
     * @param   string $table name of the table into which a new row was inserted
     * @return  mixed MDB2 Error Object or id
     * @access  public
     */
    function lastInsertID($table = null, $field = null, $parse = true)
    {
        if ($parse) {
            $table = $this->getPrefix() . $table;
            $field = $field;
        }
        $result = self::$instances[$this->instance . '_write']->dbc->lastInsertID(
            $table,
            $field
        );

        return $result;
    }

    /**
     * Start a transaction
     *
     * @access  public
     * @return  mixed   True on success, a Jaws_Error error on failure
     */
    function beginTransaction()
    {
        return self::$instances[$this->instance . '_write']->dbc->beginTransaction();
    }

    /**
     * Cancel any database changes done during a transaction
     *
     * @access  public
     * @return  mixed   True on success, a Jaws_Error error on failure
     */
    function rollback()
    {
        return self::$instances[$this->instance . '_write']->dbc->rollback();
    }

    /**
     * Commit the database changes done during a transaction
     *
     * @access  public
     * @return  mixed   True on success, a Jaws_Error error on failure
     */
    function commit()
    {
        return self::$instances[$this->instance . '_write']->dbc->commit();
    }

    /**
     * returns the next free id of a sequence if the RDBMS
     * does not support auto increment
     *
     * @access  public
     * @param   string name of the table into which a new row was inserted
     * @param   string name of the field that the sequence belongs to
     * @param   bool when true the sequence is automatic created, if it not exists
     * @param   bool if the returned value should be quoted
     * @return  mixed MDB2 Error Object or id
     */
    function getBeforeId($table, $field, $ondemend = true, $quote = true)
    {
        if ($this->dbc->supports('auto_increment') !== true) {
            $table = $this->getPrefix() . $table;
            $seq = $table . '_' . $field;
            $id = self::$instances[$this->instance . '_write']->dbc->nextID($seq, $ondemend);
            if (!$quote || MDB2::isError($id)) {
                return $id;
            }

            return self::$instances[$this->instance . '_write']->dbc->quote($id, 'integer');
        } elseif (!$quote) {
            return null;
        }

        if ($this->_dsn['phptype'] == 'pgsql') {
            return 'DEFAULT';
        }

        return 'NULL';
    }

    /**
     * returns the autoincrement ID if supported or $id
     *
     * @access  public
     * @param   string name of the table into which a new row was inserted
     * @param   string name of the field into which a new row was inserted
     * @return  mixed MDB2 Error Object or id
     */
    function getAfterId($table, $field = null)
    {
        if ($this->dbc->supports('auto_increment') === false) {
            return null;
        }
        return $this->lastInsertID($table, $field);
    }

    /**
     * gives you a dump of the table
     *
     * @param   string  $file   Sump file path
     * @param   string  $type   Type of data/structure to dump('all', 'structure', 'content')
     * @return  string  Dumped file path
     */
    function Dump($file, $type = '')
    {
        @set_time_limit(0);
        require_once PEAR_PATH. 'MDB2/Schema.php';

        $dsn = $this->_dsn;

        $options = array(
            'debug' => false,
            'log_line_break' => '<br />',
            'portability' => (
                MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_EMPTY_TO_NULL ^
                MDB2_PORTABILITY_FIX_CASE ^ MDB2_PORTABILITY_RTRIM
            ),
            'quote_identifier' => true
        );

        $schema =& MDB2_Schema::factory($dsn, $options);
        if (MDB2::isError($schema)) {
            return Jaws_Error::raiseError($schema->getMessage());
        }

        switch ($type) {
            case 'structure':
                $dump_what = MDB2_SCHEMA_DUMP_STRUCTURE;
                break;
            case 'content':
                $dump_what = MDB2_SCHEMA_DUMP_CONTENT;
                break;
            default:
                $dump_what = MDB2_SCHEMA_DUMP_ALL;
                break;
        }

        $config = array(
            'output_mode' => 'file',
            'output' => $file
        );

        $DBDef = $schema->getDefinitionFromDatabase();
        if (MDB2::isError($DBDef)) {
            return Jaws_Error::raiseError($DBDef->getMessage());
        }

        $res = $schema->dumpDatabase($DBDef, $config, $dump_what);
        if (MDB2::isError($res)) {
            return Jaws_Error::raiseError($res->getMessage());
        }

        return $file;
    }

    /**
     * set the range of the next query
     *
     * @param   string $limit number of rows to select
     * @param   string $offset first row to select
     * @return  mixed MDB2_OK on success, a MDB2 error on failure
     * @access  public
     */
    function setLimit($limit, $offset = null)
    {
        $result = $this->dbc->setLimit($limit, $offset);
        if (MDB2::isError($result)) {
            $GLOBALS['log']->Log(JAWS_ERROR_ERROR, $result->getUserInfo(), 2);
            return new Jaws_Error(
                $result->getMessage(),
                $result->getCode(),
                JAWS_ERROR_ERROR,
                1
            );
        }

        return $result;
    }

    /**
     * Quote wrapper for MDB2-Jaws
     *
     * @access  public
     * @param   string  $value   Value to quote
     * @param   string  $type    Value type (integer, text, boolean)
     * @return  string  Quoted value
     */
    function quote($value, $type = 'text')
    {
        if (in_array($type, array('text', 'integer', 'value'))) {
            return $this->dbc->quote($value, $type);
        }
        return $value;
    }

    /**
     * drop an existing table via MDB2 management module
     *
     * @access  public
     * @param   string  $table  name of table
     * @return  mixed   MDB2_OK on success, a MDB2 error on failure
     */
    function dropTable($table)
    {
        self::$instances[$this->instance . '_write']->dbc->loadModule('Manager');
        $result = self::$instances[$this->instance . '_write']->dbc->manager->dropTable(
            $this->getPrefix() . $table
        );
        if (MDB2::isError($result)) {
            if ($result->getCode() !== MDB2_ERROR_NOSUCHTABLE) {
                return Jaws_Error::raiseError($result->getMessage(), $result->getCode(), JAWS_ERROR_ERROR, 1);
            }
        }

        return true;
    }

    /**
     * truncate a table via MDB2 management module
     *
     * @access  public
     * @param   string  $table  name of table
     * @return  mixed   MDB2_OK on success, a MDB2 error on failure
     */
    function truncateTable($table)
    {
        self::$instances[$this->instance . '_write']->dbc->loadModule('Manager');
        $result = self::$instances[$this->instance . '_write']->dbc->manager->truncateTable(
            $this->getPrefix() . $table
        );
        if (MDB2::isError($result)) {
            if ($result->getCode() !== MDB2_ERROR_NOSUCHTABLE) {
                return Jaws_Error::raiseError($result->getMessage(), $result->getCode(), JAWS_ERROR_ERROR, 1);
            }
        }

        return true;
    }

    /**
     * Quote a string so it can be safely used as a table or column name
     *
     * @access  public
     * @param   string|array    $statement  Statement text
     * @return  string  parsed sql string with [[table]] and {field} replaced
     */
    function quoteIdentifier($statement)
    {
        $statement = is_array($statement)? $statement[1] : $statement;
        return $this->dbc->quoteIdentifier($this->_prefix. $statement);
    }

    /**
     * Executes a query
     *
     * @access  public
     * @param   string  SQL To Execute
     * @param   array   Array that has the params to replace
     * @return  string  parsed sql string with [[table]] and {field} replaced
     */
    function sqlParse($sql, $params = null)
    {
        $sql = preg_replace_callback(
            '@\[\[(.*?)\]\]@sm',
            array($this, 'quoteIdentifier'),
            $sql
        );
        $sql = preg_replace_callback(
            '@\[(.*?)\]@sm',
            array($this, 'quoteIdentifier'),
            $sql
        );

        if (is_array($params)) {
            foreach ($params as $key => $param) {
                if (is_array($param)) {
                    $value = $param['value'];
                    $type  = $param['type'];
                } else {
                    $value = $param;
                    $type  = null;
                }

                //Add "N" character before text field value,
                //when using FreeTDS as MSSQL driver, to supporting unicode text
                if ($this->_dsn['phptype'] == 'mssql' && is_string($value) && $this->Is_FreeTDS_MSSQL_Driver()) {
                    $value = 'N' . $this->dbc->quote($value, $type);
                } else {
                    $value = $this->dbc->quote($value, $type);
                }

                $sql = str_replace('{'.$key.'}', $value, $sql);
            }
        }

        $GLOBALS['log']->Log(JAWS_DEBUG, "Query:\n".$sql, 2);
        return $sql;
    }

    /**
     * Installs table(s)/data schema into database
     *
     * @access  public
     * @param   string  $new_schema     New schema file path/name
     * @param   array   $variables      Schema variables
     * @param   string  $old_schema     Old schema file path/name
     * @param   string  $init_data      Schema is include initialization data
     * @param   string  $create         If the database should be created
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function installSchema($new_schema, $variables = array(), $old_schema = false, $init_data = false, $create = true)
    {
        MDB2::loadFile('Schema');

        $dsn = $this->_dsn;
        unset($dsn['database']);

        // If the database should be created
        $variables['create'] = (int)$create;
        // The database name
        $variables['database'] = $this->_dsn['database'];
        // Prefix of all the tables added
        $variables['table_prefix'] = $this->getPrefix();
        // set default charset
        if (!isset($variables['charset'])) {
            $variables['charset'] = $this->getUnicodeCharset();
        }

        $options = array(
            'debug' => false,
            'log_line_break' => '<br />',
            'portability' => (
                MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_EMPTY_TO_NULL ^
                MDB2_PORTABILITY_FIX_CASE ^ MDB2_PORTABILITY_RTRIM
            ),
            'quote_identifier' => true,
            'force_defaults' => false,
            //'dtd_file' => '',
        );

        switch ($this->_dsn['phptype']) {
            case 'ibase':
                $options['portability'] = $options['portability'] | MDB2_PORTABILITY_FIX_CASE;
                $options['database_path'] = empty($this->_db_path)? ROOT_DATA_PATH : $this->_db_path;
                break;

            case 'oci8':
                $options['emulate_database'] = false;
                $options['portability'] = $options['portability'] | MDB2_PORTABILITY_FIX_CASE;
                break;

            case 'sqlite':
                $options['database_path'] = empty($this->_db_path)? ROOT_DATA_PATH : $this->_db_path;
                break;

            case 'mssql':
                $options['multibyte_text_field_type'] = $this->Is_FreeTDS_MSSQL_Driver();
                break;
        }

        if ($this->_is_dba) {
            $options['DBA_username'] = $this->_dsn['username'];
            $options['DBA_password'] = $this->_dsn['password'];
        }

        if (!isset($this->schema)) {
            $this->schema =& MDB2_Schema::factory(
                self::$instances[$this->instance . '_write']->dbc,
                $options
            );
            if (MDB2::isError($this->schema)) {
                return $this->schema;
            }
        }

        $method = $init_data === true ? 'writeInitialization' : 'updateDatabase';
        $result = $this->schema->$method($new_schema, $old_schema, $variables);
        if (MDB2::isError($result)) {
            $this->schema->disconnect();
            unset($this->schema);
            $GLOBALS['log']->Log(JAWS_ERROR_ERROR, $result->getUserInfo(), 2);
            return new Jaws_Error(
                $result->getMessage(),
                $result->getCode(),
                JAWS_ERROR_ERROR,
                1
            );
        }

        return $result;
    }

    /**
     * return the current datetime
     *
     * @access  public
     * @param   int     $timestamp  Time stamp
     * @return  string  current datetime in the MDB2 format
     */
    function date($timestamp = 0)
    {
        return empty($timestamp)? gmdate('Y-m-d H:i:s') : date('Y-m-d H:i:s', (int)$timestamp);
    }

    /**
     * Detect mssql driver is FreeTDS
     *
     * @access  public
     * @return  bool
     */
    function Is_FreeTDS_MSSQL_Driver()
    {
        static $freeTDS;
        if (!isset($freeTDS)) {
            ob_start();
            @phpinfo(INFO_MODULES);
            $info = ob_get_contents();
            ob_end_clean();
            $freeTDS = stripos($info, 'FreeTDS') !== false;
        }

        return $freeTDS;
    }

}