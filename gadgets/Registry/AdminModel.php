<?php
/**
 * Registry Core Gadget
 *
 * @category   GadgetModel
 * @package    Registry
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class RegistryAdminModel extends Jaws_Gadget_Model
{
    /**
     * Installs the gadget
     *
     * @access       public
     * @return       true on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        $this->AddRegistry('pluggable', 'false');
        return true;
    }
}