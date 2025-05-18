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
// $Id: sqlite.php 327310 2012-08-27 15:16:18Z danielc $
//

require_once 'MDB2/Driver/Function/Common.php';

/**
 * MDB2 SQLite driver for the function modules
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Driver_Function_sqlite extends MDB2_Driver_Function_Common
{
    // {{{ constructor

    /**
     * Constructor
     */
    function __construct($db_index)
    {
        parent::__construct($db_index);
        // create all sorts of UDFs
    }

    // {{{ now()

    /**
     * Return string to call a variable with the current timestamp inside an SQL statement
     * There are three special variables for current date and time.
     *
     * @return string to call a variable with the current timestamp
     * @access public
     */
    function now($type = 'timestamp')
    {
        switch ($type) {
        case 'time':
            return 'time(\'now\')';
        case 'date':
            return 'date(\'now\')';
        case 'timestamp':
        default:
            return 'datetime(\'now\')';
        }
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
        return 'strftime("%s",'. $expression.', "utc")';
    }

    // }}}
    // {{{ substring()

    /**
     * return string to call a function to get a substring inside an SQL statement
     *
     * @return string to call a function to get a substring
     * @access public
     */
    function substring($value, $position = 1, $length = null)
    {
        if (null !== $length) {
            return "substr($value, $position, $length)";
        }
        return "substr($value, $position, length($value))";
    }

    // }}}
    // {{{ GROUP_CONCAT()

    /**
     * Returns concatenate rows of strings into one string with a specified separator
     *
     * @param string $expression
     * @param string $orderBy
     * @param string $delimiter...
     * @return string string with concatenated rows of strings with a specified separator
     * @access public
     **/
    function listagg($expression, $orderBy = null, $delimiter = ',')
    {
        $sql = $expression;
        $delimiter = $delimiter? ", '$delimiter'" : '';

        return "GROUP_CONCAT($expression $delimiter)";
    }

    // }}}
    // {{{ random()

    /**
     * return string to call a function to get random value inside an SQL statement
     *
     * @return return string to generate float between 0 and 1
     * @access public
     */
    function random()
    {
        return '((RANDOM()+2147483648)/4294967296)';
    }

    // }}}
    // {{{ replace()

    /**
     * return string to call a function to get a replacement inside an SQL statement.
     *
     * @return string to call a function to get a replace
     * @access public
     */
    function replace($str, $from_str, $to_str)
    {
        $db = $this->getDBInstance();
        if (MDB2::isError($db)) {
            return $db;
        }

        $error = $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
            'method not implemented', __FUNCTION__);
        return $error;
    }

    // }}}

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
        return "trunc($value1)";
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
        return "trunc($value1 / $value2)";
    }

}
?>
