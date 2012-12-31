<?php
/**
 * Banner Gadget Info
 *
 * @category   GadgetInfo
 * @package    Banner
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Banner_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $_Version = '0.8.2';

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'ManageBanners',
        'ManageGroups',
        'BannersGrouping',
        'ViewReports'
    );

    /**
     * Banners data directory
     *
     * @var     string
     * @access  protected
     */
    var $DataDirectory = 'banners/';

}