<?php
/**
 * Glossary Gadget (layout actions)
 *
 * @category   Gadget
 * @package    Glossary
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class GlossaryLayoutHTML extends Jaws_Gadget_HTML
{
    /**
     * Look for a random term and prints it
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function RandomTerms()
    {
        $tpl = new Jaws_Template('gadgets/Glossary/templates/');
        $tpl->Load('Random.html');
        $tpl->SetBlock('random');
        $model = $GLOBALS['app']->LoadGadget('Glossary', 'Model');
        $term = $model->GetRandomTerm();
        if (!Jaws_Error::IsError($term)) {
            $tpl->SetVariable('title', _t('GLOSSARY_RANDOM_TERM'));
            $tpl->SetVariable('term', $term['term']);
            $tpl->SetVariable('description', Jaws_Gadget::ParseText($term['description'], 'Glossary'));
        }
        $tpl->ParseBlock('random');

        return $tpl->Get();
    }

    /**
     * Looks for a list of terms (general) and prints them
     *
     * @access   public
     * @return   string XHTML template Content
     */
    function ListOfTerms()
    {
        $tpl = new Jaws_Template('gadgets/Glossary/templates/');
        $tpl->Load('SimpleList.html');
        $tpl->SetBlock('list_of_terms');
        $tpl->SetVariable('title', _t('GLOSSARY_NAME'));
        $model = $GLOBALS['app']->LoadGadget('Glossary', 'Model');
        $terms = $model->GetTerms();
        if (!Jaws_Error::IsError ($terms)) {
            foreach ($terms as $term) {
                $tpl->SetBlock('list_of_terms/term');
                $tpl->SetVariable('term', $term['term']);
                $tid = empty($term['fast_url']) ? $term['id'] : $term['fast_url'];
                $tpl->SetVariable('url', $GLOBALS['app']->Map->GetURLFor('Glossary', 'ViewTerm', array('term' => $tid)));
                $tpl->ParseBlock('list_of_terms/term');
            }
        }
        $tpl->ParseBlock('list_of_terms');

        return $tpl->Get();
    }

}