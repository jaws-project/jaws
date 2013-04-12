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
class Glossary_HTML extends Jaws_Gadget_HTML
{
    /**
     * Runs the default action
     *
     * @access  public
     * @return  string  HTML content of Default action
     */
    function DefaultAction()
    {
        return $this->Display();
    }

    /**
     * Look for a term and prints it
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ViewTerm()
    {
        $request =& Jaws_Request::getInstance();
        $term = $request->get('term', 'get');
        $term = Jaws_XSS::defilter($term, true);

        $model = $GLOBALS['app']->LoadGadget('Glossary', 'Model');
        $term = $model->GetTerm($term);
        if (!Jaws_Error::IsError($term) && isset($term['term'])) {
            $this->SetTitle($term['term']);
            $tpl = new Jaws_Template('gadgets/Glossary/templates/');
            $tpl->Load('ViewTerm.html');
            $tpl->SetBlock('definition');
            $tpl->SetVariable('title', _t('GLOSSARY_NAME'));

            $date = $GLOBALS['app']->loadDate();
            $tpl->SetBlock('definition/term');
            $tpl->SetVariable('term', $term['term']);
            $tid = empty($term['fast_url']) ? $term['id'] : $term['fast_url'];
            $tpl->SetVariable('url', $this->gadget->GetURLFor('ViewTerm', array('term' => $tid)));
            $tpl->SetVariable('description', $this->gadget->ParseText($term['description']));
            $tpl->SetVariable('created_in', _t('GLOBAL_CREATETIME'));
            $tpl->SetVariable('updated_in', _t('GLOBAL_UPDATETIME'));
            $tpl->SetVariable('createtime', $date->Format($term['createtime']));
            $tpl->SetVariable('updatetime', $date->Format($term['updatetime']));
            $tpl->ParseBlock('definition/term');
            $tpl->ParseBlock ('definition');
        } else {
            require_once JAWS_PATH . 'include/Jaws/HTTPError.php';
            return Jaws_HTTPError::Get(404);
        }

        return $tpl->Get();
    }

    /**
     * Look for all the terms, order them and prints them all together
     *
     * @access  public
     * @return  string  XHTML template Content
     */
    function Display()
    {
        $tpl = new Jaws_Template('gadgets/Glossary/templates/');
        $tpl->Load('AlphabeticList.html');
        $tpl->SetBlock('list');
        $tpl->SetVariable('title', _t('GLOSSARY_NAME'));
        $this->SetTitle(_t('GLOSSARY_NAME'));

        $model = $GLOBALS['app']->LoadGadget('Glossary', 'Model');
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
                    $tpl->SetVariable('url', $this->gadget->GetURLFor('DefaultAction'));
                    $tpl->ParseBlock ('list/letters');

                    //open new block
                    $tpl->SetBlock('list/letter');
                    $tpl->SetVariable('letter', $letter);
                }

                $tpl->SetBlock('list/letter/term');
                $tpl->SetVariable('term', $term['term']);
                $tid = empty($term['fast_url']) ? $term['id'] : $term['fast_url'];
                $tpl->SetVariable('url',  $this->gadget->GetURLFor('ViewTerm', array('term' => $tid)));
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

}