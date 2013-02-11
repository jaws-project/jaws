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
        $sql = 'SELECT MAX([faq_position]) FROM [[faq]] WHERE [category] = {category}';
        $max = $GLOBALS['db']->queryOne($sql, array('category' => $category));
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

        $params = array();
        $params['question'] = $question;
        $params['fast_url'] = $fast_url;
        $params['answer']   = $answer;
        $params['category'] = $category;
        $params['active']   = $active;
        $params['position'] = $this->GetMaxQuestionPosition($category) + 1;
        $params['now']      = $GLOBALS['db']->Date();

        $sql = '
            INSERT INTO [[faq]]
                ([question], [fast_url], [answer], [category], [published], [faq_position], [createtime], [updatetime])
            VALUES
                ({question}, {fast_url}, {answer}, {category}, {active}, {position}, {now}, {now})';

        $result = $GLOBALS['db']->query($sql, $params);
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

        $params = array();
        $params['id']       = $id;
        $params['question'] = $question;
        $params['fast_url'] = $fast_url;
        $params['answer']   = $answer;
        $params['category'] = $category;
        $params['active']   = $active;
        $params['now']      = $GLOBALS['db']->Date();

        $sql = '
            UPDATE [[faq]] SET
                [question]   = {question},
                [fast_url]   = {fast_url},
                [answer]     = {answer},
                [category]   = {category},
                [published]  = {active},
                [updatetime] = {now}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
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
        $sql = 'SELECT [faq_position] FROM [[faq]] WHERE [id] = {id}';
        $rid = $GLOBALS['db']->queryRow($sql, array('id' => $id));
        if (Jaws_Error::IsError($rid)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_QUESTION_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FAQ_ERROR_QUESTION_NOT_DELETED'), _t('FAQ_NAME'));
        }

        if (isset($rid['faq_position'])) {
            $sql = '
                UPDATE [[faq]] SET
                    [faq_position] = [faq_position] - 1
                WHERE [faq_position] > {pos}';

            $rs = $GLOBALS['db']->query($sql, array('pos' => $rid['faq_position']));
            if (Jaws_Error::IsError($rs)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_QUESTION_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_QUESTION_NOT_DELETED'), _t('FAQ_NAME'));
            }

            $sql = 'DELETE FROM [[faq]] WHERE [id] = {id}';
            $rs = $GLOBALS['db']->query($sql, array('id' => $id));
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

        $params = array();
        $params['category'] = (int)$cat;
        $params['question'] = (int)$id;
        $params['oldpos']   = (int)$oldpos;
        $params['newpos']   = (int)$newpos;

        //Start Transaction
        $GLOBALS['db']->dbc->beginTransaction();

        $sql = '
            UPDATE [[faq]] SET
                [faq_position] = {oldpos}
            WHERE
                [category] = {category}
              AND
                [faq_position] = {newpos}
            ';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();

            $result->setMessage(_t('FAQ_ERROR_QUESTION_NOT_MOVED'));
            return $result;
        }

        $sql = '
            UPDATE [[faq]] SET
                [faq_position] = {newpos}
            WHERE
                [id] = {question}';

        $result = $GLOBALS['db']->query($sql, $params);
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
        $sql = 'SELECT MAX([category_position]) FROM [[faq_category]]';
        $max = $GLOBALS['db']->queryOne($sql);
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

        $params = array();
        $params['category']    = $category;
        $params['fast_url']    = $fast_url;
        $params['description'] = $description;
        $params['position']    = $this->GetMaxCategoryPosition() + 1;
        $params['updatetime']  = $GLOBALS['db']->Date();

        $sql = '
            INSERT INTO [[faq_category]]
                ([category], [fast_url], [description], [category_position], [updatetime])
            VALUES
                ({category}, {fast_url}, {description}, {position}, {updatetime})';

        $result = $GLOBALS['db']->query($sql, $params);
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

        $params = array();
        $params['id'] = $id;
        $params['category']    = $category;
        $params['fast_url']    = $fast_url;
        $params['description'] = $description;
        $params['updatetime']  = $GLOBALS['db']->Date();

        $sql = '
            UPDATE [[faq_category]] SET
                [category] = {category},
                [fast_url] = {fast_url},
                [description] = {description},
                [updatetime] = {updatetime}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
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
        $sql = 'SELECT [category_position] FROM [[faq_category]] WHERE [id] = {id}';
        $row = $GLOBALS['db']->queryRow($sql, array('id' => $id));
        if (Jaws_Error::IsError($row)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_UPDATED'), _t('FAQ_NAME'));
        }

        if (isset($row['category_position'])) {
            $sql = '
                UPDATE [[faq_category]] SET
                    [category_position] = [category_position] - 1
                WHERE [category_position] > {pos}';

            $result = $GLOBALS['db']->query($sql, array('pos' => $row['category_position']));
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_UPDATED'), _t('FAQ_NAME'));
            }

            $sql = 'DELETE FROM [[faq_category]] WHERE [id] = {id}';
            $result = $GLOBALS['db']->query($sql, array('id' => $id));
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_DELETED'), _t('FAQ_NAME'));
            }

            $sql = 'DELETE FROM [[faq]] WHERE [category] = {id}';
            $result = $GLOBALS['db']->query($sql, array('id' => $id));
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
        $params = array();
        $params['id']     = (int)$cat;
        $params['one']    = 1;
        $params['oldpos'] = (int)$old_position;
        $params['newpos'] = (int)$new_position;

        //Start Transaction
        $GLOBALS['db']->dbc->beginTransaction();

        if ($params['oldpos'] > $params['newpos']) {
            $sql = '
                UPDATE [[faq_category]] SET
                    [category_position] = [category_position] + {one}
                WHERE
                    [category_position] BETWEEN {newpos} AND {oldpos}
                ';
        } else {
            $sql = '
                UPDATE [[faq_category]] SET
                    [category_position] = [category_position] - {one}
                WHERE
                    [category_position] BETWEEN {oldpos} AND {newpos}
                ';
        }

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            //Rollback Transaction
            $GLOBALS['db']->dbc->rollback();

            $result->setMessage(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'));
            return $result;
        }

        $sql = '
            UPDATE [[faq_category]] SET
                [category_position] = {newpos}
            WHERE
                [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
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