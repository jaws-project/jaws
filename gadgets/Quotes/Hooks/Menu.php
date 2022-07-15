<?php
/**
 * Quotes - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Quotes
 */
class Quotes_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls = array();
        $urls[] = array(
            'url'   => $this->gadget->urlMap('recentQuotes'),
            'title' => $this::t('ACTIONS_RECENTQUOTES_TITLE')
        );
        return $urls;
    }
}