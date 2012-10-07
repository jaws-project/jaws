<?php
/*
 * PiwiTranslate.php - Class to manage some translations of Piwi
 *                     error messages in most cases
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */

class PiwiTranslate
{

    /*
     * Translates a given string
     *
     * @param   string $string String to translate
     * @return  string The translated string
     * @access  public
     */
    function translate($string)
    {
        $file = PIWI_PATH . '/Lang/' . $GLOBALS['piwi']['lang'] . '.php';
        if (file_exists($file)) {
            require_once $file;
        }

        return $string;
    }
}
?>