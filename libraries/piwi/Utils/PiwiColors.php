<?php
/*
 * PiwiColors.php - Class to manage some random colors (for combos, grids, etc)
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */

class PiwiColors
{
    /*
     * Choose the alternate color
     *
     * @param   string $string String to mark-up
     * @return  string The color, just alternated
     * @access  public
     */
    function alternate($color)
    {
        if ($color == '#fff') {
            return '#eee';
        }

        return '#fff';
    }
}
?>