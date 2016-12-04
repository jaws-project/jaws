<?php
/**
 * Faq - Sitemap hook
 *
 * @category    GadgetHook
 * @package     Faq
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Faq_Hooks_Sitemap extends Jaws_Gadget_Hook
{
    /**
     * Fetch items can be included in sitemap
     *
     * @access  public
     * @param   int     $data_type      Data type
     * @param   int     $updated_time   Last updated time
     *                  (0: first level of categories, 1: all levels of categories, 2: flatted all items)
     * @return  mixed   Array of data otherwise Jaws_Error
     */
    function Execute($data_type = 0, $updated_time = 0)
    {
        $result = array(
            '/' => array(
                'id'     => 0,
                'parent' => 0,
                'title'  => _t('FAQ_TITLE'),
                'url'    => $this->gadget->urlMap('View', array(), true)
            ),
            'levels' => array(),
            'items'  => array()
        );
        if ($data_type == 0) {
            $gModel = $this->gadget->model->load('Category');
            $categories = $gModel->GetCategories();
            if (Jaws_Error::IsError($categories)) {
                return $categories;
            }

            foreach ($categories as $category) {
                $result['levels'][] = array(
                    'id'     => $category['id'],
                    'title'  => $category['category'],
                );
            }
        } elseif ($data_type == 1 || $data_type == 2) {
            $gModel = $this->gadget->model->load('Category');
            $categories = $gModel->GetCategories();
            if (Jaws_Error::IsError($categories)) {
                return $categories;
            }
            foreach ($categories as $category) {
                $cat = empty($category['fast_url']) ? $category['id'] : $category['fast_url'];
                $result['levels'][] = array(
                    'id'     => $category['id'],
                    'parent' => $category['id'],
                    'title'  => $category['category'],
                    'lastmod'=> $category['updatetime'],
                    'url'    => $this->gadget->urlMap('ViewCategory', array('id' => $cat), true),
                );
            }

            if ($data_type == 2) {
                $pModel = $this->gadget->model->load('Question');
                $questions = $pModel->GetAllQuestions(array('published' => true));
                if (Jaws_Error::IsError($questions)) {
                    return $questions;
                }
                foreach ($questions as $question) {
                    $entry = empty($question['fast_url']) ? $question['id'] : $question['fast_url'];
                    $result['items'][] = array(
                        'id'        => $question['id'],
                        'parent'    => $question['category'],
                        'title'     => $question['question'],
                        'lastmod'   => $question['updatetime'],
                        'url'       => $this->gadget->urlMap('ViewQuestion', array('id' => $entry), true),
                    );
                }
            }
        }
        return $result;
    }

}