<?php
/**
* @package JPSpan
* @subpackage Unserialzier
* @version $Id: JSON.php,v 1.0 2009/12/18 14:53:50 afz Exp $
*/
//---------------------------------------------------------------------------

/**
* Unserializes JSON serialized strings
* @package JPSpan
* @subpackage Unserialzier
* @access public
*/
class JPSpan_Unserializer_JSON
{
    /**
    * Unserialize a string into PHP data types.
    * @param string data serialized with JSON's serialization protocol
    * @return mixed PHP data
    * @access public
    */
    function unserialize($data)
    {
        return Jaws_UTF8::json_decode($data);
    }

}
