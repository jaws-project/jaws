<?php
/**
 * Languages Core Gadget
 *
 * @category   LanguagesInfo
 * @package    Languages
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class LanguagesInfo extends Jaws_GadgetInfo
{
    /**
     * Gadget version
     *
     * @var    string
     * @access private
     */
    var $_Version = '0.2.1';

    /**
     * Is this gadget core gadget?
     *
     * @var    boolean
     * @access private
     */
    var $_IsCore = true;

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'ModifyLanguageProperties',
    );

}