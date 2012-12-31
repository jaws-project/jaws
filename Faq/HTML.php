<?php
/**
 * Faq Gadget
 *
 * @category   Gadget
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_HTML extends Jaws_Gadget_HTML
{
    /**
     * Calls default action(View)
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function DefaultAction()
    {
        return $this->View();
    }

    /**
     * Displays a concrete question & answer
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ViewQuestion()
    {
        $request =& Jaws_Request::getInstance();
        $qid = $request->get('id', 'get');

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $qid = $xss->defilter($qid, true);

        $tpl = new Jaws_Template('gadgets/Faq/templates/');
        $tpl->Load('Question.html');
        $tpl->SetBlock('faq_question');

        $model = $GLOBALS['app']->LoadGadget('Faq', 'Model');
        $q = $model->GetQuestion($qid);
        if (!Jaws_Error::IsError($q) && !empty($q)) {
            $this->SetTitle($q['question']);
            $tpl->SetVariable('title', $q['question']);
            $tpl->SetVariable('answer', $this->gadget->ParseText($q['answer'], 'Faq'));
        }
        $tpl->ParseBlock('faq_question');

        return $tpl->Get();
    }

    /**
     * Displays a concrete category
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ViewCategory()
    {
        $model = $GLOBALS['app']->LoadGadget('Faq', 'Model');

        $request =& Jaws_Request::getInstance();
        $cat_id  = $request->get('id', 'get');

        $xss  = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $cat_id  = $xss->defilter($cat_id, true);

        $this->SetTitle(_t('FAQ_NAME') . ' - ' . _t('FAQ_CATEGORIES'));
        $questions = $model->GetQuestions($cat_id, true);
        if (is_array($questions) && count($questions) > 0) {
            $tpl = new Jaws_Template('gadgets/Faq/templates/');
            $tpl->Load('Category.html');
            foreach ($questions as $cat) {
                $tpl->SetBlock('faq_category');
                $tpl->SetVariable('title', _t('FAQ_TITLE'));
                $tpl->SetVariable('category', $cat['category']);
                $tpl->SetVariable('description', $this->gadget->ParseText($cat['description'], 'Faq'));
                if (isset($cat['questions']) && is_array($cat['questions'])) {
                    $qPos = 0;
                }

                foreach ($cat['questions'] as $q) {
                    $qPos++;
                    $tpl->SetBlock('faq_category/question');
                    $tpl->SetVariable('id',  $q['id']);
                    $tpl->SetVariable('pos', $qPos);
                    $tpl->SetVariable('question', $q['question'], 'Faq', false);
                    $tpl->SetVariable('url', $this->gadget->GetURLFor('ViewCategory', array('id' => $cat_id)));
                    $tpl->ParseBlock('faq_category/question');
                }

                if (isset($cat['questions']) && is_array($cat['questions'])) {
                    $qPos = 0;
                }

                foreach ($cat['questions'] as $q) {
                    $qPos++;
                    $tpl->SetBlock('faq_category/item');
                    $tpl->SetVariable('top_label', _t('FAQ_GO_TO_TOP'));
                    $tpl->SetVariable('top_link', $this->gadget->GetURLFor('ViewCategory', array('id' => $cat_id)).'#topfaq');
                    $tpl->SetVariable('id', $q['id']);
                    $tpl->SetVariable('pos', $qPos);
                    $qid = empty($q['fast_url']) ? $q['id'] : $q['fast_url'];
                    $tpl->SetVariable('url', $this->gadget->GetURLFor('ViewQuestion', array('id' => $qid)));
                    $tpl->SetVariable('question', $q['question']);
                    $tpl->SetVariable('answer', $this->gadget->ParseText($q['answer'], 'Faq'));
                    $tpl->ParseBlock('faq_category/item');
                }
                $tpl->ParseBlock('faq_category');
            }
            return $tpl->Get();
        }

        // FIXME: We should return something like "No questions found"
        return '';
    }

    /**
     * Displays complete FAQ to the user: first fastlinks and below questions and answers
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function View()
    {
        $tpl = new Jaws_Template('gadgets/Faq/templates/');
        $tpl->Load('ViewFaq.html');
        $tpl->SetBlock('faq');
        $tpl->SetVariable('title', _t('FAQ_TITLE'));
        $this->SetTitle(_t('FAQ_TITLE'));
        $view_answers = $tpl->BlockExists('faq/answers');

        $model = $GLOBALS['app']->LoadGadget('Faq', 'Model');
        $questions = $model->GetQuestions(null, true);
        if (is_array($questions) && count($questions) > 0) {
            $tpl->SetBlock('faq/summary');
            $tpl->SetVariable('contents', _t('FAQ_CONTENTS'));
            foreach ($questions as $cat) {
                $tpl->SetBlock('faq/summary/category');
                $tpl->SetVariable('id', $cat['id']);
                $tpl->SetVariable('category', $cat['category']);
                $cid  = empty($cat['fast_url'])? $cat['id'] : $cat['fast_url'];
                $curl = $this->gadget->GetURLFor('ViewCategory', array('id' => $cid));
                $tpl->SetVariable('url', $curl);
                $tpl->SetVariable('description', $cat['description']);
                if (isset($cat['questions']) && is_array($cat['questions'])) {
                    $qPos = 0;
                    foreach ($cat['questions'] as $q) {
                        $qPos++;
                        $tpl->SetBlock('faq/summary/category/item');
                        $tpl->SetVariable('id',  $q['id']);
                        $tpl->SetVariable('pos', $qPos);
                        $tpl->SetVariable('question', $q['question'], 'Faq', false);
                        $qurl = $view_answers? $this->gadget->GetURLFor('View') : $curl;
                        $tpl->SetVariable('url', $qurl);
                        $tpl->ParseBlock('faq/summary/category/item');
                    }
                }
                $tpl->ParseBlock('faq/summary/category');
            }
            $tpl->ParseBlock('faq/summary');

            if ($view_answers) {
                $tpl->SetBlock('faq/answers');
                $catPos = 0;
                foreach ($questions as $cat) {
                    $catPos++;
                    $tpl->SetBlock('faq/answers/category');
                    $tpl->SetVariable('id',  $cat['id']);
                    $tpl->SetVariable('pos', $catPos);
                    $tpl->SetVariable('category', $cat['category']);
                    if (isset($cat['questions']) && is_array($cat['questions'])) {
                        $qPos = 0;
                    }

                    foreach ($cat['questions'] as $q) {
                        $qPos++;
                        $tpl->SetBlock('faq/answers/category/question');
                        $tpl->SetVariable('top_label', _t('FAQ_GO_TO_TOP'));
                        $tpl->SetVariable('top_link', $this->gadget->GetURLFor('View').'#topfaq');
                        $tpl->SetVariable('id',  $q['id']);
                        $tpl->SetVariable('pos', $qPos);
                        $qid = empty($q['fast_url']) ? $q['id'] : $q['fast_url'];
                        $tpl->SetVariable('url', $this->gadget->GetURLFor('ViewQuestion', array('id' => $qid)));
                        $tpl->SetVariable('question', $q['question']);
                        $tpl->SetVariable('answer', $this->gadget->ParseText($q['answer'], 'Faq'));
                        $tpl->ParseBlock('faq/answers/category/question');
                    }
                    $tpl->ParseBlock('faq/answers/category');
                }
                $tpl->ParseBlock('faq/answers');
            }
        }
        $tpl->ParseBlock('faq');

        return $tpl->Get();
    }

}