<?php
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2008 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith                                         |
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
// $Id: mysqli.php 327310 2012-08-27 15:16:18Z danielc $
//

require_once 'MDB2/Driver/Function/Common.php';

/**
 * MDB2 MySQLi driver for the function modules
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Driver_Function_mysqli extends MDB2_Driver_Function_Common
{
     // }}}
    // {{{ executeStoredProc()

    /**
     * Execute a stored procedure and return any results
     *
     * @param string $name string that identifies the function to execute
     * @param mixed  $params  array that contains the paramaters to pass the stored proc
     * @param mixed   $types  array that contains the types of the columns in
     *                        the result set
     * @param mixed $result_class string which specifies which result class to use
     * @param mixed $result_wrap_class string which specifies which class to wrap results in
     * @return mixed a result handle or MDB2_OK on success, a MDB2 error on failure
     * @access public
     */
    function executeStoredProc($name, $params = null, $types = null, $result_class = true, $result_wrap_class = false)
    {
        $db = $this->getDBInstance();
        if (MDB2::isError($db)) {
            return $db;
        }

        $multi_query = $db->getOption('multi_query');
        if (!$multi_query) {
            $db->setOption('multi_query', true);
        }
        $query = 'CALL '.$name;
        $query .= $params ? '('.implode(', ', $params).')' : '()';
        $result = $db->query($query, $types, $result_class, $result_wrap_class);
        if (!$multi_query) {
            $db->setOption('multi_query', false);
        }
        return $result;
    }

    // }}}
    // {{{ unixtimestamp()

    /**
     * return string to call a function to get the unix timestamp from a iso timestamp
     *
     * @param string $expression
     *
     * @return string to call a variable with the timestamp
     * @access public
     */
    function unixtimestamp($expression)
    {
        return 'UNIX_TIMESTAMP('. $expression.')';
    }

    // }}}
    // {{{ concat()

    /**
     * Returns string to concatenate two or more string parameters
     *
     * @param string $value1
     * @param string $value2
     * @param string $values...
     * @return string to concatenate two strings
     * @access public
     **/
    function concat($value1, $value2)
    {
        $args = func_get_args();
        return "CONCAT(".implode(', ', $args).")";
    }

    // }}}
    // {{{ GROUP_CONCAT()

    /**
     * Returns a string with concatenated non-NULL value from a group
     *
     * @param string $expression
     * @param string $orderBy
     * @param string $delimiter...
     * @return string string from a group concatenated values with a specified separator
     * @access public
     **/
    function listagg($expression, $orderBy = null, $delimiter = ',')
    {
        $sql = $expression;
        $orderBy = $orderBy? "ORDER BY $orderBy" : '';
        $delimiter = $delimiter? "SEPARATOR '$delimiter'" : '';

        return "GROUP_CONCAT($expression $orderBy $delimiter)";
    }

    /**
     * return math truncate of expression
     *
     * @param mixed $value1
     * @param int   $value2
     *
     * @return return string of math truncate of an expression
     * @access public
     */
    function trunc($value1, $value2 = 0)
    {
        return "truncate($value1, $value2)";
    }

    /**
     * return math division of expression
     *
     * @param mixed $value1
     * @param mixed $value2
     *
     * @return return string of math division of an expression
     * @access public
     */
    function div($value1, $value2)
    {
        return "($value1 div $value2)";
    }

    // }}}
    // {{{ guid()

    /**
     * Returns global unique identifier
     *
     * @return string to get global unique identifier
     * @access public
     */
    function guid()
    {
        return 'UUID()';
    }

    // }}}
}
?>