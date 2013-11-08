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
class Faq_Actions_Question extends Jaws_Gadget_Action
{
    /**
     * Displays a concrete question & answer
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ViewQuestion()
    {
        $qid = jaws()->request->fetch('id', 'get');
        $qid = Jaws_XSS::defilter($qid, true);

        $tpl = $this->gadget->loadTemplate('Question.html');
        $tpl->SetBlock('faq_question');

        $model = $this->gadget->model->load('Question');
        $q = $model->GetQuestion($qid);
        if (!Jaws_Error::IsError($q) && !empty($q)) {
            $this->SetTitle($q['question']);
            $tpl->SetVariable('title', $q['question']);
            $tpl->SetVariable('answer', $this->gadget->ParseText($q['answer']));
        }
        $tpl->ParseBlock('faq_question');

        return $tpl->Get();
    }

    /**
     * Displays complete FAQ to the user: first fastlinks and below questions and answers
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function View()
    {
        $tpl = $this->gadget->loadTemplate('ViewFaq.html');
        $tpl->SetBlock('faq');
        $tpl->SetVariable('title', _t('FAQ_TITLE'));
        $this->SetTitle(_t('FAQ_TITLE'));
        $view_answers = $tpl->BlockExists('faq/answers');

        $model = $this->gadget->model->load('Question');
        $questions = $model->GetQuestions(null, true);
        if (is_array($questions) && count($questions) > 0) {
            $tpl->SetBlock('faq/summary');
            $tpl->SetVariable('contents', _t('FAQ_CONTENTS'));
            foreach ($questions as $cat) {
                $tpl->SetBlock('faq/summary/category');
                $tpl->SetVariable('id', $cat['id']);
                $tpl->SetVariable('category', $cat['category']);
                $cid  = empty($cat['fast_url'])? $cat['id'] : $cat['fast_url'];
                $curl = $this->gadget->urlMap('ViewCategory', array('id' => $cid));
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
                        $qurl = $view_answers? $this->gadget->urlMap('View') : $curl;
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
                        $tpl->SetVariable('top_link', $this->gadget->urlMap('View').'#topfaq');
                        $tpl->SetVariable('id',  $q['id']);
                        $tpl->SetVariable('pos', $qPos);
                        $qid = empty($q['fast_url']) ? $q['id'] : $q['fast_url'];
                        $tpl->SetVariable('url', $this->gadget->urlMap('ViewQuestion', array('id' => $qid)));
                        $tpl->SetVariable('question', $q['question']);
                        $tpl->SetVariable('answer', $this->gadget->ParseText($q['answer']));
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