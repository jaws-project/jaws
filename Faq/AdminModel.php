<?php
require_once JAWS_PATH . 'gadgets/Faq/Model.php';
/**
 * Faq Admin Gadget
 *
 * @category   GadgetModel
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_AdminModel extends Faq_Model
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
     * Add a new Question
     *
     * @access  public
     * @param   string  $question   The question
     * @param   string  $fast_url   Fast URL
     * @param   string  $answer     The answer of the question
     * @param   int     $category   Category id
     * @param   bool    $active     Question status
     * @return  mixed   True if question is succesfully added, Jaws_Error if not
     */
    function AddQuestion($question, $fast_url, $answer, $category, $active)
    {
        $fast_url = empty($fast_url) ? $question : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'faq');

        $now = $GLOBALS['db']->Date();
        $params['question']     = $question;
        $params['fast_url']     = $fast_url;
        $params['answer']       = $answer;
        $params['category']     = $category;
        $params['published']    = $active;
        $params['faq_position'] = $this->GetMaxQuestionPosition($category) + 1;
        $params['createtime']   = $now;
        $params['updatetime']   = $now;

        $faqTable = Jaws_ORM::getInstance()->table('faq');
        $result = $faqTable->insert($params)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_QUESTION_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FAQ_ERROR_QUESTION_NOT_ADDED'), _t('FAQ_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_QUESTION_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Update a question
     *
     * @access  public
     * @param   string  $id         Number of the question
     * @param   string  $question   The question
     * @param   string  $fast_url   Fast URL
     * @param   string  $answer     The answer of the question
     * @param   int     $category   Category id
     * @param   bool    $active     Question status
     * @return  mixed   True if question is succesfully updated, Jaws_Error if not
     */
    function UpdateQuestion($id, $question, $fast_url, $answer, $category, $active)
    {
        $fast_url = empty($fast_url) ? $question : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'faq', false);

        $params['question']     = $question;
        $params['fast_url']     = $fast_url;
        $params['answer']       = $answer;
        $params['category']     = $category;
        $params['published']    = $active;
        $params['updatetime']   = $GLOBALS['db']->Date();

        $faqTable = Jaws_ORM::getInstance()->table('faq');
        $result = $faqTable->update($params)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_QUESTION_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FAQ_ERROR_QUESTION_NOT_UPDATED'), _t('FAQ_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_QUESTION_UPDATED'), RESPONSE_NOTICE);
        return true;
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
            return new Jaws_Error(_t('FAQ_ERROR_QUESTION_NOT_DELETED'), _t('FAQ_NAME'));
        }

        if (isset($rid['faq_position'])) {
            $faqTable = Jaws_ORM::getInstance()->table('faq');
            $rs = $faqTable->update(
                array('faq_position' => $faqTable->expr('faq_position - ?', 1))
            )->where('faq_position', $rid['faq_position'], '>')->exec();
            if (Jaws_Error::IsError($rs)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_QUESTION_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_QUESTION_NOT_DELETED'), _t('FAQ_NAME'));
            }

            $faqTable = Jaws_ORM::getInstance()->table('faq');
            $rs = $faqTable->delete()->where('id', $id)->exec();
            if (Jaws_Error::IsError($rs)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_QUESTION_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_QUESTION_NOT_DELETED'), _t('FAQ_NAME'));
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
        if (($newpos < 1) || (($direction > 0) && ($newpos > $this->GetMaxCategoryPosition()))) {
            $newpos = $oldpos;
        }

        //Start Transaction
        $GLOBALS['db']->dbc->beginTransaction();

        $faqTable = Jaws_ORM::getInstance()->table('faq');
        $faqTable->update(array('faq_position' => (int)$oldpos))->where('category', (int)$cat);
        $result = $faqTable->and()->where('faq_position', (int)$newpos)->exec();
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();

            $result->setMessage(_t('FAQ_ERROR_QUESTION_NOT_MOVED'));
            return $result;
        }

        $faqTable = Jaws_ORM::getInstance()->table('faq');
        $result = $faqTable->update(array('faq_position' => (int)$newpos))->where('id', (int)$id)->exec();
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();

            $result->setMessage(_t('FAQ_ERROR_QUESTION_NOT_MOVED'));
            return $result;
        }

        //Commit Transaction
        $GLOBALS['db']->dbc->commit();
        return true;
    }

    /**
     * Max category position
     *
     * @access  public
     * @return  int  Max position
     */
    function GetMaxCategoryPosition()
    {
        $table = Jaws_ORM::getInstance()->table('faq_category');
        $max = $table->select('max(category_position)')->fetchOne();
        if (Jaws_Error::IsError($max)) {
            $max = 0;
        }

        return $max;
    }

    /**
     * Add a category
     *
     * @access  public
     * @param   string  $category     Category name
     * @param   string  $fast_url     Fast URL
     * @param   string  $description  Category description
     * @return  mixed   True if success, Jaws_Error otherwise
     */
    function AddCategory($category, $fast_url, $description)
    {
        $fast_url = empty($fast_url) ? $category : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'faq_category');

        $params['category']             = $category;
        $params['fast_url']             = $fast_url;
        $params['description']          = $description;
        $params['category_position']    = $this->GetMaxCategoryPosition() + 1;
        $params['updatetime']           = $GLOBALS['db']->Date();

        $table = Jaws_ORM::getInstance()->table('faq_category');
        $result = $table->insert($params)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_ADDED'), _t('FAQ_NAME'));
        }

        $cid = $GLOBALS['db']->lastInsertID('faq_category', 'id');
        if (Jaws_Error::IsError($cid) || empty($cid)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_ADDED'), _t('FAQ_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_CATEGORY_ADDED'), RESPONSE_NOTICE);
        return $cid;
    }

    /**
     * Update a category
     *
     * @access  public
     * @param   int     $id           Category ID
     * @param   string  $category     Category name
     * @param   string  $fast_url     Fast URL
     * @param   string  $description  Category description
     * @return  mixed   True if category is succesfully updated, Jaws_Error if not
     */
    function UpdateCategory($id, $category, $fast_url, $description)
    {
        $fast_url = empty($fast_url) ? $category : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'faq_category', false);

        $params['category']    = $category;
        $params['fast_url']    = $fast_url;
        $params['description'] = $description;
        $params['updatetime']  = $GLOBALS['db']->Date();

        $table = Jaws_ORM::getInstance()->table('faq_category');
        $result = $table->update($params)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_UPDATED'), _t('FAQ_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_CATEGORY_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Delete category
     *
     * @access  public
     * @param   int     $id     category position
     * @return  mixed   True if success, Jaws_Error on failure
     */
    function DeleteCategory($id)
    {
        $table = Jaws_ORM::getInstance()->table('faq_category');
        $row = $table->select('category_position')->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($row)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_UPDATED'), _t('FAQ_NAME'));
        }

        if (isset($row['category_position'])) {
            $table = Jaws_ORM::getInstance()->table('faq_category');
            $result = $table->update(
                array('category_position' => $table->expr('category_position - ?', 1))
            )->where('category_position', $row['category_position'], '>')->exec();

            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_UPDATED'), _t('FAQ_NAME'));
            }

            $table = Jaws_ORM::getInstance()->table('faq_category');
            $result = $table->delete()->where('id', $id)->exec();
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_DELETED'), _t('FAQ_NAME'));
            }

            $faqTable = Jaws_ORM::getInstance()->table('faq');
            $result = $faqTable->delete()->where('category', $id)->exec();
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_DELETED'), _t('FAQ_NAME'));
            }

            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_CATEGORY_DELETED'), RESPONSE_NOTICE);
            return true;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_DOES_NOT_EXISTS'), RESPONSE_ERROR);
        return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_DOES_NOT_EXISTS'));
    }

    /**
     * Move a given category
     *
     * @access  public
     * @param   int     $cat            Category ID
     * @param   int     $old_position   Old position of category
     * @param   int     $new_position   New position of category
     * @return  mixed   True if the category was moved without problems, if not, returns Jaws_Error
     */
    function MoveCategory($cat, $old_position, $new_position)
    {
        //Start Transaction
        $GLOBALS['db']->dbc->beginTransaction();

        $table = Jaws_ORM::getInstance()->table('faq_category');
        if ((int)$old_position > (int)$new_position) {
            $result = $table->update(
                array('category_position' => $table->expr('category_position + ?', 1))
            )->where('category_position', array((int)$new_position, (int)$old_position), 'between')->exec();
        } else {
            $result = $table->update(
                array('category_position' => $table->expr('category_position - ?', 1))
            )->where('category_position', array((int)$old_position, (int)$new_position), 'between')->exec();
        }
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();

            $result->setMessage(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'));
            return $result;
        }

        $table = Jaws_ORM::getInstance()->table('faq_category');
        $result = $table->update(array('category_position' => (int)$new_position))->where('id', (int)$cat)->exec();
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();

            $result->setMessage(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'));
            return $result;
        }

        //Commit Transaction
        $GLOBALS['db']->dbc->commit();
        return true;
    }

}