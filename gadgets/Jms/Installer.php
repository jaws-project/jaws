<?php
/**
 * JMS Installer
 *
 * @category    GadgetModel
 * @package     JMS
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jms_Installer extends Jaws_Gadget_Installer
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  bool    True
     */
    function Install()
    {
        $this->gadget->AddRegistry('pluggable', 'false');
        return true;
    }

}