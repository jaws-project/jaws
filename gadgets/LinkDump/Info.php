<?php
/**
 * LinkDump Gadget
 *
 * @category   GadgetInfo
 * @package    LinkDump
 * @author     Amir Mohammad Saied <amir@iranamp.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
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
    var $version = '0.9.0';

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'Categories';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'LinkDump';

}