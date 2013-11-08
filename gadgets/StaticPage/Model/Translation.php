<?php
/**
 * StaticPage Gadget
 *
 * @category   GadgetModel
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Model_Translation extends Jaws_Gadget_Model
{

    /**
     * Gets the translation(by translation ID) of a page
     *
     * @access  public
     * @param   int     $id  Translation ID
     * @return  mixed   Array translation information or Jaws_Error on failure
     */
    function GetPageTranslation($id)
    {
        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $sptTable->select(
            'translation_id:integer', 'base_id:integer', 'title', 'content', 'language',
            'meta_keywords', 'meta_description', 'user:integer', 'published:boolean', 'updated'
        )->where('translation_id', $id);

        $row = $sptTable->fetchRow();
        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
        }

        if (isset($row['translation_id'])) {
            if (!empty($row)) {
                if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
                    $model = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
                    $tags = $model->GetItemTags(
                        array('gadget' => 'StaticPage', 'action' => 'page', 'reference' => $row['translation_id']),
                        true);
                    $row['tags'] = implode(',', array_filter($tags));
                }
            }
            return $row;
        }

        return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
    }


    /**
     * Gets the translation by page ID and language code
     *
     * @access  public
     * @param   int     $page_id    ID of the page we are translating
     * @param   string  $language   The language we are using
     * @return  mixed   Array of translation information or Jaws_Error on failure
     */
    function GetPageTranslationByPage($page_id, $language)
    {
        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $row = $sptTable->select(
            'translation_id:integer', 'base_id:integer', 'title', 'content', 'language',
            'user:integer', 'published:boolean', 'updated'
        )->where('base_id', $page_id)->and()->where('language', $language)->fetchRow();

        if (Jaws_Error::IsError($row)) {
            return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
        }

        if (isset($row['translation_id'])) {
            return $row;
        }

        return new Jaws_Error(_t('STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS'), _t('STATICPAGE_NAME'));
    }

    /**
     * Returns all available languages a page has been translated to
     *
     * @access  public
     * @param   int     $page           Page ID
     * @param   bool    $onlyPublished  Publish status of the page
     * @return  mixed   Array of language codes / False if no code are found
     */
    function GetTranslationsOfPage($page, $onlyPublished = false)
    {
        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $sptTable->select('translation_id:integer', 'language')->where('base_id', $page);

        if ($onlyPublished) {
            $sptTable->and()->where('published', true);
        }

        $result = $sptTable->fetchAll();
        if (Jaws_Error::isError($result)) {
            return false;
        }

        return (count($result) > 0) ? $result : false;
    }


    /**
     * Checks for existance of a page translation
     *
     * @access  public
     * @param   mixed   $page_id    ID or fast_url of the page (int/string)
     * @param   string  $language   The translation we are looking for
     * @return  bool    True if exists and false if not
     */
    function TranslationExists($page_id, $language)
    {
        $spTable = Jaws_ORM::getInstance()->table('static_pages_translation as spt');
        $spTable->select('count(translation_id) as total');
        $spTable->join('static_pages as sp',  'sp.page_id',  'spt.base_id');

        if (is_numeric($page_id)) {
            $spTable->where('sp.page_id', $page_id);
        } else {
            $spTable->where('sp.fast_url', $page_id);
        }
        $total = $spTable->and()->where('spt.language', $language)->fetchOne();
        return ($total == '0') ? false : true;
    }


}