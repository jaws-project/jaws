<?php
/**
 * SimpleSite - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    SimpleSite
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class SimpleSiteURLListHook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   List of URLs
     */
    function Hook()
    {
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('SimpleSite', 'Sitemap'),
                        'title' => _t('SIMPLESITE_SITEMAP'));
        return $urls;
    }
}
