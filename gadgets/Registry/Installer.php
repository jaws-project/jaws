<?php
/**
 * Registry Installer
 *
 * @category    GadgetModel
 * @package     Registry
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Registry_Installer extends Jaws_Gadget_Installer
{
    /**
     * Installs the gadget
     *
     * @access       public
     * @return       true on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        $this->gadget->registry->add('pluggable', 'false');
        return true;
    }

}