<?php
/**
 * Layout Core Gadget
 *
 * @category   GadgetInfo
 * @package    Layout
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Layout_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '4.0.0';

    /**
     * Is this gadget core gadget?
     *
     * @var    boolean
     * @access  private
     */
    var $_IsCore = true;

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'Layout';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Layout';

    /**
     * Logged user ID(readonly)
     *
     * @var     int
     * @access  public
     */
    public $user = 0; // readonly

    /**
     * Loaded theme name(readonly)
     *
     * @var     string
     * @access  public
     */
    public $theme = 0; // readonly

    /**
     * Locality of loaded theme(readonly)
     *
     * @var     int
     * @access  public
     */
    public $locality = 0; // readonly

    /**
     * Constructor
     *
     * @access  protected
     * @param   string  $gadget Gadget's name(filesystem name)
     * @return  void
     */
    protected function __construct($gadget)
    {
        parent::__construct($gadget);
        if (isset($GLOBALS['app'])) {
            $this->user     = (int)$GLOBALS['app']->Session->GetAttribute('user');
            $default_theme  = unserialize($GLOBALS['app']->Registry->fetch('theme', 'Settings'));
            $this->theme    =  $default_theme['name'];
            $this->locality = (int)$default_theme['locality'];
        } else {
            $this->user = 0;
            $this->theme = 'jaws';
            $this->locality = 0;
        }
    }

}