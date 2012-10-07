<?php
// $Id: Null.php 259271 2008-05-07 21:26:19Z jeichorn $
/**
 * Null Serializer
 *
 * @category   HTML
 * @package    AJAX
 * @author     Joshua Eichorn <josh@bluga.net>
 * @copyright  2005 Joshua Eichorn
 * @license    http://www.opensource.org/licenses/lgpl-license.php  LGPL
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PackageName
 */
class HTML_AJAX_Serializer_Null 
{
    
    function serialize($input) 
    {
        return $input;
    }

    function unserialize($input) 
    {
        return $input;
    }
}
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
?>
