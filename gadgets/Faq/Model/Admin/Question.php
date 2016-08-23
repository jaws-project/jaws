<?php
/**
 * Faq Admin Gadget
 *
 * @category   GadgetModel
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_Model_Admin_Question extends Faq_Model_Question
{
    /**
     * Max question position
     *
     * @access  public
     * @param   int     $category   Category ID
     * @return  int     Max position
     */
    function GetMaxQuestionPosition($category)
    {
        $faqTable = Jaws_ORM::getInstance()->table('faq');
        $max = $faqTable->select('max(faq_position)')->where('category', $category)->fetchOne();
        if (Jaws_Error::IsError($max)) {
            $max = 0;
        }

        return $max;
    }

    /**
     * Insert a new Question
     *
     * @access  public
     * @param   array   $data     Question data
     * @return  mixed   True if question is succesfully added, Jaws_Error if not
     */
    function InsertQuestion($data)
    {
        $fast_url = empty($data['fast_url']) ? $data['question'] : $data['fast_url'];
        $data['fast_url'] = $this->GetRealFastUrl($fast_url, 'faq');

        $now = Jaws_DB::getInstance()->date();
        $data['faq_position'] = $this->GetMaxQuestionPosition($data['category']) + 1;
        $data['createtime']   = $now;
        $data['updatetime']   = $now;

        $faqTable = Jaws_ORM::getInstance()->table('faq');
        return $faqTable->insert($data)->exec();
    }

    /**
     * Update a question
     *
     * @access  public
     * @param   string  $id         Number of the question
     * @param   array   $data     Question data
     * @return  mixed   True if question is succesfully updated, Jaws_Error if not
     */
    function UpdateQuestion($id, $data)
    {
        $fast_url = empty($data['fast_url']) ? $data['question'] : $data['fast_url'];
        $data['fast_url'] = $this->GetRealFastUrl($fast_url, 'faq');
        $data['updatetime']   = Jaws_DB::getInstance()->date();

        $faqTable = Jaws_ORM::getInstance()->table('faq');
        return $faqTable->update($data)->where('id', $id)->exec();
    }


    /**
     * Delete a question
     *
     * @access  public
     * @param   string  $id     Number of the question
     * @return  bool    True if question is succesfully deleted, Jaws_Error if not
     */
    function DeleteQuestion($id)
    {
        $faqTable = Jaws_ORM::getInstance()->table('faq');
        $rid = $faqTable->select('faq_position')->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($rid)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_QUESTION_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FAQ_ERROR_QUESTION_NOT_DELETED'));
        }

        if (isset($rid['faq_position'])) {
            $faqTable = Jaws_ORM::getInstance()->table('faq');
            $rs = $faqTable->update(
                array('faq_position' => $faqTable->expr('faq_position - ?', 1))
            )->where('faq_position', $rid['faq_position'], '>')->exec();
            if (Jaws_Error::IsError($rs)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_QUESTION_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_QUESTION_NOT_DELETED'));
            }

            $faqTable = Jaws_ORM::getInstance()->table('faq');
            $rs = $faqTable->delete()->where('id', $id)->exec();
            if (Jaws_Error::IsError($rs)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_QUESTION_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_QUESTION_NOT_DELETED'));
            }

            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_QUESTION_DELETED'), RESPONSE_NOTICE);
            return true;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_QUESTION_DOES_NOT_EXISTS'), RESPONSE_ERROR);
        return new Jaws_Error(_t('FAQ_ERROR_QUESTION_DOES_NOT_EXISTS'));
    }

    /**
     * Move a given question
     *
     * @access  public
     * @param   int     $cat        Category ID
     * @param   int     $id         Question ID
     * @param   int     $position   Position of question
     * @param   string  $direction  Direction (+1/-1)
     * @return  mixed   Returns true if the question was moved without problems, if not, returns Jaws_Error
     */
    function MoveQuestion($cat, $id, $position, $direction)
    {
        $oldpos = $position;
        $newpos = $position + $direction;

        $catModel = $this->gadget->model->loadAdmin('Category');
        if (($newpos < 1) || (($direction > 0) && ($newpos > $catModel->GetMaxCategoryPosition()))) {
            $newpos = $oldpos;
        }

        $faqTable = Jaws_ORM::getInstance()->table('faq');

        //Start Transaction
        $faqTable->beginTransaction();

        $faqTable->update(array('faq_position' => (int)$oldpos))->where('category', (int)$cat);
        $result = $faqTable->and()->where('faq_position', (int)$newpos)->exec();
        if (Jaws_Error::IsError($result)) {
            $result->setMessage(_t('FAQ_ERROR_QUESTION_NOT_MOVED'));
            return $result;
        }

        $faqTable = Jaws_ORM::getInstance()->table('faq');
        $result = $faqTable->update(array('faq_position' => (int)$newpos))->where('id', (int)$id)->exec();
        if (Jaws_Error::IsError($result)) {
            $result->setMessage(_t('FAQ_ERROR_QUESTION_NOT_MOVED'));
            return $result;
        }

        // commit transaction
        $faqTable->commit();
        return true;
    }

    /**
     * Get the list of questions
     *
     * @access  public
     * @param   int         $category  Category id
     * @return  mixed   Returns an array of questions and Jaws_Error on error
     */
    function GetQuestionsCount($category = null)
    {
        $faqTable = Jaws_ORM::getInstance()->table('faq');
        $faqTable->select('count(id):integer');

        if(!empty($category)) {
            $faqTable->and()->where('category', $category);
        }

        return $faqTable->fetchOne();
    }

}