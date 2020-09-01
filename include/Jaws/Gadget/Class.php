<?php
/**
 * Jaws Gadget Class base of gadget components
 *
 * @category    Gadget
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Class
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  protected
     */
    var $gadget = null;

    /**
     * constructor
     *
     * @access  public
     * @param   object  $gadget Jaws_Gadget object
     * @return  void
     */
    function __construct($gadget)
    {
        $this->gadget = $gadget;
        $this->app = Jaws::getInstance();
    }

    /**
     * Convenience function to translate strings
     *
     * @param   string  $params Method parameters
     *
     * @return string
     */
    public static function t($params)
    {
        return Jaws_Translate::getInstance()->XTranslate(get_called_class(), func_get_args());
    }

}