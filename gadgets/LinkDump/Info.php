<?php
/**
 * LinkDump Gadget
 *
 * @category   GadgetInfo
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amir@iranamp.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class LinkDump_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '1.1.0';

    /**
     * Recommended gadgets
     *
     * @var     array
     * @access  public
     */
    var $recommended = array('Tags');

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'Links';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'LinkDump';

}