<?php
/**
 * Glossary Gadget
 *
 * @category   Gadget
 * @package    Glossary
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Glossary_Actions_Term extends Jaws_Gadget_Action
{

    /**
     * Look for all the terms, order them and prints them all together
     *
     * @access  public
     * @return  string  XHTML template Content
     */
    function ViewTerms()
    {
        $tpl = $this->gadget->template->load('AlphabeticList.html');
        $tpl->SetBlock('list');
        $tpl->SetVariable('title', _t('GLOSSARY_NAME'));
        $this->SetTitle(_t('GLOSSARY_NAME'));

        $model = $this->gadget->model->load('Term');
        $terms = $model->GetTerms();
        if (!Jaws_Error::IsError($terms)) {
            $last_letter = null;
            foreach ($terms as $term) {
                $letter = Jaws_UTF8::substr($term['term'], 0, 1);
                if ($letter !== $last_letter) {
                    $last_letter = $letter;

                    //close opened block
                    if (!is_null($last_letter)) {
                        $tpl->ParseBlock ('list/letter');
                    }

                    $tpl->SetBlock('list/letters');
                    $tpl->SetVariable('letter', $letter);
                    $tpl->SetVariable('url', $this->gadget->urlMap('ViewTerms'));
                    $tpl->ParseBlock ('list/letters');

                    //open new block
                    $tpl->SetBlock('list/letter');
                    $tpl->SetVariable('letter', $letter);
                }

                $tpl->SetBlock('list/letter/term');
                $tpl->SetVariable('term', $term['term']);
                $tid = empty($term['fast_url']) ? $term['id'] : $term['fast_url'];
                $tpl->SetVariable('url',  $this->gadget->urlMap('ViewTerm', array('term' => $tid)));
                $tpl->SetVariable('description', $this->gadget->ParseText($term['description']));
                $tpl->ParseBlock('list/letter/term');
            }
        }

        if (!empty($terms)) {
            $tpl->ParseBlock ('list/letter');
        }

        $tpl->ParseBlock ('list');
        return $tpl->Get ();
    }

    /**
     * Look for a term and prints it
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ViewTerm()
    {
        $term = jaws()->request->fetch('term', 'get');
        $term = Jaws_XSS::defilter($term, true);

        $model = $this->gadget->model->load('Term');
        $term = $model->GetTerm($term);
        if (!Jaws_Error::IsError($term) && isset($term['term'])) {
            $this->SetTitle($term['term']);

            $tpl = $this->gadget->template->load('ViewTerm.html');
            $tpl->SetBlock('definition');
            $tpl->SetVariable('title', _t('GLOSSARY_NAME'));

            $date = Jaws_Date::getInstance();
            $tpl->SetBlock('definition/term');
            $tpl->SetVariable('term', $term['term']);
            $tid = empty($term['fast_url']) ? $term['id'] : $term['fast_url'];
            $tpl->SetVariable('url', $this->gadget->urlMap('ViewTerm', array('term' => $tid)));
            $tpl->SetVariable('description', $this->gadget->ParseText($term['description']));
            $tpl->SetVariable('created_in', _t('GLOBAL_CREATETIME'));
            $tpl->SetVariable('updated_in', _t('GLOBAL_UPDATETIME'));
            $tpl->SetVariable('createtime', $date->Format($term['createtime']));
            $tpl->SetVariable('updatetime', $date->Format($term['updatetime']));
            $tpl->ParseBlock('definition/term');
            $tpl->ParseBlock ('definition');
        } else {
            return Jaws_HTTPError::Get(404);
        }

        return $tpl->Get();
    }

    /**
     * Look for a random term and prints it
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function RandomTerms()
    {
        $tpl = $this->gadget->template->load('Random.html');
        $tpl->SetBlock('random');
        $model = $this->gadget->model->load('Term');
        $term = $model->GetRandomTerm();
        if (!Jaws_Error::IsError($term)) {
            $tpl->SetVariable('title', _t('GLOSSARY_RANDOM_TERM'));
            $tpl->SetVariable('term', $term['term']);
            $tpl->SetVariable('description', $this->gadget->ParseText($term['description']));
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
        $tpl = $this->gadget->template->load('SimpleList.html');
        $tpl->SetBlock('list_of_terms');
        $tpl->SetVariable('title', _t('GLOSSARY_NAME'));
        $model = $this->gadget->model->load('Term');
        $terms = $model->GetTerms();
        if (!Jaws_Error::IsError ($terms)) {
            foreach ($terms as $term) {
                $tpl->SetBlock('list_of_terms/term');
                $tpl->SetVariable('term', $term['term']);
                $tid = empty($term['fast_url']) ? $term['id'] : $term['fast_url'];
                $tpl->SetVariable('url', $this->gadget->urlMap('ViewTerm', array('term' => $tid)));
                $tpl->ParseBlock('list_of_terms/term');
            }
        }
        $tpl->ParseBlock('list_of_terms');

        return $tpl->Get();
    }
}