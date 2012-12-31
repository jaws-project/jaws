<?php
/**
 * Object-relational mapping class
 *
 * @category   ORM
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_ORM
{
    /**
     * Jaws_DB  object
     *
     * @var     pbject
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
     * Table quoted name
     *
     * @var     string
     * @access  private
     */
    var $_table_quoted = '';

    /**
     * Table alias name
     *
     * @var     string
     * @access  private
     */
    var $_table_alias = '';

    /**
     * Table primary key name
     *
     * @var     string
     * @access  private
     */
    var $_pk_field = 'id';

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
     * Constructor
     *
     * @access  public
     * @return  void
     */
    function __construct()
    {
        $this->jawsdb = &$GLOBALS['db'];
        $this->_tbl_prefix = $this->jawsdb->GetPrefix();
        $this->_identifier_quoting = $this->jawsdb->dbc->identifier_quoting;
    }

    /**
     * Creates the Jaws_ORM instance
     *
     * @return  object returns the instance
     * @access  public
     */
    function getInstance()
    {
        return new Jaws_ORM();
    }

    /**
     * Sets table name/alias
     *
     * @access  public
     * @param   string  $table  Table name
     * @param   string  $alias  Table alias in query
     * @return  object  Jaws_ORM object
     */
    function table($table, $alias = '')
    {
        $this->_table = $table;
        $this->_table_alias = empty($alias)? '': (' as '. $this->quoteIdentifier($alias));
        $this->_table_quoted = $this->quoteIdentifier($this->_tbl_prefix. $table);
        return $this;
    }

    /**
     * Quote a string so it can be safely used as a table or column name
     *
     * @access  private
     * @param   string  $column  Column name
     * @return  string  quoted string
     */
    function quoteIdentifier($column)
    {
        if (strpos($column, '.') !== false) {
            $tbl_prefix = '';
            $column = str_replace(
                '.',
                $this->_identifier_quoting['end']. '.'. $this->_identifier_quoting['start'],
                $column
            );
        } else {
            $tbl_prefix = $this->_tbl_prefix;
        }

        if (false === strpos($column, '(')) {
            $column = $this->_identifier_quoting['start']
                    . $tbl_prefix. $column
                    . $this->_identifier_quoting['end'];
        } else {
            $column = str_replace(
                '(',
                '('. $this->_identifier_quoting['start']. $tbl_prefix,
                $column
            );
            $column = str_replace(
                ')',
                $this->_identifier_quoting['end']. ')',
                $column
            );
        }

        return $column;
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
        if (is_array($value)) {
            // $value is array(value, type)
            $value = $this->jawsdb->dbc->quote($value[0], isset($value[1])? $value[1] : null);
        } else {
            // Add "N" character before text field value,
            // when using FreeTDS as MSSQL driver, to supporting unicode text
            if ($this->jawsdb->_dsn['phptype'] == 'mssql' &&
                is_string($value) &&
                $this->jawsdb->Is_FreeTDS_MSSQL_Driver())
            {
                $value = 'N' . $this->dbc->quote($value);
            } else {
                $value = $this->jawsdb->dbc->quote($value);
            }
        }

        return $value;
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
        $this->_columns = func_get_args();
        foreach($this->_columns as $key => $column) {
            if (is_object($column)) {
                $colstr = '('. $column->get(). ')';
                $type   = $column->type;
                $alias  = $column->alias;
                unset($column);
            } else {
                @list($column, $alias, $type) = explode(':', $column);
                $colstr = $this->quoteIdentifier($column);
            }

            $this->_columns[$key] = $colstr. (empty($alias)? '' : (' as '. $this->quoteIdentifier($alias)));
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
     * @param   string  $join   Join type
     * @param   string  $table  Join target table
     * @param   string  $target Join target field
     * @param   string  $source Join source field
     * @return  object  Jaws_ORM object
     */
    function join($join, $table, $target, $source)
    {
        $table = explode(' ', $table);
        $table[0] = $this->quoteIdentifier($this->_tbl_prefix. $table[0]);
        $table = implode(' ', $table);

        $source = $this->quoteIdentifier($source);
        $target = $this->quoteIdentifier($target);

        $this->_joins[] = "$join join $table on $source = $target";
        return $this;
    }

    /**
     * Where SQL command
     *
     * @access  public
     * @param   string  $column Column
     * @param   string  $opt    Operator condition
     * @param   string  $value  Column value
     * @return  object  Jaws_ORM object
     */
    function where($column, $opt, $value)
    {
        switch ($opt) {
            case 'in':
                $value = '('. implode(', ', array_map(array($this, 'quoteValue'), $value)). ')';
                break;

            case 'between':
            case 'not between':
                $value = $this->quoteValue($value[0]). ' AND '. $this->quoteValue($value[1]);
                break;

            case 'like':
            case 'not like':
                $value  = $this->quoteValue($value);
                break;

            default:
                $value  = $this->quoteValue($value);
        }

        // quote column identifier
        if (is_object($column)) {
            $colstr = $column->get();
            unset($column);
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
     * @param   string  $opt    Operator condition
     * @param   string  $value  Column value
     * @return  object  Jaws_ORM object
     */
    function openWhere($column = '', $opt = '', $value = '')
    {
        $this->_where[] = '(';
        if (!empty($column)) {
            $this->where($column, $opt, $value);
        }

        return $this;
    }

    /**
     * Where SQL command suffix with open parenthesis
     *
     * @access  public
     * @param   string  $column Column
     * @param   string  $opt    Operator condition
     * @param   string  $value  Column value
     * @return  object  Jaws_ORM object
     */
    function closeWhere($column = '', $opt = '', $value = '')
    {
        if (!empty($column)) {
            $this->where($column, $opt, $value);
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
                $colstr = $column->get();
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
     * @param   string  $column Column
     * @param   string  $opt    Operator condition
     * @param   string  $value  Column value
     * @return  object  Jaws_ORM object
     * @todo    support more than one having condition
     */
    function having($column, $opt, $value)
    {
        switch ($opt) {
            case 'in':
                $value = '('. implode(', ', array_map(array($this, 'quoteValue'), $value)). ')';
                break;

            case 'between':
                $value = $this->quoteValue($value[0]). ' AND '. $this->quoteValue($value[1]);
                break;

            default:
                $value  = $this->quoteValue($value);
        }

        // quote column identifier
        if (is_object($column)) {
            $colstr = $column->get();
            unset($column);
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
     * @param   string  $column Column
     * @param   string  $sort   Sort type
     * @return  object  Jaws_ORM object
     */
    function orderBy($column, $sort = '')
    {
        // quote column identifier
        if (is_object($column)) {
            $this->_orderBy[] = $column->get(). ' '. $sort;
            unset($column);
        } else {
            $this->_orderBy[] = $this->quoteIdentifier($column). ' '. $sort;
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
     * @param   string  $result_type  Result type (all/row/col/one)
     * @return  mixed   Fetched data or Jaws_Error on failure
     */
    function get($select_type = 'raw')
    {
        if (!$this->_passed_types) {
            $this->_types = array();
        }

        $sql = 'select '. $this->_distinct. implode(', ', $this->_columns) . "\n";
        $sql.= 'from '. $this->_table_quoted. "\n";
        $sql.= $this->_build_join();
        $sql.= $this->_build_where();
        $sql.= $this->_build_groupBy();
        $sql.= $this->_build_having();
        $sql.= $this->_build_orderBy();

        switch ($select_type) {
            // Fetch the values from the first row of the result set
            case 'row':
                $result = $this->jawsdb->dbc->queryRow($sql, $this->_types);
                break;

            // Fetch the value from the first column of each row of the result set
            case 'col':
                $result = $this->jawsdb->dbc->queryCol($sql, $this->_types);
                break;

            // Fetch the value from the first column of the first row of the result
            case 'one':
                $result = $this->jawsdb->dbc->queryone($sql, $this->_types);
                break;

            // Fetch all the rows of the result set into a two dimensional array
            case 'all':
                if (!empty($this->_limit)) {
                    $result = $this->jawsdb->setLimit($this->_limit, $this->_offset);
                    if (Jaws_Error::IsError($result)) {
                        break;
                    }
                }
                $result = $this->jawsdb->dbc->queryAll($sql, $this->_types);
                break;

            default:
                $result = $sql;
        }

        $this->reset();
        if (PEAR::IsError($result)) {
            $GLOBALS['log']->Log(JAWS_ERROR_ERROR, $result->getUserInfo(), 1);
            return Jaws_Error::raiseError(
                $result->getMessage(),
                $result->getCode(),
                JAWS_ERROR_ERROR,
                -1
            );
        }

        return $result;
    }

    /**
     * Execute a query
     *
     * @access  public
     * @param   int     $error_level  Sets this error level if errors occurred
     * @return  mixed   Query result or Jaws_Error on failure
     */
    function execute($error_level = JAWS_ERROR_ERROR)
    {
        switch ($this->_query_command) {
            case 'delete':
                $sql = "delete\n";
                $sql.= 'from '. $this->_table_quoted. "\n";
                $sql.= $this->_build_where();
                $result = $this->jawsdb->dbc->exec($sql);
                break;

            case 'update':
                $sql = 'update '. $this->_table_quoted. " set\n";
                foreach ($this->_values as $column => $value) {
                    $value  = ', '. $this->quoteValue($value);
                    $column = $this->_identifier_quoting['start']
                            . $column
                            . $this->_identifier_quoting['end'];
                    $sql.= "$column = $value\n";
                }
                $sql.= $this->_build_where();
                $result = $this->jawsdb->dbc->exec($sql);
                break;

            // insert a rows
            case 'insert':
                $values  = '';
                $columns = '';
                $sql = 'insert into '. $this->_table_quoted;
                foreach ($this->_values as $column => $value) {
                    $values .= ', '. $this->quoteValue($value);
                    $columns.= ', '
                            . $this->_identifier_quoting['start']
                            . $column
                            . $this->_identifier_quoting['end'];
                }
                $sql.= "\n(". trim($columns, ', '). ")\nvalues(". trim($values, ', '). ")\n";
                $result = $this->jawsdb->dbc->exec($sql);
                if (!PEAR::IsError($result) && !empty($result)) {
                    $result = $this->jawsdb->lastInsertID($this->_table, $this->_pk_field);
                }
                break;

            // insert multiple rows
            case 'insertArray':
                $columns = '';
                $sql = 'insert into '. $this->_table_quoted;
                // build insert columns list
                foreach ($this->_columns as $column) {
                    $columns.= ', '
                            . $this->_identifier_quoting['start']
                            . $column
                            . $this->_identifier_quoting['end'];
                }
                $sql.= "\n(". trim($columns, ', '). ")\n";

                // build insert values list
                $vsql = '';
                $dbDriver  = $this->jawsdb->getDriver();
                foreach ($this->_values as $values) {
                    $values_str = implode(', ', array_map(array($this, 'quoteValue'), $values));
                    switch ($dbDriver) {
                        case 'oci8':
                            $vsql.= (empty($vsql)? '' : "\n UNION ALL"). "\n SELECT $values_str FROM DUAL";
                            break;

                        case 'ibase':
                            $vsql.= (empty($vsql)? '' : "\n UNION ALL"). "\n SELECT $values_str FROM RDB\$DATABASE";
                            break;

                        case 'pgsql':
                            $vsql.= (empty($vsql)? "\n VALUES" : ","). "\n ($values_str)";
                            break;

                        default:
                            $vsql.= (empty($vsql)? '' : "\n UNION ALL"). "\n SELECT $values_str";
                            break;
                    }
                }

                $sql.= $vsql;
                $result = $this->jawsdb->dbc->exec($sql);
                break;

            default:
                // trigger an error
        }

        $this->reset();
        if (PEAR::IsError($result)) {
            $GLOBALS['log']->Log($error_level, $result->getUserInfo(), 1);
            return Jaws_Error::raiseError(
                $result->getMessage(),
                $result->getCode(),
                $error_level,
                -1
            );
        }

        return $result;
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
                $this->_where[] = " $method ";
                return $this;

            case 'insert':
            case 'update':
                $this->_values = $params[0];
            case 'delete':
                $this->_query_command = $method;
                return $this;
                break;

            case 'insertArray':
                $this->_columns = array_shift($params);
                $this->_values  = $params;
                $this->_query_command = $method;
                return $this;

            case 'getAll':
            case 'getRow':
            case 'getCol':
            case 'getOne':
                return $this->get(strtolower(substr($method, 3)));

            case 'now':
            case 'lower':
            case 'upper':
            case 'length':
            case 'random':
            case 'concat':
            case 'replace':
            case 'substring':
                return new Jaws_ORM_Function($method, $params);
                break;

            default:
                // trigger an error
        }
    }

    /**
     * Reset internal variables
     *
     * @access  public
     * @return  voide
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
 * @copyright  2012-2013 Jaws Development Group
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
    }

    /**
     * Builds function string
     *
     * @access  public
     * @return  string  Function string
     */
    public function get(&$objFunc)
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
        }

        return $func_str;
    }

}