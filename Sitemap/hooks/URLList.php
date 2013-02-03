<?php
/**
 * Sitemap - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Sitemap
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class SitemapURLListHook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Hook()
    {
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('Sitemap', 'Sitemap'),
                        'title' => _t('SITEMAP_SITEMAP'));
        return $urls;
    }
}
