<?php
/**
 * Glossary - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Glossary
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class GlossaryURLListHook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     */
    function Hook()
    {
        $urls   = array();
        $urls[] = array('url'   => $GLOBALS['app']->Map->GetURLFor('Glossary', 'DefaultAction'),
                        'title' => _t('GLOSSARY_NAME'));
        return $urls;
    }
}
