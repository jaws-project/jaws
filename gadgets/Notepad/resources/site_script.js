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
    Delete: function(response) {
        if (response.type === 'response_notice') {
            updateNotes();
        }
        NotepadAjax.showResponse(response);
    }
};

/**
 * Initiates Notepad
 */
function initNotepad()
{
    NotepadAjax.backwardSupport();
    noteTemplate = $('note_template').get('html');
    updateNotes();
    //console.log(noteTemplate);
}

/**
 * Feches and displays notes
 */
function updateNotes()
{
    var notes = NotepadAjax.callSync('GetNotes');
    displayNotes(notes);
}

/**
 * Displays files and directories
 */
function displayNotes(notes)
{
    // Creates a datagrid row from raw data
    function getNoteRow(data)
    {
        var html = noteTemplate.substitute(data),
            tr = Elements.from(html)[0];
        //tr.getElement('input').addEvent('click', fileCheck);
        return tr;
    }

    var ws = $('note_template').empty();
    notes.each(function (note) {
        ws.grab(getNoteRow(note));
    });
}

var NotepadAjax = new JawsAjax('Notepad', NotepadCallback),
    noteTemplate;
