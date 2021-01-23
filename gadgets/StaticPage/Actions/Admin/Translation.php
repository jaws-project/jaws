<?php
/**
 * StaticPage Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    StaticPage
 * @author     Jon Wood <jon@jellybob.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class StaticPage_Actions_Admin_Translation extends StaticPage_Actions_Admin_Default
{
    /**
     * Builds the form to create a new translation
     *
     * @access  public
     * @return  string  XHTML content
     */
    function AddNewTranslation()
    {
        $this->gadget->CheckPermission('AddPage');

        $model = $this->gadget->model->load('Page');
        //Get Id
        $page_id = (int)$this->gadget->request->fetch('page', 'get');
        $page = $model->GetPage($page_id);
        if (Jaws_Error::IsError($page)) {
            $this->gadget->session->push($page->GetMessage(), RESPONSE_ERROR);
            return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=StaticPage');
        }
        return $this->CreateForm($page['title'], '', '', '', '', $page['content'], true, true, '', $page_id, '',
            'AddTranslation', 'translation');
    }

    /**
     * Adds a new page translation
     *
     * @access  public
     * @return  void
     */
    function AddTranslation()
    {
        $this->gadget->CheckPermission('EditPage');
        $model = $this->gadget->model->loadAdmin('Translation');
        $fetch   = array('page', 'title', 'content', 'language', 'meta_keys', 'meta_desc', 'tags', 'published');
        $post    = $this->gadget->request->fetch($fetch, 'post');
        $post['content'] = $this->gadget->request->fetch('content', 'post', 'strip_crlf');
        $page    = (int)$post['page'];

        $result = $model->AddTranslation($page, $post['title'], $post['content'], $post['language'],
            $post['meta_keys'], $post['meta_desc'], $post['tags'], $post['published']);
        if (Jaws_Error::isError($result)) {
            return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=StaticPage');
        } else {
            $translation = $model->GetPageTranslationByPage($page, $post['language']);
            if (Jaws_Error::isError($translation)) {
                return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=StaticPage');
            } else {
                return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=StaticPage&reqAction=EditTranslation&id=' .
                    $translation['translation_id']);
            }
        }
    }

    /**
     * Builds the form to edit a translation
     *
     * @access  public
     * @return  string  XHTML content
     */
    function EditTranslation()
    {
        $this->gadget->CheckPermission('AddPage');

        $model = $this->gadget->model->load('Translation');
        //Get Id
        $trans_id = (int)$this->gadget->request->fetch('id', 'get');
        $translation = $model->GetPageTranslation($trans_id);
        if (Jaws_Error::IsError($translation)) {
            $this->gadget->session->push($translation->GetMessage(), RESPONSE_ERROR);
            return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=StaticPage');
        }
        return $this->CreateForm($translation['title'], '', $translation['meta_keywords'], $translation['meta_description'],
            $translation['tags'], $translation['content'], $translation['published'], true,
            $translation['language'], $trans_id, '', 'SaveEditTranslation', 'translation');
    }

    /**
     * Updates a translation
     *
     * @access  public
     * @return  void
     */
    function SaveEditTranslation()
    {
        $this->gadget->CheckPermission('EditPage');
        $model = $this->gadget->model->loadAdmin('Translation');
        $fetch   = array('trans_id', 'title', 'language', 'meta_keys', 'meta_desc', 'tags', 'published');
        $post    = $this->gadget->request->fetch($fetch, 'post');
        $post['content'] = $this->gadget->request->fetch('content', 'post', 'strip_crlf');
        $trans   = (int)$post['trans_id'];
        $result = $model->UpdateTranslation($trans, $post['title'], $post['content'], $post['language'],
            $post['meta_keys'], $post['meta_desc'], $post['tags'], $post['published']);

        return Jaws_Header::Location(BASE_SCRIPT . '?reqGadget=StaticPage&reqAction=EditTranslation&id=' . $trans);
    }


}