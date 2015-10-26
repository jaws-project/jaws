<?php
/**
 * Sitemap gadget info
 *
 * @category   GadgetInfo
 * @package    Sitemap
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Info extends Jaws_Gadget
{
    /**
     * Constants
     */
    const SITEMAP_CATEGORY_SHOW_IN_NONE = 1;
    const SITEMAP_CATEGORY_SHOW_IN_XML = 2;
    const SITEMAP_CATEGORY_SHOW_IN_USER_SIDE = 3;
    const SITEMAP_CATEGORY_SHOW_IN_BOTH = 4;

    const SITEMAP_CHANGE_FREQ_ALWAYS = 1;
    const SITEMAP_CHANGE_FREQ_HOURLY = 2;
    const SITEMAP_CHANGE_FREQ_DAILY = 3;
    const SITEMAP_CHANGE_FREQ_WEEKLY = 4;
    const SITEMAP_CHANGE_FREQ_MONTHLY = 5;
    const SITEMAP_CHANGE_FREQ_YEARLY = 6;
    const SITEMAP_CHANGE_FREQ_NEVER = 7;

    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '1.1.0';

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = 'Sitemap';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'ManageSitemap';
}