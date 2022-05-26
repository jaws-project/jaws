<?php
/**
 * StaticPage Gadget
 *
 * @category   GadgetModel
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Model_Admin_Translation extends StaticPage_Model_Translation
{
    /**
     * Creates a translation of the given page
     *
     * @access  public
     * @param   mixed   $page_id    ID or fast_url of the page (int/string)
     * @param   string  $title      The translated page title
     * @param   string  $content    The translated page content
     * @param   string  $language   The language we are using
     * @param   string  $meta_keys  Meta keywords
     * @param   string  $meta_desc  Meta description
     * @param   string  $tags       Tags
     * @param   bool    $published  Publish status of the page
     * @return  mixed   Translation Id or Jaws_Error on failure
     */
    function AddTranslation($page_id, $title, $content, $language, $meta_keys, $meta_desc, $tags, $published)
    {
        // Language exists?
        $language = str_replace(array('.', '/'), '', $language);
        if ($language != 'en' && !file_exists(ROOT_JAWS_PATH . "languages/$language/FullName")) {
            $this->gadget->session->push($this::t('ERROR_LANGUAGE_NOT_EXISTS', $language), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_LANGUAGE_NOT_EXISTS', $language));
        }

        if ($this->TranslationExists($page_id, $language)) {
            $this->gadget->session->push($this::t('ERROR_TRANSLATION_EXISTS', $language), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_TRANSLATION_EXISTS', $language));
        }
        $published = $this->gadget->GetPermission('PublishPages')? $published : false;

        $params['base_id'] = $page_id;
        $params['title'] = $title;
        $params['content'] = $content;
        $params['language'] = $language;
        $params['user'] = $this->app->session->user->id;
        $params['meta_keywords'] = $meta_keys;
        $params['meta_description'] = $meta_desc;
        $params['published'] = (bool)$published;
        $params['updated'] = Jaws_DB::getInstance()->date();

        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation', '', 'translation_id');
        $tid = $sptTable->insert($params)->exec();
        if (Jaws_Error::IsError($tid)) {
            $this->gadget->session->push($this::t('ERROR_TRANSLATION_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_TRANSLATION_NOT_ADDED'));
        }

        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $model = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
            $res = $model->InsertReferenceTags('StaticPage', 'page', $tid, (bool)$published, null, $tags);
            if (Jaws_Error::IsError($res)) {
                $this->gadget->session->push($this::t('ERROR_TAG_NOT_ADDED'), RESPONSE_ERROR);
                return new Jaws_Error($this::t('ERROR_TAG_NOT_ADDED'));
            }
        }

        $this->gadget->session->push($this::t('TRANSLATION_CREATED'), RESPONSE_NOTICE);
        return $tid;
    }

    /**
     * Updates a translation
     *
     * @access  public
     * @param   int     $id         Translation ID
     * @param   string  $title      The translated page title
     * @param   string  $content    The translated page content
     * @param   string  $language   The language we are using
     * @param   string  $meta_keys  Meta keywords
     * @param   string  $meta_desc  Meta description
     * @param   string  $tags       Tags
     * @param   bool    $published  Publish status of the page
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function UpdateTranslation($id, $title, $content, $language, $meta_keys, $meta_desc, $tags, $published)
    {
        //Language exists?
        $language = str_replace(array('.', '/'), '', $language);
        //Original language?
        $translation = $this->GetPageTranslation($id);
        if (Jaws_Error::isError($translation)) {
            $this->gadget->session->push($this::t('ERROR_TRANSLATION_NOT_EXISTS'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_TRANSLATION_NOT_EXISTS'));
        }

        if ($translation['language'] != $language) {
            if ($this->TranslationExists($translation['base_id'], $language)) {
                $this->gadget->session->push($this::t('ERROR_TRANSLATION_EXISTS'), RESPONSE_ERROR);
                return new Jaws_Error($this::t('ERROR_TRANSLATION_EXISTS'));
            }
        }

        // check modify other's pages ACL
        if (!$this->gadget->GetPermission('ModifyOthersPages') &&
            ($this->app->session->user->id != $translation['user']))
        {
            // FIXME: need new language statement
            $this->gadget->session->push($this::t('ERROR_TRANSLATION_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_TRANSLATION_NOT_UPDATED'));
        }

        // check modify published pages ACL
        if ($translation['published'] &&
            !$this->gadget->GetPermission('ManagePublishedPages'))
        {
            // FIXME: need new language statement
            $this->gadget->session->push($this::t('ERROR_TRANSLATION_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_TRANSLATION_NOT_UPDATED'));
        }

        // Lets update it
        $params['title']            = $title;
        $params['content']          = $content;
        $params['language']         = $language;
        $params['meta_keywords']    = $meta_keys;
        $params['meta_description'] = $meta_desc;
        $params['updated']          = Jaws_DB::getInstance()->date();
        if ($this->gadget->GetPermission('PublishPages')) {
            $params['published'] = (bool)$published;
        } else {
            $params['published'] = false;
        }

        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $result = $sptTable->update($params)->where('translation_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push($this::t('ERROR_TRANSLATION_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_TRANSLATION_NOT_UPDATED'));
        }

        // Update page translation tags
        if (Jaws_Gadget::IsGadgetInstalled('Tags') && !empty($tags)) {
            $model = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
            $res = $model->UpdateReferenceTags('StaticPage', 'page', $id, $params['published'], time(), $tags);
            if (Jaws_Error::IsError($res)) {
                $this->gadget->session->push($this::t('ERROR_TAG_NOT_UPDATED'), RESPONSE_ERROR);
            }
        }

        $this->gadget->session->push($this::t('TRANSLATION_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes the translation
     *
     * @access  public
     * @param   int     $id Translation ID
     * @return  mixed   True on success or Jaws_Error on failure
     */
    function DeleteTranslation($id)
    {
        $params = array();
        $params['id'] = $id;

        if (!$this->gadget->GetPermission('ModifyOthersPages')) {
            $translation = $this->GetPageTranslation($id);
            if (Jaws_Error::isError($translation)) {
                $this->gadget->session->push($this::t('ERROR_TRANSLATION_NOT_EXISTS'), RESPONSE_ERROR);
                return new Jaws_Error($this::t('ERROR_TRANSLATION_NOT_EXISTS'));
            }

            if ($this->app->session->user->id != $translation['user']) {
                $this->gadget->session->push($this::t('ERROR_TRANSLATION_NOT_DELETED'), RESPONSE_ERROR);
                return new Jaws_Error($this::t('ERROR_TRANSLATION_NOT_DELETED'));
            }
        }

        $sptTable = Jaws_ORM::getInstance()->table('static_pages_translation');
        $result = $sptTable->delete()->where('translation_id', $id)->exec();
        if (Jaws_Error::IsError($result)) {
            $this->gadget->session->push($this::t('ERROR_TRANSLATION_NOT_DELETED'), RESPONSE_ERROR);
            return new Jaws_Error($this::t('ERROR_TRANSLATION_NOT_DELETED'));
        }

        if (Jaws_Gadget::IsGadgetInstalled('Tags')) {
            $model = Jaws_Gadget::getInstance('Tags')->model->loadAdmin('Tags');
            $res = $model->DeleteReferenceTags('StaticPage', 'page', $id);
            if (Jaws_Error::IsError($res)) {
                $this->gadget->session->push($this::t('ERROR_TAG_NOT_DELETED'), RESPONSE_ERROR);
                return $res;
            }
        }

        $this->gadget->session->push($this::t('TRANSLATION_DELETED'), RESPONSE_NOTICE);
        return true;
    }

}