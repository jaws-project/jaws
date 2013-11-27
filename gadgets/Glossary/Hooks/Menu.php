<?php
/**
 * Glossary - URL List gadget hook
 *
 * @category   GadgetHook
 * @package    Glossary
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Glossary_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget 
     * can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls   = array();
        $urls[] = array('url'   => $this->gadget->urlMap('ViewTerms'),
                        'title' => _t('GLOSSARY_NAME'));
        return $urls;
    }

}