<?php
/**
 * Sitemap gadget info
 *
 * @category   GadgetInfo
 * @package    Sitemap
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Sitemap_Info extends Jaws_Gadget
{
    /**
     * Constants
     */
    const SITEMAP_CATEGORY_SHOW_IN_NONE = 0;
    const SITEMAP_CATEGORY_SHOW_IN_XML = 1;
    const SITEMAP_CATEGORY_SHOW_IN_USER_SIDE = 2;
    const SITEMAP_CATEGORY_SHOW_IN_BOTH = 3;

    const SITEMAP_CHANGE_FREQ_NONE = 'none';
    const SITEMAP_CHANGE_FREQ_ALWAYS = 'always';
    const SITEMAP_CHANGE_FREQ_HOURLY = 'hourly';
    const SITEMAP_CHANGE_FREQ_DAILY = 'daily';
    const SITEMAP_CHANGE_FREQ_WEEKLY = 'weekly';
    const SITEMAP_CHANGE_FREQ_MONTHLY = 'monthly';
    const SITEMAP_CHANGE_FREQ_YEARLY = 'yearly';
    const SITEMAP_CHANGE_FREQ_NEVER = 'never';

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
    var $default_action = 'Sitemap';

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'ManageSitemap';
}