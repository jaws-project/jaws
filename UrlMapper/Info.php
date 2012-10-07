<?php
/**
 * UrlMapper Core Gadget
 *
 * @category   GadgetInfo
 * @package    UrlMapper
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class UrlMapperInfo extends Jaws_GadgetInfo
{
    /**
     * Gadget version
     *
     * @var    string
     * @access private
     */
    var $_Version = '0.3.3';

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
        'EditMaps',
    );

}