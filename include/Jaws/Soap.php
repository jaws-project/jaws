<?php
/**
 * Class that deals like a wrapper between Jaws and PHP::SoapClient/PHP::SoapServer
 *
 * @category    Application
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Soap
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  protected
     */
    protected $app = null;

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    function __construct()
    {
        $this->app = Jaws::getInstance();
    }

}