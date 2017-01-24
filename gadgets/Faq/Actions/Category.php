<?php
/**
 * Faq Gadget
 *
 * @category   Gadget
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_Actions_Category extends Jaws_Gadget_Action
{
    /**
     * Displays a list with links to each category
     *
     * @access  public
     * @return  string  XHTML template content
     */
    function ListCategories()
    {
        $tpl = $this->gadget->template->load('Categories.html');
        $tpl->SetBlock('faq_categories');
        $tpl->SetVariable('title', _t('FAQ_CATEGORIES'));
        $model = $this->gadget->model->load('Category');
        $cats = $model->GetCategories();
        if (is_array($cats) && count($cats) > 0) {
            foreach ($cats as $c) {
                $tpl->SetBlock('faq_categories/item');
                $tpl->SetVariable('id', $c['id']);
                $id = empty($c['fast_url']) ? $c['id'] : $c['fast_url'];
                $tpl->SetVariable('url', $this->gadget->urlMap('ViewCategory', array('id' => $id)));
                $tpl->SetVariable('category',$c['category']);
                $tpl->ParseBlock('faq_categories/item');
            }
        }
        $tpl->ParseBlock('faq_categories');

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
        $model = $this->gadget->model->load('Question');

        $cat_id = jaws()->request->fetch('id', 'get');
        $cat_id = Jaws_XSS::defilter($cat_id);

        $this->SetTitle($this->gadget->title . ' - ' . _t('FAQ_CATEGORIES'));
        $questions = $model->GetQuestions($cat_id, true);
        if (is_array($questions) && count($questions) > 0) {
            $tpl = $this->gadget->template->load('Category.html');
            foreach ($questions as $cat) {
                $tpl->SetBlock('faq_category');
                $tpl->SetVariable('title', _t('FAQ_TITLE'));
                $tpl->SetVariable('category', $cat['category']);
                $tpl->SetVariable('description', $this->gadget->plugin->parseAdmin($cat['description']));
                if (isset($cat['questions']) && is_array($cat['questions'])) {
                    $qPos = 0;
                }

                foreach ($cat['questions'] as $q) {
                    $qPos++;
                    $tpl->SetBlock('faq_category/question');
                    $tpl->SetVariable('id',  $q['id']);
                    $tpl->SetVariable('pos', $qPos);
                    $tpl->SetVariable('question', $q['question'], 'Faq', false);
                    $tpl->SetVariable('url', $this->gadget->urlMap('ViewCategory', array('id' => $cat_id)));
                    $tpl->ParseBlock('faq_category/question');
                }

                if (isset($cat['questions']) && is_array($cat['questions'])) {
                    $qPos = 0;
                }

                foreach ($cat['questions'] as $q) {
                    $qPos++;
                    $tpl->SetBlock('faq_category/item');
                    $tpl->SetVariable('top_label', _t('FAQ_GO_TO_TOP'));
                    $tpl->SetVariable('top_link', $this->gadget->urlMap('ViewCategory', array('id' => $cat_id)).'#topfaq');
                    $tpl->SetVariable('id', $q['id']);
                    $tpl->SetVariable('pos', $qPos);
                    $qid = empty($q['fast_url']) ? $q['id'] : $q['fast_url'];
                    $tpl->SetVariable('url', $this->gadget->urlMap('ViewQuestion', array('id' => $qid)));
                    $tpl->SetVariable('question', $q['question']);
                    $tpl->SetVariable('answer', $this->gadget->plugin->parseAdmin($q['answer']));
                    $tpl->ParseBlock('faq_category/item');
                }
                $tpl->ParseBlock('faq_category');
            }
            return $tpl->Get();
        }

        // FIXME: We should return something like "No questions found"
        return '';
    }
}