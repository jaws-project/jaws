<?php
/**
 * Object-relational mapping class
 *
 * @category   ORM
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_ORM
{
    /**
     * Jaws_DB  object
     *
     * @var     object
     * @access  public
     */
    var $jawsdb;

    /**
     * The DB prefix for tables
     *
     * @var     string
     * @access  private
     */
    var $_tbl_prefix = '';

    /**
     * The DB identifier quoting characters
     *
     * @var     array
     * @access  private
     */
    var $_identifier_quoting = array();

    /**
     * Select distinct statement
     *
     * @var     string
     * @access  private
     */
    var $_distinct = '';

    /**
     * Select columns list
     *
     * @var     array
     * @access  private
     */
    var $_columns = array();

    /**
     * Select columns types list
     *
     * @var     array
     * @access  private
     */
    var $_types = array();

    /**
     * Types passed to select?
     *
     * @var     bool
     * @access  private
     */
    var $_passed_types = false;

    /**
     * Insert/Update columns/values pairs
     *
     * @var     array
     * @access  private
     */
    var $_values = array();

    /**
     * Where conditions list
     *
     * @var     array
     * @access  private
     */
    var $_where = array();

    /**
     * Join options
     *
     * @var     array
     * @access  private
     */
    var $_joins = array();

    /**
     * Group By columns list
     *
     * @var     array
     * @access  private
     */
    var $_groupBy = array();

    /**
     * Having conditions list
     *
     * @var     array
     * @access  private
     */
    var $_having = array();

    /**
     * Order By columns list
     *
     * @var     array
     * @access  private
     */
    var $_orderBy = array();

    /**
     * Number of rows to select
     *
     * @var     int
     * @access  private
     */
    var $_limit  = null;

    /**
     * First row to select
     *
     * @var     int
     * @access  private
     */
    var $_offset = null;

    /**
     * Table name
     *
     * @var     string
     * @access  private
     */
    var $_table = '';

    /**
     * Table primary key name
     *
     * @var     string
     * @access  private
     */
    var $_pk_field;

    /**
     * Table(s) quoted/aliased identifier
     *
     * @var     string
     * @access  private
     */
    var $_tablesIdentifier = '';

    /**
     * SQL command name(insert/update/delete)
     *
     * @var     string
     * @access  private
     */
    var $_query_command = '';

    /**
     * return type of nested select
     *
     * @var     string
     * @access  public
     */
    var $type = '';

    /**
     * Alias name of nested select
     *
     * @var     string
     * @access  public
     */
    var $alias = '';

    /**
     * Not quoted by quoteIdentifier
     *
     * @var     array
     * @access  private
     */
    var $reserved_words = array(',', '+', '-', '/', '*', '?', '<', '>', '<>', 'as', 'desc', 'asc');

    /**
     * Determine if there is an open transaction
     *
     * @var     bool
     * @access  private
     */
    static private $in_transaction = false;

    /**
     * Auto rollback changes on error
     *
     * @var     bool
     * @access  private
     */
    static private $auto_rollback_on_error = true;

    /**
     * Auto log on error
     *
     * @var     bool
     * @access  private
     */
    static private $auto_log_on_error = true;

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    function __construct($db_instance)
    {
        $this->jawsdb = Jaws_DB::getInstance($db_instance);
        $this->_tbl_prefix = $this->jawsdb->GetPrefix();
        $this->_identifier_quoting = $this->jawsdb->dbc->identifier_quoting;
    }

    /**
     * Creates the Jaws_ORM instance
     *
     * @access  public
     * @param   string  $db_instance    Instance name
     * @return  object returns the instance
     */
    static function getInstance($db_instance = 'default')
    {
        return new Jaws_ORM($db_instance);
    }

    /**
     * Sets table name/alias
     *
     * @access  public
     * @param   mixed   $table      Table name
     * @param   string  $alias      Table alias in query
     * @param   string  $pk_field   Table primary key
     * @return  object  Jaws_ORM object
     */
    function table($table, $alias = '', $pk_field = 'id')
    {
        $this->_table = $table;
        $this->_pk_field = $pk_field;

        $alias = empty($alias)? '': (' as '. $this->quoteIdentifier($this->_tbl_prefix.$alias));
        if (is_object($table)) {
            $table_quoted = '('. $table->get(). ')';
        } else {
            $table_quoted = $this->quoteIdentifier($this->_tbl_prefix. $table, true);
        }

        $this->_tablesIdentifier = $table_quoted. $alias;
        return $this;
    }

    /**
     * Quote a string so it can be safely used as a table or column name
     *
     * @access  private
     * @param   string  $column  Column name
     * @return  string  quoted string
     */
    function quoteIdentifier($column, $prefix_aliased = false)
    {
        $prev_is_as = false;
        $parts = array_filter(array_map('trim', explode(' ', $column)));
        foreach ($parts as $idx => $column) {
            if (in_array($column, $this->reserved_words)) {
                $prev_is_as = $column == 'as'? true : false;
                continue;
            }

            if (strpos($column, '[') !== false) {
                $column = str_replace('[',  $this->_identifier_quoting['start'], $column);
                $column = str_replace(']',  $this->_identifier_quoting['end'],   $column);
            } else {
                // auto quote identifier if bracket not found
                if (false !== $dotted_column = strpos($column, '.')) {
                    $column = str_replace(
                        '.',
                        $this->_identifier_quoting['end']. '.'. $this->_identifier_quoting['start'],
                        $column
                    );
                }

                if (false !== $open_parenthesis = strpos($column, '(')) {
                    $column = str_replace(
                        '(',
                        '('. $this->_identifier_quoting['start']. ($dotted_column? $this->_tbl_prefix : ''),
                        $column
                    );

                    $column = str_replace(
                        ')',
                        $this->_identifier_quoting['end']. ')',
                        $column
                    );
                } else {
                    if (($prev_is_as && $prefix_aliased) || $dotted_column) {
                        $prev_is_as  = false;
                        $column = $this->_tbl_prefix. $column;
                    }

                    $column = $this->_identifier_quoting['start']. $column. $this->_identifier_quoting['end'];
                }
            }

            $parts[$idx] = $column;
        }

        return implode(' ', $parts);
    }

    /**
     * Quote value so it can be safely using
     *
     * @access  private
     * @param   mixed   $value
     * @return  string  quoted value
     */
    function quoteValue($value)
    {
        if (is_object($value)) {
            $vstr = '('. $value->get(). ')';
            unset($value);
        } else {
            if (is_array($value)) {
                // $value is array(value, type)
                $value[1] = isset($value[1])? $value[1] : null;
                if ($value[1] == 'expr') {
                    $vstr = $this->quoteIdentifier($value[0]);
                } else {
                    $vstr = $this->jawsdb->dbc->quote($value[0], $value[1]);
                }
            } else {
                // Add "N" character before text field value,
                // when using FreeTDS as MSSQL driver, to supporting unicode text
                if ($this->jawsdb->_dsn['phptype'] == 'mssql' &&
                    is_string($value) &&
                    $this->jawsdb->Is_FreeTDS_MSSQL_Driver())
                {
                    $vstr = 'N' . $this->dbc->quote($value);
                } else {
                    $vstr = $this->jawsdb->dbc->quote($value);
                }
            }
        }

        return $vstr;
    }

    /**
     * Select distinct statement
     *
     * @access  public
     * @return  object  Jaws_ORM object
     */
    function distinct()
    {
        $this->_distinct = 'distinct ';
        return $this;
    }

    /**
     * Select SQL command
     *
     * @access  public
     * @param   array   $columns    select column list
     * @return  object  Jaws_ORM object
     */
    function select($columns)
    {
        $this->_columns = is_array($columns)? $columns : func_get_args();
        foreach($this->_columns as $key => $column) {
            if (is_object($column)) {
                $this->_columns[$key] = '('. $column->get(). ')';
                $type   = $column->type;
                $alias  = $column->alias;
                $this->_columns[$key].= empty($alias)? '' : (' as '. $this->quoteIdentifier($alias));
                unset($column);
            } else {
                if ($type = trim(strrchr($column, ':'), ':')) {
                    $this->_columns[$key] = $this->quoteIdentifier(substr($column, 0, strrpos($column, ':')));
                } else {
                    $this->_columns[$key] = $this->quoteIdentifier($column);
                }
            }

            if (empty($type)) {
                $this->_types[] = 'text';
            } else {
                $this->_types[] = $type;
                $this->_passed_types = true;
            }
        }

        return $this;
    }

    /**
     * Join SQL command
     *
     * @access  public
     * @param   string  $table  Join target table
     * @param   string  $source Join source field
     * @param   string  $target Join target field
     * @param   string  $join   Join type
     * @param   string  $opt    Join condition
     * @return  object  Jaws_ORM object
     */
    function join($table, $source, $target, $join = 'inner', $opt = '=')
    {
        $table  = $this->quoteIdentifier($this->_tbl_prefix. $table, true);
        $source = $this->quoteIdentifier($source);
        $target = $this->quoteIdentifier($target);

        $this->_joins[] = "$join join $table on $source $opt $target";
        return $this;
    }

    /**
     * Where SQL command
     *
     * @access  public
     * @param   mixed   $column Column
     * @param   mixed   $value  Column value
     * @param   string  $opt    Operator condition
     * @return  object  Jaws_ORM object
     */
    function where($column, $value, $opt = '=')
    {
        switch ($opt) {
            case 'in':
            case 'not in':
                if (is_object($value)) {
                    $value = $this->quoteValue($value);
                } else {
                    $value = '('. implode(', ', array_map(array($this, 'quoteValue'), $value)). ')';
                }
                break;

            case 'between':
            case 'not between':
                $value = $this->quoteValue($value[0]). ' and '. $this->quoteValue($value[1]);
                break;

            case 'like':
            case 'not like':
                $value  = $this->quoteValue($value);
                break;

            case 'is null':
            case 'is not null':
                $value  = '';
                break;

            default:
                $value  = $this->quoteValue($value);
        }

        // quote column identifier
        if (is_object($column)) {
            $colstr = '('. $column->get(). ')';
            unset($column);
        } elseif (is_array($column)) {
            $colstr = $this->quoteValue($column);
        } else {
            $colstr = $this->quoteIdentifier($column);
        }

        $this->_where[] = "($colstr $opt $value)";
        return $this;
    }

    /**
     * Where SQL command prefix with open parenthesis
     *
     * @access  public
     * @param   string  $column Column
     * @param   string  $value  Column value
     * @param   string  $opt    Operator condition
     * @return  object  Jaws_ORM object
     */
    function openWhere($column = '', $value = '', $opt = '=')
    {
        $this->_where[] = '(';
        if (!empty($column)) {
            $this->where($column, $value, $opt);
        }

        return $this;
    }

    /**
     * Where SQL command suffix with open parenthesis
     *
     * @access  public
     * @param   string  $column Column
     * @param   string  $value  Column value
     * @param   string  $opt    Operator condition
     * @return  object  Jaws_ORM object
     */
    function closeWhere($column = '', $value = '', $opt = '=')
    {
        if (!empty($column)) {
            $this->where($column, $value, $opt);
        } else {
            if (in_array(end($this->_where), array(' and ', ' or '))) {
                array_pop($this->_where);
            }
        }

        $this->_where[] = ')';
        return $this;
    }

    /**
     * Group by SQL command
     *
     * @access  public
     * @param   array   $columns    select column list
     * @return  object  Jaws_ORM object
     */
    function groupBy($columns)
    {
        foreach(func_get_args() as $column) {
            // quote column identifier
            if (is_object($column)) {
                $colstr = '('. $column->get(). ')';
                unset($column);
            } else {
                $colstr = $this->quoteIdentifier($column);
            }

            $this->_groupBy[] = $colstr;
        }

        return $this;
    }

    /**
     * Having SQL command
     *
     * @access  public
     * @param   mixed   $column Column
     * @param   mixed   $value  Column value
     * @param   string  $opt    Operator condition
     * @return  object  Jaws_ORM object
     */
    function having($column, $value, $opt = '=')
    {
        switch ($opt) {
            case 'in':
            case 'not in':
                $value = '('. implode(', ', array_map(array($this, 'quoteValue'), $value)). ')';
                break;

            case 'between':
            case 'not between':
                $value = $this->quoteValue($value[0]). ' and '. $this->quoteValue($value[1]);
                break;

            case 'like':
            case 'not like':
                $value  = $this->quoteValue($value);
                break;

            case 'is null':
            case 'is not null':
                $value  = '';
                break;

            default:
                $value  = $this->quoteValue($value);
        }

        // quote column identifier
        if (is_object($column)) {
            $colstr = '('. $column->get(). ')';
            unset($column);
        } elseif (is_array($column)) {
            $colstr = $this->quoteValue($column);
        } else {
            $colstr = $this->quoteIdentifier($column);
        }

        $this->_having[] = "($colstr $opt $value)";
        return $this;
    }

    /**
     * Order by SQL command
     *
     * @access  public
     * @param   mixed   $args   Order by expression
     * @return  object  Jaws_ORM object
     */
    function orderBy($args)
    {
        foreach(func_get_args() as $arg) {
            // quote arg identifier
            if (is_object($arg)) {
                $this->_orderBy[] = $arg->get(). $arg->sort;
                unset($arg);
            } else {
                $this->_orderBy[] = $this->quoteIdentifier($arg);
            }
        }

        return $this;
    }

    /**
     * Limit/Offset SQL command
     *
     * @access  public
     * @param   int     $limit  Number of rows to select
     * @param   int     $offset First row to select
     * @return  object  Jaws_ORM object
     */
    function limit($limit, $offset = null)
    {
        $this->_limit  = $limit;
        $this->_offset = $offset;
        return $this;
    }

    /**
     * Return type of nested select
     *
     * @access  public
     * @param   string  $type   Return type(text, boolean, integer, decimal, float, blob, clob, timestamp)
     * @return  void
     */
    function type($type = '')
    {
        $this->type = $type;
    }

    /**
     * Alias name of nested select
     *
     * @access  public
     * @param   string  $alias   Alias name
     * @return  void
     */
    function alias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * Builds join string
     *
     * @access  private
     * @return  string  Join string
     */
    private function _build_join()
    {
        $join_str = implode("\n", $this->_joins);
        return empty($join_str)? '' : "$join_str\n";
    }

    /**
     * Builds where string
     *
     * @access  private
     * @return  string  Where string
     */
    private function _build_where()
    {
        // removing extra and/or operators at begin/end of statement
        if (in_array(reset($this->_where), array(' and ', ' or '))) {
            array_shift($this->_where);
        }
        if (in_array(end($this->_where), array(' and ', ' or '))) {
            array_pop($this->_where);
        }

        $where_str = implode('', $this->_where);
        return empty($where_str)? '' : "where $where_str\n";
    }

    /**
     * Builds group by string
     *
     * @access  private
     * @return  string  Group by string
     */
    private function _build_groupBy()
    {
        $groupby_str = implode(',', $this->_groupBy);
        return empty($groupby_str)? '' : "group by $groupby_str\n";
    }

    /**
     * Builds having string
     *
     * @access  private
     * @return  string  Having string
     */
    private function _build_having()
    {
        // removing extra and/or operators at begin/end of statement
        if (in_array(reset($this->_having), array(' and ', ' or '))) {
            array_shift($this->_having);
        }
        if (in_array(end($this->_having), array(' and ', ' or '))) {
            array_pop($this->_having);
        }

        $having_str = implode('', $this->_having);
        return empty($having_str)? '' : "having $having_str\n";
    }

    /**
     * Builds order by string
     *
     * @access  private
     * @return  string  Order by string
     */
    private function _build_orderBy()
    {
        $orderby_str = implode(', ', $this->_orderBy);
        return empty($orderby_str)? '' : "order by $orderby_str\n";
    }

    /**
     * Fetch data from the result set
     *
     * @access  public
     * @param   string  $result_type    Result type (all/row/col/one)
     * @param   mixed   $argument        Extra parameters
     * @param   int     $error_level    Sets this error level if errors occurred
     * @return  mixed   Fetched data or Jaws_Error on failure
     */
    function fetch($style = 'row', $argument = null, $error_level = JAWS_ERROR_ERROR)
    {
        if (!$this->_passed_types) {
            $this->_types = array();
        }

        $sql = 'select '. $this->_distinct. implode(', ', $this->_columns) . "\n";
        $sql.= 'from '. $this->_tablesIdentifier. "\n";
        $sql.= $this->_build_join();
        $sql.= $this->_build_where();
        $sql.= $this->_build_groupBy();
        $sql.= $this->_build_having();
        $sql.= $this->_build_orderBy();

        switch ($style) {
            // Fetch the values from the first row of the result set
            case 'raw':
                $result = $sql;
                break;

            // Fetch the value from the first column of each row of the result set
            case 'column':
                $result = $this->jawsdb->dbc->queryCol($sql, $this->_types, (int)$argument);
                break;

            // Fetch the value from the first column of the first row of the result
            case 'one':
                $result = $this->jawsdb->dbc->queryone($sql, $this->_types);
                break;

            // Fetch all the rows of the result set into a two dimensional array
            case 'all':
                if (!empty($this->_limit)) {
                    $result = $this->jawsdb->dbc->setLimit($this->_limit, $this->_offset);
                    if (MDB2::isError($result)) {
                        break;
                    }
                }
                $result = $this->jawsdb->dbc->queryAll($sql, $this->_types);
                break;

            default:
                $result = $this->jawsdb->dbc->queryRow($sql, $this->_types);
        }

        if (MDB2::isError($result)) {
            // auto rollback
            if (self::$in_transaction && self::$auto_rollback_on_error) {
                $this->rollback();
            }

            if (self::$auto_log_on_error) {
                $GLOBALS['log']->Log($error_level, $result->getUserInfo(), 2);
            }

            $result = Jaws_Error::raiseError(
                $result->getMessage(),
                $result->getCode(),
                $error_level,
                -1
            );
        }

        $this->reset();
        return $result;
    }

    /**
     * Execute a query
     *
     * @access  public
     * @param   int     $error_level  Sets this error level if errors occurred
     * @return  mixed   Query result or Jaws_Error on failure
     */
    function exec($error_level = JAWS_ERROR_ERROR)
    {
        switch ($this->_query_command) {
            case 'delete':
                $sql = "delete\n";
                $sql.= 'from '. $this->_tablesIdentifier. "\n";
                $sql.= $this->_build_where();
                $result = $this->jawsdb->dbc->exec($sql);
                break;

            // insert a rows
            case 'insert':
            case 'upsert':
                $values  = '';
                $columns = '';
                $sql = 'insert into '. $this->_tablesIdentifier;
                foreach ($this->_values as $column => $value) {
                    $values .= ', '. $this->quoteValue($value);
                    $columns.= ', '. $this->quoteIdentifier($column);
                }
                $sql.= "\n(". trim($columns, ', '). ")\nvalues(". trim($values, ', '). ")\n";
                $result = $this->jawsdb->dbc->exec($sql);
                if (!MDB2::isError($result)) {
                    if (!empty($result) && !empty($this->_pk_field)) {
                        $result = $this->jawsdb->dbc->lastInsertID(
                            $this->_tbl_prefix. $this->_table,
                            $this->_pk_field
                        );
                    }
                    break;
                } elseif (($this->_query_command != 'upsert') || (MDB2_ERROR_CONSTRAINT != $result->getCode())) {
                    break;
                }
                // going to update case

            case 'update':
                $sql = 'update '. $this->_tablesIdentifier. " set\n";
                foreach ($this->_values as $column => $value) {
                    $value  = $this->quoteValue($value);
                    $column = $this->quoteIdentifier($column);
                    $sql.= "$column = $value,\n";
                }

                // remove extra comma from end of query
                $sql = substr_replace($sql, '', -2, 1);
                $sql.= $this->_build_where();
                $result = $this->jawsdb->dbc->exec($sql);
                if (!MDB2::isError($result) && ($this->_query_command == 'upsert')) {
                    // upsert: return record primary key same as insert
                    if (!empty($this->_pk_field)) {
                        $sql = 'select '. $this->quoteIdentifier($this->_pk_field). "\n";
                        $sql.= 'from '. $this->_tablesIdentifier. "\n";
                        $sql.= $this->_build_where();
                        $result = $this->jawsdb->dbc->queryone($sql);
                    }
                }
                break;

            // insert multiple rows
            case 'insertAll':
                $columns = '';
                $sql = 'insert into '. $this->_tablesIdentifier;
                // build insert columns list
                $sql.= "\n(". implode(', ', array_map(array($this, 'quoteIdentifier'), $this->_columns)). ")";
                // build insert values list
                $vsql = '';
                $dbDriver  = $this->jawsdb->getDriver();
                $dbVersion = $this->jawsdb->getDBVersion();
                foreach ($this->_values as $values) {
                    $values_str = implode(', ', array_map(array($this, 'quoteValue'), array_values($values)));
                    switch ($dbDriver) {
                        case 'oci8':
                            $vsql.= (empty($vsql)? '' : "\n UNION ALL"). "\n SELECT $values_str FROM DUAL";
                            break;

                        case 'ibase':
                            $vsql.= (empty($vsql)? '' : "\n UNION ALL"). "\n SELECT $values_str FROM RDB\$DATABASE";
                            break;

                        case 'pgsql':
                            if (version_compare($dbVersion, '8.2.0', '>=')) {
                                $vsql.= (empty($vsql)? "\n VALUES" : ","). "\n ($values_str)";
                            } else {
                                $vsql[] = " VALUES ($values_str)";
                            }
                            
                            break;

                        default:
                            $vsql.= (empty($vsql)? '' : "\n UNION ALL"). "\n SELECT $values_str";
                            break;
                    }
                }

                if (is_array($vsql)) {
                    $outer_transaction = self::$in_transaction;
                    $this->beginTransaction();
                    foreach ($vsql as $psql) {
                        $result = $this->jawsdb->dbc->exec($sql. $psql);
                        if (MDB2::isError($result)) {
                            break 2;
                        }
                    }
                    if (!$outer_transaction) {
                        $this->commit();
                    }
                } else {
                    $sql.= $vsql;
                    $result = $this->jawsdb->dbc->exec($sql);
                }
                break;

            default:
                // trigger an error
        }

        if (MDB2::isError($result)) {
            // auto rollback
            if (self::$in_transaction && self::$auto_rollback_on_error) {
                $this->rollback();
            }

            if (self::$auto_log_on_error) {
                $GLOBALS['log']->Log($error_level, $result->getUserInfo(), 1);
            }

            $result = Jaws_Error::raiseError(
                $result->getMessage(),
                $result->getCode(),
                $error_level,
                -1
            );
        }

        $this->reset();
        return $result;
    }

    /**
     * Start a transaction
     *
     * @access  public
     * @return  mixed   True on success, a Jaws_Error error on failure
     */
    function beginTransaction($auto_rollback = true)
    {
        if (!self::$in_transaction) {
            self::$in_transaction = true;
            self::$auto_rollback_on_error = $auto_rollback;
            $this->jawsdb->dbc->beginTransaction();
        }

        return $this;
    }

    /**
     * Cancel any database changes done during a transaction
     *
     * @access  public
     * @return  mixed   True on success, a Jaws_Error error on failure
     */
    function rollback()
    {
        if (self::$in_transaction) {
            self::$in_transaction = false;
            $this->jawsdb->dbc->rollback();
        }

        return $this;
    }

    /**
     * Commit the database changes done during a transaction
     *
     * @access  public
     * @return  mixed   True on success, a Jaws_Error error on failure
     */
    function commit()
    {
        if (self::$in_transaction) {
            self::$in_transaction = false;
            self::$auto_rollback_on_error = true;
            $this->jawsdb->dbc->commit();
        }

        return $this;
    }

    /**
     * Overloading magic method
     *
     * @access  private
     * @param   string  $method  Method name
     * @param   string  $params  Method parameters
     * @return  mixed   Jaws_ORM object otherwise Jaws_Error
     */
    function __call($method, $params)
    {
        switch ($method) {
            case 'or':
            case 'and':
                if (!empty($this->_where)) {
                    $this->_where[] = " $method ";
                }
                return $this;

            case 'insert':
            case 'update':
            case 'upsert':
                $this->_values = $params[0];
            case 'delete':
                $this->_query_command = $method;
                return $this;
                break;

            case 'insertAll':
                $this->_columns = $params[0];
                $this->_values  = $params[1];
                $this->_query_command = $method;
                return $this;

            case 'fetchAll':
            case 'fetchRow':
            case 'fetchColumn':
            case 'fetchOne':
            case 'fetchRaw':
                $argument = array_shift($params);
                $error_level = empty($params)? JAWS_ERROR_ERROR : $params[0];
                return $this->fetch(strtolower(substr($method, 5)), $argument, $error_level);

            case 'get':
                return $this->fetch('raw');

            case 'now':
            case 'expr':
            case 'lower':
            case 'upper':
            case 'length':
            case 'random':
            case 'concat':
            case 'replace':
            case 'substring':
                return new Jaws_ORM_Function($this, $method, $params);
                break;

            default:
                // trigger an error
        }
    }

    /**
     * Reset internal variables
     *
     * @access  public
     * @return  void
     */
    function reset()
    {
        $this->_distinct = '';
        $this->_columns  = array();
        $this->_types    = array();
        $this->_values   = array();
        $this->_where    = array();
        $this->_joins    = array();
        $this->_groupBy  = array();
        $this->_having   = array();
        $this->_orderBy  = array();
        $this->_limit    = null;
        $this->_offset   = null;
        $this->_passed_types  = false;
        $this->_query_command = '';
    }

}

/**
 * Object-relational mapping function class
 *
 * @category   ORM
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_ORM_Function
{
    /**
     * Jaws_ORM object
     *
     * @var     object
     * @access  private
     */
    var $orm = '';

    /**
     * Method name
     *
     * @var     string
     * @access  public
     */
    var $method = '';

    /**
     * Method params
     *
     * @var     array
     * @access  public
     */
    var $params = array();

    /**
     * return type
     *
     * @var     string
     * @access  public
     */
    var $type = '';

    /**
     * Alias name of method statement in select command
     *
     * @var     string
     * @access  public
     */
    var $alias = '';

    /**
     * Sort type of Order by command
     *
     * @var     string
     * @access  public
     */
    var $sort = '';

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    function __construct(&$orm, $method, $params)
    {
        $this->orm    = $orm;
        $this->method = $method;
        $this->params = $params;
        $this->orm->jawsdb->dbc->loadModule('Function', null, true);
    }

    /**
     * Method return type
     *
     * @access  public
     * @param   string  $type   Return type(text, boolean, integer, decimal, float, blob, clob, timestamp)
     * @return  void
     */
    function type($type = '')
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Alias name of method statement in select command
     *
     * @access  public
     * @param   string  $alias   Alias name
     * @return  void
     */
    function alias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Sort type of Order by command
     *
     * @access  public
     * @param   string  $sort   Sort type
     * @return  void
     */
    function sort($sort)
    {
        $this->sort = ' '. $sort;
        return $this;
    }

    /**
     * Builds function string
     *
     * @access  public
     * @return  string  Function string
     */
    public function get()
    {
        $params = $this->params;
        $method = $this->method;

        $func_str = '';
        switch ($method) {
            case 'lower':
            case 'upper':
            case 'length':
                $params[0] = $this->orm->quoteIdentifier($params[0]);
                $func_str = $this->orm->jawsdb->dbc->function->$method($params[0]);
                break;

            case 'now':
            case 'random':
                $func_str = $this->orm->jawsdb->dbc->function->$method();
                break;

            case 'concat':
            case 'replace':
                foreach ($params as &$param) {
                    if (is_array($param)) {
                        $param = $this->orm->quoteValue($param);
                    } else {
                        $param = $this->orm->quoteIdentifier($param);
                    }
                }

                $func_str = call_user_func_array(array($this->orm->jawsdb->dbc->function, $method), $params);
                break;

            case 'substring':
                $params[0] = $this->orm->quoteIdentifier($params[0]);
                $func_str = call_user_func_array(array($this->orm->jawsdb->dbc->function, 'substring'), $params);
                break;

            case 'expr':
                $func_str = array_shift($params);
                $func_str = $this->orm->quoteIdentifier($func_str);
                foreach ($params as $param) {
                    $func_str = preg_replace('/\?/', $this->orm->quoteValue($param), $func_str, 1);
                }

                break;
        }

        return $func_str;
    }

}