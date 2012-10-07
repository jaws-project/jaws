<?php
// vim: set et ts=4 sw=4 fdm=marker:
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2006 Manuel Lemos, Tomas V.V.Cox,                 |
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
// $Id$
//

require_once 'MDB2/Driver/Datatype/Common.php';

/**
 * MDB2 FrontbaseSQL driver
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Driver_Datatype_fbsql extends MDB2_Driver_Datatype_Common
{
    // {{{ _baseConvertResult()

    /**
     * general type conversion method
     *
     * @param mixed $value refernce to a value to be converted
     * @param string $type specifies which type to convert to
     * @param string $rtrim if text should be rtrimmed
     * @return object a MDB2 error on failure
     * @access protected
     */
    function _baseConvertResult($value, $type, $rtrim = true)
    {
        if (null === $value) {
            return null;
        }
        switch ($type) {
         case 'boolean':
             return $value == 'T';
         case 'time':
            if ($value[0] == '+') {
                return substr($value, 1);
            } else {
                return $value;
            }
        }
        return parent::_baseConvertResult($value, $type, $rtrim);
    }

    // }}}
    // {{{ getTypeDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare an text type
     * field to be used in statements like CREATE TABLE.
     *
     * @param array $field  associative array with the name of the properties
     *      of the field being declared as array indexes. Currently, the types
     *      of supported field properties are as follows:
     *
     *      length
     *          Integer value that determines the maximum length of the text
     *          field. If this argument is missing the field should be
     *          declared to have the longest length allowed by the DBMS.
     *
     *      default
     *          Text value to be used as default for this field.
     *
     *      notnull
     *          Boolean flag that indicates whether this field is constrained
     *          to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *      declare the specified field.
     * @access public
     */
    function getTypeDeclaration($field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        switch ($field['type']) {
        case 'text':
            $length = !empty($field['length'])
                ? $field['length'] : $db->options['default_text_field_length'];
            $fixed = !empty($field['fixed']) ? $field['fixed'] : false;
            return $fixed ? 'CHAR('.$length.')' : 'VARCHAR('.$length.')';
        case 'clob':
            return 'CLOB';
        case 'blob':
            return 'BLOB';
        case 'integer':
            return 'INT';
        case 'boolean':
            return 'BOOLEAN';
        case 'date':
            return 'DATE';
        case 'time':
            return 'TIME';
        case 'timestamp':
            return 'TIMESTAMP';
        case 'float':
            return 'FLOAT';
        case 'decimal':
            $length = !empty($field['length']) ? $field['length'] : 18;
            $scale = !empty($field['scale']) ? $field['scale'] : $db->options['decimal_places'];
            return 'DECIMAL('.$length.','.$scale.')';
        }
        return '';
    }

    // }}}
    // {{{ _quoteBoolean()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @param bool $quote determines if the value should be quoted and escaped
     * @param bool $escape_wildcards if to escape escape wildcards
     * @return string text string that represents the given argument value in
     *       a DBMS specific format.
     * @access protected
     */
    function _quoteBoolean($value, $quote, $escape_wildcards)
    {
        return ($value ? 'True' : 'False');
    }

    // }}}
    // {{{ _quoteDate()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @param bool $quote determines if the value should be quoted and escaped
     * @param bool $escape_wildcards if to escape escape wildcards
     * @return string text string that represents the given argument value in
     *        a DBMS specific format.
     * @access protected
     */
    function _quoteDate($value, $quote, $escape_wildcards)
    {
        return 'DATE'.$this->_quoteText($value, $quote, $escape_wildcards);
    }

    // }}}
    // {{{ _quoteTimestamp()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @param bool $quote determines if the value should be quoted and escaped
     * @param bool $escape_wildcards if to escape escape wildcards
     * @return string text string that represents the given argument value in
     *        a DBMS specific format.
     * @access protected
     */
    function _quoteTimestamp($value, $quote, $escape_wildcards)
    {
        return 'TIMESTAMP'.$this->_quoteText($value, $quote, $escape_wildcards);
    }

    // }}}
    // {{{ _quoteTime()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     *        compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @param bool $quote determines if the value should be quoted and escaped
     * @param bool $escape_wildcards if to escape escape wildcards
     * @return string text string that represents the given argument value in
     *        a DBMS specific format.
     * @access protected
     */
    function _quoteTime($value, $quote, $escape_wildcards)
    {
        return 'TIME'.$this->_quoteText($value, $quote, $escape_wildcards);
    }
    
    // }}}
    // {{{ _mapNativeDatatype()

    /**
     * Maps a native array description of a field to a MDB2 datatype and length
     *
     * @param array  $field native field description
     * @return array containing the various possible types, length, sign, fixed
     * @access public
     */
    function _mapNativeDatatype($field)
    {
        $db_type = strtolower($field['type']);
        $length = $field['length'];
        if ($length == '-1' && !empty($field['atttypmod'])) {
            $length = $field['atttypmod'] - 4;
        }
        $type = array();
        $unsigned = $fixed = null;
        switch ($db_type) {
        case 'tinyint':
            $type[] = 'integer';
            $unsigned = preg_match('/ unsigned/i', $field['type']);
            $length = 1;
            break;
        case 'smallint':
            $type[] = 'integer';
            $unsigned = false;
            $length = 2;
            if ($length == '2') {
                $type[] = 'boolean';
                if (preg_match('/^(is|has)/', $field['name'])) {
                    $type = array_reverse($type);
                }
            }
            break;
        case 'int':
        case 'integer':
            $type[] = 'integer';
            $unsigned = false;
            $length = 4;
            break;
        case 'longint':
            $type[] = 'integer';
            $unsigned = false;
            $length = 8;
            break;
        case 'boolean':
            $type[] = 'boolean';
            $length = null;
            break;
        case 'character varying':
        case 'char varying':
        case 'varchar':
        case 'national character varying':
        case 'national char varying':
        case 'nchar varying':
            $fixed = false;
        case 'unknown':
        case 'char':
        case 'character':
        case 'national character':
        case 'national char':
        case 'nchar':
            $type[] = 'text';
            if (strstr($db_type, 'text')) {
                $type[] = 'clob';
                $type = array_reverse($type);
            }
            if ($fixed !== false) {
                $fixed = true;
            }
            break;
        case 'date':
            $type[] = 'date';
            $length = null;
            break;
        case 'timestamp':
        case 'timestamp with time zone':
            $type[] = 'timestamp';
            $length = null;
            break;
        case 'time':
        case 'time with time zone':
            $type[] = 'time';
            $length = null;
            break;
        case 'float':
        case 'double precision':
        case 'real':
            $type[] = 'float';
            break;
        case 'decimal':
        case 'numeric':
            $type[] = 'decimal';
            break;
        case 'blob':
            $type[] = 'blob';
            $length = null;
            break;
        case 'clob':
            $type[] = 'clob';
            $length = null;
            break;
        default:
            $db =& $this->getDBInstance();
            if (PEAR::isError($db)) {
                return $db;
            }
            return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'unknown database attribute type: '.$db_type, __FUNCTION__);
        }

        if ((int)$length <= 0) {
            $length = null;
        }

        return array($type, $length, $unsigned, $fixed);
    }

    // }}}
}
?>