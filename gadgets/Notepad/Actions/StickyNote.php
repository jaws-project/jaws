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
class Notepad_Actions_StickyNote extends Jaws_Gadget_Action
{
    /**
     * Builds layout params of the StickyNote action
     *
     * @access  public
     * @return  array   Layout parameters
     */
    function StickyNoteLayoutParams()
    {
        $types = array();
        $types[1] = _t('NOTEPAD_LAST_NOTE');
        $types[5] = _t('NOTEPAD_LATEST_5_NOTES');
        $types[10] = _t('NOTEPAD_LATEST_10_NOTES');
        $result[] = array(
            'title' => _t('NOTEPAD_STICKYNOTE_DISPLAY'),
            'value' => $types
        );

        return $result;
    }

    /**
     * Displays last note or latest notes
     *
     * @access  public
     * @param   int     $count  Number of notes to be displayed   
     * @return  string  XHTML UI
     */
    function StickyNote($count)
    {
        $GLOBALS['app']->Layout->addLink('gadgets/Notepad/Resources/site_style.css');
        $model = $this->gadget->model->load('StickyNote');
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $notes = $model->GetLatestNotes($user, $count);
        if (Jaws_Error::IsError($notes) || empty($notes)) {
            return;
        }

        if ($count == 1) {
            $action = $this->gadget->action->load('View');
            $view = $action->ViewNote($notes[0]['id']);
        } else {
            $view = $this->GetNotesView($notes);
        }

        return $view;
    }

    /**
     * Builds a list view of passed notes
     *
     * @access  public
     * @param   array   $notes  List of notes to be displayed
     * @return  string  XHTML view
     */
    function GetNotesView($notes)
    {
        $tpl = $this->gadget->template->load('StickyNote.html');
        $tpl->SetBlock('notes');

        $tpl->SetVariable('title', _t('NOTEPAD_LATEST_NOTES'));
        $objDate = Jaws_Date::getInstance();
        foreach ($notes as $note) {
            $tpl->SetBlock('notes/note');
            $tpl->SetVariable('note_title', $note['title']);
            $tpl->SetVariable('note_content', $this->gadget->ParseText($note['content'], 'Notepad'));
            $tpl->SetVariable('created', $objDate->Format($note['createtime'], 'n/j/Y g:i a'));
            $tpl->SetVariable('note_url', $this->gadget->urlMap('ViewNote', array('id' => $note['id'])));
            $tpl->ParseBlock('notes/note');
        }

        $tpl->ParseBlock('notes');
        return $tpl->Get();
    }
}