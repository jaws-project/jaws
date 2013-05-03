<?php
/**
 * COMPONENTS Installer
 *
 * @category    GadgetModel
 * @package     COMPONENTS
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Components_Installer extends Jaws_Gadget_Installer
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  bool    True
     */
    function Install()
    {
        $this->gadget->registry->insert('pluggable', 'false');
        return true;
    }

}