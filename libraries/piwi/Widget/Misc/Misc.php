<?php
/**
 * Container.php - Main Class for all container widgets
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Widget.php';

class Misc extends Widget
{
    /**
     * Misc Initializer
     *
     * @access private
     */
    function init()
    {
        $this->_packable     = false;
        $this->_familyWidget = 'misc';
        parent::init();
    }
}
?>