<?php
/**
 * Glossary Gadget Admin
 *
 * @category   GadgetModel
 * @package    Glossary
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Glossary_Model_Admin_Term extends Jaws_Gadget_Model
{
    /**
     * Deletes a term
     *
     * @acess   public
     * @param   int     $id  Term ID
     * @return  mixed   Returns true if term was deleted or Jaws_Error on error
     */
    function DeleteTerm($id)
    {
        $glossaryTable = Jaws_ORM::getInstance()->table('glossary');
        $result = $glossaryTable->delete()->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOSSARY_ERROR_TERM_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('GLOSSARY_ERROR_TERM_NOT_DELETED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('GLOSSARY_TERM_DELETED'), RESPONSE_NOTICE);
        return true;
    }


    /**
     * Updates a term
     *
     * @acess   public
     * @param   int     $id         Term's ID
     * @param   string  $term       Term
     * @param   string  $fast_url
     * @param   string  $desc       Term's description
     * @return  mixed   Returns true if term was deleted or Jaws_Error on error
     */
    function UpdateTerm($id, $term, $fast_url, $desc)
    {
        $fast_url = empty($fast_url) ? $term : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'glossary', false);

        $params['term']         = $term;
        $params['fast_url']     = $fast_url;
        $params['description']  = $desc;
        $params['updatetime']   = Jaws_DB::getInstance()->date();

        $glossaryTable = Jaws_ORM::getInstance()->table('glossary');
        $result = $glossaryTable->update($params)->where('id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOSSARY_ERROR_TERM_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('GLOSSARY_ERROR_TERM_NOT_UPDATED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('GLOSSARY_TERM_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Adds a new term
     *
     * @acess   public
     * @param   string  $term       Term
     * @param   string  $fast_url
     * @param   string  $desc       Term's description
     * @return  mixed   Returns true if term was added or Jaws_Error on error
     */
    function NewTerm($term, $fast_url, $desc)
    {
        $fast_url = empty($fast_url) ? $term : $fast_url;
        $fast_url = $this->GetRealFastUrl($fast_url, 'glossary');

        $now = Jaws_DB::getInstance()->date();
        $params['term']         = $term;
        $params['fast_url']     = $fast_url;
        $params['description']  = $desc;
        $params['createtime']   = $now;
        $params['updatetime']   = $now;

        $glossaryTable = Jaws_ORM::getInstance()->table('glossary');
        $result = $glossaryTable->insert($params)->exec();
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOSSARY_ERROR_TERM_NOT_CREATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('GLOSSARY_ERROR_TERM_NOT_CREATED'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('GLOSSARY_TERM_ADDED'), RESPONSE_NOTICE);

        $glossaryTable = Jaws_ORM::getInstance()->table('glossary');
        $row = $glossaryTable->select('id:integer')->where('createtime', $now)->fetchRow();
        if (Jaws_Error::IsError($row)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOSSARY_ERROR_TERM_NOT_CREATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('GLOSSARY_ERROR_TERM_NOT_CREATED'));
        }

        if (isset($row['id'])) {
            return $row['id'];
        }

        return false;
    }
}