<?php
/**
 * Faq Admin Gadget
 *
 * @category   GadgetModel
 * @package    Faq
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Faq_Model_Admin_Category extends Faq_Model_Category
{
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
            return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_ADDED'));
        }

        $cid = $GLOBALS['db']->lastInsertID('faq_category', 'id');
        if (Jaws_Error::IsError($cid) || empty($cid)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_ADDED'));
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
            return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_UPDATED'));
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
            return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_UPDATED'));
        }

        if (isset($row['category_position'])) {
            $table = Jaws_ORM::getInstance()->table('faq_category');
            $result = $table->update(
                array('category_position' => $table->expr('category_position - ?', 1))
            )->where('category_position', $row['category_position'], '>')->exec();

            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_UPDATED'));
            }

            $table = Jaws_ORM::getInstance()->table('faq_category');
            $result = $table->delete()->where('id', $id)->exec();
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_DELETED'));
            }

            $faqTable = Jaws_ORM::getInstance()->table('faq');
            $result = $faqTable->delete()->where('category', $id)->exec();
            if (Jaws_Error::IsError($result)) {
                $GLOBALS['app']->Session->PushLastResponse(_t('FAQ_ERROR_CATEGORY_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error(_t('FAQ_ERROR_CATEGORY_NOT_DELETED'));
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

        $table = Jaws_ORM::getInstance()->table('faq_category');
        //Start Transaction
        $table->beginTransaction();

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
            $result->setMessage(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'));
            return $result;
        }

        $table = Jaws_ORM::getInstance()->table('faq_category');
        $result = $table->update(array('category_position' => (int)$new_position))->where('id', (int)$cat)->exec();
        if (Jaws_Error::IsError($result)) {
            $result->setMessage(_t('FAQ_ERROR_CATEGORY_NOT_MOVED'));
            return $result;
        }

        //Commit Transaction
        $table->commit();
        return true;
    }
}