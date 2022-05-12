<?php
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2006 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith, Lorenzo Alberton                       |
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
// | Author: Lorenzo Alberton <l.alberton@quipo.it>                       |
// +----------------------------------------------------------------------+
//
// $Id: ibase.php 327310 2012-08-27 15:16:18Z danielc $

require_once 'MDB2/Driver/Function/Common.php';

/**
 * MDB2 FireBird/InterBase driver for the function modules
 *
 * @package MDB2
 * @category Database
 * @author Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Driver_Function_ibase extends MDB2_Driver_Function_Common
{
    // {{{ functionTable()

    /**
     * return string for internal table used when calling only a function
     *
     * @return string for internal table used when calling only a function
     * @access public
     */
    function functionTable()
    {
        return ' FROM RDB$DATABASE';
    }

    // }}}
    // {{{ length()

    /**
     * return string to call a function to get a replacement inside an SQL statement.
     *
     * @return string to call a function to get a replace
     * @access public
     */
    function length($expression)
    {
        return "STRLEN($expression)";
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