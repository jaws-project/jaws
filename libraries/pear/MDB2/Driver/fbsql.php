<?php
// vim: set et ts=4 sw=4 fdm=marker:
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2006 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith, Frank M. Kromann                       |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2 is a merge of PEAR DB and Metabases that provides a unified DB  |
// | API as well as database abstraction for PHP applications.            |
// | This LICENSE is in the BSD license style.                            |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | Redistributions of source code must retain the above copyright       |
// | notice, this list of conditions and the following disclaimer.        |
// |                                                                      |
// | Redistributions in binary form must reproduce the above copyright    |
// | notice, this list of conditions and the following disclaimer in the  |
// | documentation and/or other materials provided with the distribution. |
// |                                                                      |
// | Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,    |
// | Lukas Smith nor the names of his contributors may be used to endorse |
// | or promote products derived from this software without specific prior|
// | written permission.                                                  |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS|
// |  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED  |
// | AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT          |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY|
// | WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE          |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Author: Lukas Smith <smith@pooteeweet.org>                           |
// +----------------------------------------------------------------------+
//
// $Id: fbsql.php 328145 2012-10-25 20:20:57Z danielc $
//

/**
 * MDB2 FrontBase driver
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 * @author  Frank M. Kromann <frank@kromann.info>
 */
class MDB2_Driver_fbsql extends MDB2_Driver_Common
{
    // {{{ properties
    var $string_quoting = array('start' => "'", 'end' => "'", 'escape' => "'", 'escape_pattern' => false);

    var $identifier_quoting = array('start' => '"', 'end' => '"', 'escape' => '"');
    // }}}
    // {{{ constructor

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();

        $this->phptype = 'fbsql';
        $this->dbsyntax = 'fbsql';

        $this->supported['sequences'] = 'emulated';
        $this->supported['indexes'] = true;
        $this->supported['affected_rows'] = true;
        $this->supported['transactions'] = true;
        $this->supported['savepoints'] = false;
        $this->supported['summary_functions'] = true;
        $this->supported['order_by_text'] = true;
        $this->supported['current_id'] = 'emulated';
        $this->supported['limit_queries'] = 'emulated';
        $this->supported['LOBs'] = true;
        $this->supported['replace'] ='emulated';
        $this->supported['sub_selects'] = true;
        $this->supported['auto_increment'] = false; // not implemented
        $this->supported['primary_key'] = true;
        $this->supported['result_introspection'] = true;
        $this->supported['prepared_statements'] = 'emulated';
        $this->supported['identifier_quoting'] = true;
        $this->supported['pattern_escaping'] = false;
        $this->supported['new_link'] = false;

        $this->options['DBA_username'] = false;
        $this->options['DBA_password'] = false;
    }

    // }}}
    // {{{ errorInfo()

    /**
     * This method is used to collect information about an error
     *
     * @param integer $error
     * @return array
     * @access public
     */
    function errorInfo($error = null)
    {
       if ($this->connection) {
           $native_code = @fbsql_errno($this->connection);
           $native_msg  = @fbsql_error($this->connection);
       } else {
           $native_code = @fbsql_errno();
           $native_msg  = @fbsql_error();
       }
        if (null === $error) {
            static $ecode_map;
            if (empty($ecode_map)) {
                $ecode_map = array(
                     22 => MDB2_ERROR_SYNTAX,
                     85 => MDB2_ERROR_ALREADY_EXISTS,
                    108 => MDB2_ERROR_SYNTAX,
                    116 => MDB2_ERROR_NOSUCHTABLE,
                    124 => MDB2_ERROR_VALUE_COUNT_ON_ROW,
                    215 => MDB2_ERROR_NOSUCHFIELD,
                    217 => MDB2_ERROR_INVALID_NUMBER,
                    226 => MDB2_ERROR_NOSUCHFIELD,
                    231 => MDB2_ERROR_INVALID,
                    239 => MDB2_ERROR_TRUNCATED,
                    251 => MDB2_ERROR_SYNTAX,
                    266 => MDB2_ERROR_NOT_FOUND,
                    357 => MDB2_ERROR_CONSTRAINT_NOT_NULL,
                    358 => MDB2_ERROR_CONSTRAINT,
                    360 => MDB2_ERROR_CONSTRAINT,
                    361 => MDB2_ERROR_CONSTRAINT,
                );
            }
            if (isset($ecode_map[$native_code])) {
                $error = $ecode_map[$native_code];
            }
        }
        return array($error, $native_code, $native_msg);
    }

    // }}}
    // {{{ beginTransaction()

    /**
     * Start a transaction or set a savepoint.
     *
     * @param   string  name of a savepoint to set
     * @return  mixed   MDB2_OK on success, a MDB2 error on failure
     *
     * @access  public
     */
    function beginTransaction($savepoint = null)
    {
        $this->debug('Starting transaction/savepoint', __FUNCTION__, array('is_manip' => true, 'savepoint' => $savepoint));
        if (null !== $savepoint) {
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'savepoints are not supported', __FUNCTION__);
        }
        if ($this->in_transaction) {
            return MDB2_OK;  //nothing to do
        }
        if (!$this->destructor_registered && $this->opened_persistent) {
            $this->destructor_registered = true;
            register_shutdown_function('MDB2_closeOpenTransactions');
        }
        $result =& $this->_doQuery('SET COMMIT FALSE;', true);
        if (MDB2::isError($result)) {
            return $result;
        }
        $this->in_transaction = true;
        return MDB2_OK;
    }

    // }}}
    // {{{ commit()

    /**
     * Commit the database changes done during a transaction that is in
     * progress or release a savepoint. This function may only be called when
     * auto-committing is disabled, otherwise it will fail. Therefore, a new
     * transaction is implicitly started after committing the pending changes.
     *
     * @param   string  name of a savepoint to release
     * @return  mixed   MDB2_OK on success, a MDB2 error on failure
     *
     * @access  public
     */
    function commit($savepoint = null)
    {
        $this->debug('Committing transaction/savepoint', __FUNCTION__, array('is_manip' => true, 'savepoint' => $savepoint));
        if (!$this->in_transaction) {
            return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                'commit/release savepoint cannot be done changes are auto committed', __FUNCTION__);
        }
        if (null !== $savepoint) {
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'savepoints are not supported', __FUNCTION__);
        }

        $result =& $this->_doQuery('COMMIT;', true);
        if (MDB2::isError($result)) {
            return $result;
        }
        $result =& $this->_doQuery('SET COMMIT TRUE;', true);
        if (MDB2::isError($result)) {
            return $result;
        }
        $this->in_transaction = false;
        return MDB2_OK;
    }

    // }}}
    // {{{

    /**
     * Cancel any database changes done during a transaction or since a specific
     * savepoint that is in progress. This function may only be called when
     * auto-committing is disabled, otherwise it will fail. Therefore, a new
     * transaction is implicitly started after canceling the pending changes.
     *
     * @param   string  name of a savepoint to rollback to
     * @return  mixed   MDB2_OK on success, a MDB2 error on failure
     *
     * @access  public
     */
    function rollback($savepoint = null)
    {
        $this->debug('Rolling back transaction/savepoint', __FUNCTION__, array('is_manip' => true, 'savepoint' => $savepoint));
        if (!$this->in_transaction) {
            return $this->raiseError(MDB2_ERROR_INVALID, null, null,
                'rollback cannot be done changes are auto committed', __FUNCTION__);
        }
        if (null !== $savepoint) {
            return $this->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'savepoints are not supported', __FUNCTION__);
        }

        $result =& $this->_doQuery('ROLLBACK;', true);
        if (MDB2::isError($result)) {
            return $result;
        }
        $result =& $this->_doQuery('SET COMMIT TRUE;', true);
        if (MDB2::isError($result)) {
            return $result;
        }
        $this->in_transaction = false;
        return MDB2_OK;
    }

    // }}}
    // {{{ _doConnect()

    /**
     * do the grunt work of the connect
     *
     * @return connection on success or MDB2 Error Object on failure
     * @access protected
     */
    function _doConnect($username, $password, $persistent = false)
    {
        if (!extension_loaded($this->phptype)) {
            return $this->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'extension '.$this->phptype.' is not compiled into PHP', __FUNCTION__);
        }

        $params = array(
            $this->dsn['hostspec'] ? $this->dsn['hostspec'] : 'localhost',
            $username ? $username : null,
            $password ? $password : null,
        );

        $connect_function = $persistent ? 'fbsql_pconnect' : 'fbsql_connect';

        $connection = @call_user_func_array($connect_function, $params);
        if ($connection <= 0) {
            return $this->raiseError(MDB2_ERROR_CONNECT_FAILED, null, null,
                'unable to establish a connection', __FUNCTION__);
        }

        if (!empty($this->dsn['charset'])) {
            $result = $this->setCharset($this->dsn['charset'], $connection);
            if (MDB2::isError($result)) {
                return $result;
            }
        }

        return $connection;
    }

    // }}}
    // {{{ connect()

    /**
     * Connect to the database
     *
     * @return true on success, MDB2 Error Object on failure
     **/
    function connect()
    {
        if (is_resource($this->connection)) {
            //if (count(array_diff($this->connected_dsn, $this->dsn)) == 0
            if (MDB2::areEquals($this->connected_dsn, $this->dsn)
                && $this->opened_persistent == $this->options['persistent']
            ) {
                return MDB2_OK;
            }
            $this->disconnect(false);
        }

        $connection = $this->_doConnect($this->dsn['username'],
                                        $this->dsn['password'],
                                        $this->options['persistent']);
        if (MDB2::isError($connection)) {
            return $connection;
        }

        $this->connection = $connection;
        $this->connected_dsn = $this->dsn;
        $this->connected_database_name = '';
        $this->opened_persistent = $this->options['persistent'];
        $this->dbsyntax = $this->dsn['dbsyntax'] ? $this->dsn['dbsyntax'] : $this->phptype;

        if ($this->database_name) {
            if ($this->database_name != $this->connected_database_name) {
                if (!@fbsql_select_db($this->database_name, $connection)) {
                    $err = $this->raiseError(null, null, null,
                        'Could not select the database: '.$this->database_name, __FUNCTION__);
                    return $err;
                }
                $this->connected_database_name = $this->database_name;
            }
        }

        return MDB2_OK;
    }

    // }}}
    // {{{ databaseExists()

    /**
     * check if given database name is exists?
     *
     * @param string $name    name of the database that should be checked
     *
     * @return mixed true/false on success, a MDB2 error on failure
     * @access public
     */
    function databaseExists($name)
    {
        $connection = $this->_doConnect($this->dsn['username'],
                                        $this->dsn['password'],
                                        $this->options['persistent']);
        if (MDB2::isError($connection)) {
            return $connection;
        }

        $result = @fbsql_select_db($name, $connection);
        @fbsql_close($connection);

        return $result;
    }

    // }}}
    // {{{ disconnect()

    /**
     * Log out and disconnect from the database.
     *
     * @param  boolean $force if the disconnect should be forced even if the
     *                        connection is opened persistently
     * @return mixed true on success, false if not connected and error
     *                object on error
     * @access public
     */
    function disconnect($force = true)
    {
        if (is_resource($this->connection)) {
            if ($this->in_transaction) {
                $dsn = $this->dsn;
                $database_name = $this->database_name;
                $persistent = $this->options['persistent'];
                $this->dsn = $this->connected_dsn;
                $this->database_name = $this->connected_database_name;
                $this->options['persistent'] = $this->opened_persistent;
                $this->rollback();
                $this->dsn = $dsn;
                $this->database_name = $database_name;
                $this->options['persistent'] = $persistent;
            }

            if (!$this->opened_persistent || $force) {
                @fbsql_close($this->connection);
            }
        }
        return parent::disconnect($force);
    }

    // }}}
    // {{{ standaloneQuery()

   /**
     * execute a query as DBA
     *
     * @param string $query the SQL query
     * @param mixed   $types  array that contains the types of the columns in
     *                        the result set
     * @param boolean $is_manip  if the query is a manipulation query
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function &standaloneQuery($query, $types = null, $is_manip = false)
    {
        $user = $this->options['DBA_username']? $this->options['DBA_username'] : $this->dsn['username'];
        $pass = $this->options['DBA_password']? $this->options['DBA_password'] : $this->dsn['password'];
        $connection = $this->_doConnect($user, $pass, $this->options['persistent']);
        if (MDB2::isError($connection)) {
            return $connection;
        }

        $offset = $this->offset;
        $limit = $this->limit;
        $this->offset = $this->limit = 0;
        $query = $this->_modifyQuery($query, $is_manip, $limit, $offset);

        $result =& $this->_doQuery($query, $is_manip, $connection, $this->database_name);
        if (!MDB2::isError($result)) {
            $result = $this->_affectedRows($connection, $result);
        }

        @fbsql_close($connection);
        return $result;
    }

    // }}}
    // {{{ _doQuery()

    /**
     * Execute a query
     * @param string $query  query
     * @param boolean $is_manip  if the query is a manipulation query
     * @param resource $connection
     * @param string $database_name
     * @return result or error object
     * @access protected
     */
    function &_doQuery($query, $is_manip = false, $connection = null, $database_name = null)
    {
        $this->last_query = $query;
        $result = $this->debug($query, 'query', array('is_manip' => $is_manip, 'when' => 'pre'));
        if ($result) {
            if (MDB2::isError($result)) {
                return $result;
            }
            $query = $result;
        }
        if ($this->options['disable_query']) {
            $result = $is_manip ? 0 : null;
            return $result;
        }

        if (null === $connection) {
            $connection = $this->getConnection();
            if (MDB2::isError($connection)) {
                return $connection;
            }
        }
        if (null === $database_name) {
            $database_name = $this->database_name;
        }

        if ($database_name) {
            if ($database_name != $this->connected_database_name) {
                if (!@fbsql_select_db($database_name, $connection)) {
                    $err = $this->raiseError(null, null, null,
                        'Could not select the database: '.$database_name, __FUNCTION__);
                    return $err;
                }
                $this->connected_database_name = $database_name;
            }
        }

        $result = @fbsql_query($query, $connection);
        if (!$result) {
            $err =& $this->raiseError(null, null, null,
                'Could not execute statement', __FUNCTION__);
            return $err;
        }

        $this->debug($query, 'query', array('is_manip' => $is_manip, 'when' => 'post', 'result' => $result));
        return $result;
    }

    // }}}
    // {{{ _affectedRows()

    /**
     * Returns the number of rows affected
     *
     * @param resource $result
     * @param resource $connection
     * @return mixed MDB2 Error Object or the number of rows affected
     * @access private
     */
    function _affectedRows($connection, $result = null)
    {
        if (null === $connection) {
            $connection = $this->getConnection();
            if (MDB2::isError($connection)) {
                return $connection;
            }
        }
        return @fbsql_affected_rows($connection);
    }

    // }}}
    // {{{ _modifyQuery()

    /**
     * Changes a query string for various DBMS specific reasons
     *
     * @param string $query  query to modify
     * @param boolean $is_manip  if it is a DML query
     * @param integer $limit  limit the number of rows
     * @param integer $offset  start reading from given offset
     * @return string modified query
     * @access protected
     */
    function _modifyQuery($query, $is_manip, $limit, $offset)
    {
        if ($limit > 0) {
            if ($is_manip) {
                return preg_replace('/^([\s(])*SELECT(?!\s*TOP\s*\()/i',
                    "\\1SELECT TOP($limit)", $query);
            } else {
                return preg_replace('/([\s(])*SELECT(?!\s*TOP\s*\()/i',
                    "\\1SELECT TOP($offset,$limit)", $query);
            }
        }
        // Add ; to the end of the query. This is required by FrontBase
        return $query.';';
    }

    // }}}
    // {{{ nextID()

    /**
     * Returns the next free id of a sequence
     *
     * @param string $seq_name name of the sequence
     * @param boolean $ondemand when true the sequence is
     *                          automatic created, if it
     *                          not exists
     *
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function nextID($seq_name, $ondemand = true)
    {
        $sequence_name = $this->quoteIdentifier($this->getSequenceName($seq_name), true);
        $seqcol_name = $this->quoteIdentifier($this->options['seqcol_name'], true);
        $query = "INSERT INTO $sequence_name ($seqcol_name) VALUES (NULL);";
        $this->pushErrorHandling(PEAR_ERROR_RETURN);
        $this->expectError(MDB2_ERROR_NOSUCHTABLE);
        $result =& $this->_doQuery($query, true);
        $this->popExpect();
        $this->popErrorHandling();
        if (MDB2::isError($result)) {
            if ($ondemand && $result->getCode() == MDB2_ERROR_NOSUCHTABLE) {
                $this->loadModule('Manager', null, true);
                $result = $this->manager->createSequence($seq_name);
                if (MDB2::isError($result)) {
                    return $this->raiseError($result, null, null,
                        'on demand sequence '.$seq_name.' could not be created', __FUNCTION__);
                } else {
                    return $this->nextID($seq_name, false);
                }
            }
            return $result;
        }
        $value = $this->lastInsertID();
        if (is_numeric($value)) {
            $query = "DELETE FROM $sequence_name WHERE $seqcol_name < $value";
            $result =& $this->_doQuery($query, true);
            if (MDB2::isError($result)) {
                $this->warnings[] = 'nextID: could not delete previous sequence table values from '.$seq_name;
            }
        }
        return $value;
    }

    // }}}
    // {{{ lastInsertID()

    /**
     * Returns the autoincrement ID if supported or $id or fetches the current
     * ID in a sequence called: $table.(empty($field) ? '' : '_'.$field)
     *
     * @param string $table name of the table into which a new row was inserted
     * @param string $field name of the field into which a new row was inserted
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function lastInsertID($table = null, $field = null)
    {
        $connection = $this->getConnection();
        if (MDB2::isError($connection)) {
            return $connection;
        }
        $value = @fbsql_insert_id($connection);
        if (!$value) {
            return $this->raiseError(null, null, null,
                'Could not get last insert ID', __FUNCTION__);
        }
        return $value;
    }

    // }}}
    // {{{ currID()

    /**
     * Returns the current id of a sequence
     *
     * @param string $seq_name name of the sequence
     * @return mixed MDB2 Error Object or id
     * @access public
     */
    function currID($seq_name)
    {
        $sequence_name = $this->quoteIdentifier($this->getSequenceName($seq_name), true);
        $seqcol_name = $this->quoteIdentifier($this->options['seqcol_name'], true);
        $query = "SELECT MAX($seqcol_name) FROM $sequence_name";
        return $this->queryOne($query, 'integer');
    }
}

/**
 * MDB2 FrontbaseSQL result driver
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Result_fbsql extends MDB2_Result_Common
{
    // }}}
    // {{{ fetchRow()

    /**
     * Fetch a row and insert the data into an existing array.
     *
     * @param int       $fetchmode  how the array data should be indexed
     * @param int    $rownum    number of the row where the data can be found
     * @return int data array on success, a MDB2 error on failure
     * @access public
     */
    function &fetchRow($fetchmode = MDB2_FETCHMODE_DEFAULT, $rownum = null)
    {
        if (null !== $rownum) {
            $seek = $this->seek($rownum);
            if (MDB2::isError($seek)) {
                return $seek;
            }
        }
        if ($fetchmode == MDB2_FETCHMODE_DEFAULT) {
            $fetchmode = $this->db->fetchmode;
        }
        if (   $fetchmode == MDB2_FETCHMODE_ASSOC
            || $fetchmode == MDB2_FETCHMODE_OBJECT
        ) {
            $row = @fbsql_fetch_assoc($this->result);
            if (is_array($row)
                && $this->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE
            ) {
                $row = array_change_key_case($row, $this->db->options['field_case']);
            }
        } else {
           $row = @fbsql_fetch_row($this->result);
        }
        if (!$row) {
            if (false === $this->result) {
                $err =& $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'resultset has already been freed', __FUNCTION__);
                return $err;
            }
            $null = null;
            return $null;
        }
        $mode = $this->db->options['portability'] & MDB2_PORTABILITY_EMPTY_TO_NULL;
        if ($mode) {
            $this->db->_fixResultArrayValues($row, $mode);
        }
        if ($fetchmode == MDB2_FETCHMODE_ORDERED) {
            if (!empty($this->types)) {
                $row = $this->db->datatype->convertResultRow($this->types, $row, $rtrim);
            }
        } elseif (!empty($this->types_assoc)) {
            $row = $this->db->datatype->convertResultRow($this->types_assoc, $row, $rtrim);
        }
        if (!empty($this->values)) {
            $this->_assignBindColumns($row);
        }
        if ($fetchmode === MDB2_FETCHMODE_OBJECT) {
            $object_class = $this->db->options['fetch_class'];
            if ($object_class == 'stdClass') {
                $row = (object) $row;
            } else {
                $row = new $object_class($row);
            }
        }
        ++$this->rownum;
        return $row;
    }

    // }}}
    // {{{ _getColumnNames()

    /**
     * Retrieve the names of columns returned by the DBMS in a query result.
     *
     * @return  mixed   Array variable that holds the names of columns as keys
     *                  or an MDB2 error on failure.
     *                  Some DBMS may not return any columns when the result set
     *                  does not contain any rows.
     * @access private
     */
    function _getColumnNames()
    {
        $columns = array();
        $numcols = $this->numCols();
        if (MDB2::isError($numcols)) {
            return $numcols;
        }
        for ($column = 0; $column < $numcols; $column++) {
            $column_name = @fbsql_field_name($this->result, $column);
            $columns[$column_name] = $column;
        }
        if ($this->db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $columns = array_change_key_case($columns, $this->db->options['field_case']);
        }
        return $columns;
    }

    // }}}
    // {{{ numCols()

    /**
     * Count the number of columns returned by the DBMS in a query result.
     *
     * @return mixed integer value with the number of columns, a MDB2 error
     *                       on failure
     * @access public
     */
    function numCols()
    {
        $cols = @fbsql_num_fields($this->result);
        if (null === $cols) {
            if (false === $this->result) {
                return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'resultset has already been freed', __FUNCTION__);
            }
            if (null === $this->result) {
                return count($this->types);
            }
            return $this->db->raiseError(null, null, null,
                'Could not get column count', __FUNCTION__);
        }
        return $cols;
    }

    // }}}
    // {{{ nextResult()

    /**
     * Move the internal result pointer to the next available result
     *
     * @return true on success, false if there is no more result set or an error object on failure
     * @access public
     */
    function nextResult()
    {
        if (false === $this->result) {
            return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                'resultset has already been freed', __FUNCTION__);
        }
        if (null === $this->result) {
            return false;
        }
        return @fbsql_next_result($this->result);
    }

    // }}}
    // {{{ free()

    /**
     * Free the internal resources associated with result.
     *
     * @return boolean true on success, false if result is invalid
     * @access public
     */
    function free()
    {
        if (is_resource($this->result) && $this->db->connection) {
            $free = @fbsql_free_result($this->result);
            if (false === $free) {
                return $this->db->raiseError(null, null, null,
                    'Could not free result', __FUNCTION__);
            }
        }
        $this->result = false;
        return MDB2_OK;
    }
}

/**
 * MDB2 FrontbaseSQL buffered result driver
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */

class MDB2_BufferedResult_fbsql extends MDB2_Result_fbsql
{
    // }}}
    // {{{ seek()

    /**
     * Seek to a specific row in a result set
     *
     * @param int    $rownum    number of the row where the data can be found
     * @return mixed MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function seek($rownum = 0)
    {
        if ($this->rownum != ($rownum - 1) && !@fbsql_data_seek($this->result, $rownum)) {
            if (false === $this->result) {
                return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'resultset has already been freed', __FUNCTION__);
            }
            if (null === $this->result) {
                return MDB2_OK;
            }
            return $this->db->raiseError(MDB2_ERROR_INVALID, null, null,
                'tried to seek to an invalid row number ('.$rownum.')', __FUNCTION__);
        }
        $this->rownum = $rownum - 1;
        return MDB2_OK;
    }

    // }}}
    // {{{ valid()

    /**
     * Check if the end of the result set has been reached
     *
     * @return mixed true or false on sucess, a MDB2 error on failure
     * @access public
     */
    function valid()
    {
        $numrows = $this->numRows();
        if (MDB2::isError($numrows)) {
            return $numrows;
        }
        return $this->rownum < ($numrows - 1);
    }

    // }}}
    // {{{ numRows()

    /**
     * Returns the number of rows in a result object
     *
     * @return mixed MDB2 Error Object or the number of rows
     * @access public
     */
    function numRows()
    {
        $rows = @fbsql_num_rows($this->result);
        if (null === $rows) {
            if (false === $this->result) {
                return $this->db->raiseError(MDB2_ERROR_NEED_MORE_DATA, null, null,
                    'resultset has already been freed', __FUNCTION__);
            }
            if (null === $this->result) {
                return 0;
            }
            return $this->db->raiseError(null, null, null,
                'Could not get row count', __FUNCTION__);
        }
        return $rows;
    }
}

/**
 * MDB2 FrontbaseSQL statement driver
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */

class MDB2_Statement_fbsql extends MDB2_Statement_Common
{

}

?>