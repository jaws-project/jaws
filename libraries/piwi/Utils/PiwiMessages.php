<?php
/*
 * PiwiMessages.php - Class to manage messages from Piwi (errors & warnings)
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */

class PiwiMessages
{
    /*
     * Mark-up a message as error
     *
     * @param   string $string String to mark-up
     * @access  public
     */
    function error($string)
    {
        echo "<b style=\"color: #f00;\"> Piwi Fatal Error:</b><br/>".$string;
    }
}
?>