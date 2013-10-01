/**
 * Notepad Javascript actions
 *
 * @category    Ajax
 * @package     Notepad
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var NotepadCallback = {
    DeleteNote: function(response) {
        if (response.type === 'response_error') {
            NotepadAjax.showResponse(response);
        } else {
            window.location = notepad_url;
        }
    }
};

/**
 * Initiates Notepad
 */
function initNotepad()
{
    NotepadAjax.backwardSupport();
    //console.log(noteTemplate);
}

/**
 * Submits form
 */
function submitNote()
{
    var form = $('frm_note');
    if (form.title.value === '') {
        alert(errorIncompleteData);
        form.title.focus();
        return;
    }
    if (form.content.value === '') {
        alert(errorIncompleteData);
        form.content.focus();
        return;
    }
    form.submit();
}

/**
 * Deletes note
 */
function deleteNote(id)
{
    if (confirm(confirmDelete)) {
        NotepadAjax.callAsync('DeleteNote', {id_set:id});
    }
}

var NotepadAjax = new JawsAjax('Notepad', NotepadCallback);
