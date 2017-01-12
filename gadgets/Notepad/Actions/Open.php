<?php
/**
 * Notepad Gadget
 *
 * @category    Gadget
 * @package     Notepad
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
$GLOBALS['app']->Layout->addLink('gadgets/Notepad/Resources/site_style.css');
class Notepad_Actions_Open extends Jaws_Gadget_Action
{
    /**
     * Builds UI to display a single note
     *
     * @access  public
     * @return  string  XHTML UI
     */
    function OpenNote()
    {

        $id = (int)jaws()->request->fetch('id', 'get');
        $model = $this->gadget->model->load('Notepad');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $note = $model->GetNote($id, $user);
        if (Jaws_Error::IsError($note) || empty($note)) {
            return;
        }

        $this->AjaxMe('site_script.js');
        $tpl = $this->gadget->template->load('Open.html');
        $tpl->SetBlock('note');
        $tpl->SetVariable('id', $id);
        $tpl->SetVariable('note_title', $note['title']);
        $tpl->SetVariable('note_content', $this->gadget->ParseText($note['content'], 'Notepad'));

        // Actions
        if ($note['user'] == $user) {
            $tpl->SetBlock('note/actions');
            $tpl->SetVariable('lbl_edit', _t('GLOBAL_EDIT'));
            $tpl->SetVariable('lbl_share', _t('NOTEPAD_SHARE'));
            $tpl->SetVariable('lbl_delete', _t('GLOBAL_DELETE'));
            $tpl->SetVariable('confirmDelete', _t('NOTEPAD_WARNING_DELETE_NOTE'));
            $tpl->SetVariable('notepad_url', $this->gadget->urlMap('Notepad'));
            $tpl->SetVariable('url_edit', $this->gadget->urlMap('EditNote', array('id' => $id)));
            $tpl->SetVariable('url_share', $this->gadget->urlMap('ShareNote', array('id' => $id)));
            $tpl->ParseBlock('note/actions');
        }

        $tpl->ParseBlock('note');
        return $tpl->Get();
    }
}