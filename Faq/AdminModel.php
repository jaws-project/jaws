<?php
/**
 * Faq Admin Gadget
 *
 * @category   GadgetModel
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
require_once JAWS_PATH . 'gadgets/Faq/Model.php';
class FaqAdminModel extends FaqModel
{
    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function InstallGadget()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $variables = array();
        $variables['timestamp'] = $GLOBALS['db']->Date();

        $result = $this->installSchema('insert.xml', $variables, 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function UninstallGadget()
    {
        $tables = array('faq',
                        'faq_category');
        foreach ($tables as $table) {
            $result = $GLOBALS['db']->dropTable($table);
            if (Jaws_Error::IsError($result)) {
                $gName  = _t('FAQ_NAME');
                $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
                $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
                return new Jaws_Error($errMsg, $gName);
            }
        }

        return true;
    }

    /**
     * Update the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function UpdateGadget($old, $new)
    {
        if (version_compare($old, '0.8.0', '<')) {
            $result = $this->installSchema('0.8.0.xml', '', "0.7.0.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        if (version_compare($old, '0.8.1', '<')) {
            $result = $this->installSchema('0.8.1.xml', '', "0.8.0.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        $result = $this->installSchema('schema.xml', '', "0.8.1.xml");
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        // Registry keys.

        return true;
    }

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

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');

        $params = array();
        $params['question'] = $xss->parse($question);
        $params['fast_url'] = $xss->parse($fast_url);
        $params['answer']   = $xss->parse($answer, false);
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

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');

        $params = array();
        $params['id']       = $id;
        $params['question'] = $xss->parse($question);
        $params['fast_url'] = $xss->parse($fast_url);
        $params['answer']   = $xss->parse($answer, false);
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
     * @param   string  $direction  Where to move it
     * @param   int     $id         Question id
     * @return  mixed   Returns true if the question was moved without problems, if not, returns Jaws_Error
     */
    function MoveQuestion($direction, $id)
    {
        $item = $this->getQuestion($id);
        $sql = '
            SELECT
                [id], [faq_position]
            FROM [[faq]]
            WHERE [category] = {category}
            ORDER BY [faq_position] ASC';

        $rs = $GLOBALS['db']->queryAll($sql, array('category' => $item['category_id']));
        if (Jaws_Error::IsError($rs)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_QUESTION_NOT_MOVED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FAQ_ERROR_QUESTION_NOT_MOVED'), _t('FAQ_NAME'));
        }

        $qarray = array();
        foreach($rs as $row) {
            $res['id'] = $row['id'];
            $res['position'] = $row['faq_position'];
            $qarray[$row['id']] = $res;
        }
        reset($qarray);

        if (!is_array($qarray) || (is_array($qarray) && count($qarray) == 0)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_QUESTION_NOT_MOVED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FAQ_ERROR_QUESTION_NOT_MOVED'), _t('FAQ_NAME'));
        }

        $found = false;
        while (!$found) {
            $v = current($qarray);
            if ($v['id'] == $id) {
                $found = true;
                $position = $v['position'];
                $id = $v['id'];
            } else {
                next($qarray);
            }
        }
        $run_queries = false;

        if ($direction == 'UP' && prev($qarray)) {
            $v = current($qarray);
            $m_position = $v['position'];
            $m_id = $v['id'];
            $run_queries = true;
        }

        if ($direction == 'DOWN' && next($qarray)) {
            $v = current($qarray);
            $m_position = $v['position'];
            $m_id = $v['id'];
            $run_queries = true;
        }

        if ($run_queries) {
            $params = array();
            $params['position'] = $m_position;
            $params['id'] = $id;

            $sql = '
                UPDATE [[faq]] SET
                    [faq_position] = {position}
                WHERE [id] = {id}';

            $result = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_QUESTION_NOT_MOVED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_QUESTION_NOT_MOVED'), _t('FAQ_NAME'));
            }

            $params = array();
            $params['position'] = $position;
            $params['id'] = $m_id;

            $sql = '
                UPDATE [[faq]] SET
                    [faq_position] = {position}
                WHERE [id] = {id}';

            $result = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_QUESTION_NOT_MOVED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_QUESTION_NOT_MOVED'), _t('FAQ_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_QUESTION_MOVED'), RESPONSE_NOTICE);
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
     * Fix the position of a category
     *
     * @access  public
     * @param   int     $cat  Category ID
     * @param   int     $pos  New position
     * @return  mixed   True if the category was moved without problems, if not, returns Jaws_Error
     */
    function FixCategoryPosition($cat, $pos)
    {
        $params = array();
        $params['position'] = $pos;
        $params['id']       = $cat;
        $sql = '
            UPDATE [[faq_category]] SET
                [category_position] = {position}
            WHERE [id] = {id}';

        $result = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'), _t('FAQ_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_CATEGORY_MOVED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Move a given category
     *
     * @access  public
     * @param   string  $direction  Where to move it
     * @param   int     $id         category id
     * @return  mixed   True if the category was moved without problems, if not, returns Jaws_Error
     */
    function MoveCategory($direction, $id)
    {
        $sql = '
            SELECT
                [id], [category_position]
            FROM [[faq_category]]
            ORDER BY [category_position] ASC';

        $result = $GLOBALS['db']->queryAll($sql);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'), _t('FAQ_NAME'));
        }

        $qarray = array();
        foreach ($result as $row) {
            $res['id'] = $row['id'];
            $res['position'] = $row['category_position'];
            $qarray[$row['id']] = $res;
        }
        reset($qarray);

        if ((!is_array($qarray)) ||((is_array($qarray)) &&(count($qarray) == 0))) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'), _t('FAQ_NAME'));
        }

        $found = false;
        while (!$found) {
            $v = current($qarray);
            if ($v['id'] == $id) {
                $found = true;
                $position = $v['position'];
                $id = $v['id'];
            } else {
                next($qarray);
            }
        }
        $run_queries = false;

        if ($direction == 'UP' && prev($qarray)) {
            $v = current($qarray);
            $m_position = $v['position'];
            $m_id = $v['id'];
            $run_queries = true;
        }

        if ($direction == 'DOWN' && next($qarray)) {
            $v = current($qarray);
            $m_position = $v['position'];
            $m_id = $v['id'];
            $run_queries = true;
        }

        if ($run_queries) {
            $params = array();
            $params['position'] = $m_position;
            $params['id']       = $id;
            $sql = '
                UPDATE [[faq_category]] SET
                    [category_position] = {position}
                WHERE [id] = {id}';

            $result = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'), _t('FAQ_NAME'));
            }

            $params = array();
            $params['position'] = $position;
            $params['id']       = $m_id;
            $sql = '
                UPDATE [[faq_category]] SET
                    [category_position] = {position}
                WHERE [id] = {id}';

            $result = $GLOBALS['db']->query($sql, $params);
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'), _t('FAQ_NAME'));
            }
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_CATEGORY_MOVED'), RESPONSE_NOTICE);
        return true;
    }

}