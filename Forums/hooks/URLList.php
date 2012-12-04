<?php
/**
 * Forums - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Forums
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ForumsURLListHook
{
    /**
     * Returns an array with all available items for Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Hook()
    {
        $urls = array();
        $urls[] = array(
            'url'   => $GLOBALS['app']->Map->GetURLFor('Forums', 'Forums'),
            'title' => _t('FORUMS_FORUMS')
        );

        return $urls;
    }

}