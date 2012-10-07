<?php
/**
* @package JPSpan
* @subpackage Serializer
* @version $Id: JSON.php,v 1.0 2009/12/18 14:53:50 afz Exp $
*/
//---------------------------------------------------------------------------

/**
* JSON Serialize strings
* @package JPSpan
* @subpackage Serializer
* @access public
*/
class JPSpan_Serializer_JSON
{

    function serialize($data)
    {
        return Jaws_UTF8::json_encode($data);
    }

}
