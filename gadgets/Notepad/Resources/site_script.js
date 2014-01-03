/**
 * Notepad Javascript actions
 *
 * @category    Ajax
 * @package     Notepad
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2014 Jaws Development Group
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
    },

    UpdateShare: function(response) {
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
}

/**
 * Selects/Deselects all rows
 */
function checkAll()
{
    var checked = $('chk_all').checked;
    $('grid_notes').getElements('input').set('checked', checked);
}

/**
 * Submits search
 */
/*function searchNotes(form)
{
    if (form.query.value.length < 2) {
        alert(errorShortQuery);
        return;
    }
    form.submit();
}*/

/**
 * Shows/Hides search reset button
 */
function onSearchChange(value)
{
    $('btn_note_search_reset').style.display = (value === '')? 'none' : 'inline';
}

/**
 * Submits note
 */
function submitNote(form)
{
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
 * Deletes current note
 */
function deleteNote(id)
{
    if (confirm(confirmDelete)) {
        NotepadAjax.callAsync('DeleteNote', {id_set:id});
    }
}

/**
 * Deletes selected notes
 */
function deleteNotes()
{
    var id_set = $('grid_notes').getElements('input:checked').get('value');
    if (id_set.length === 0) {
        return;
    }
    if (confirm(confirmDelete)) {
        NotepadAjax.callAsync('DeleteNote', {id_set:id_set.join(',')});
    }
}

/**
 * Initiates Sharing
 */
function initShare()
{
    $('sys_groups').selectedIndex = -1;
    Array.each($('note_users').options, function(opt) {
        sharedNoteUsers[opt.value] = opt.text;
    });
}

/**
 * Fetches and displays users of selected group
 */
function toggleUsers(gid)
{
    var container = $('sys_users').empty(),
        users = usersByGroup[gid];
    if (users === undefined) {
        users = NotepadAjax.callSync('GetUsers', {'gid':gid});
        usersByGroup[gid] = users;
    }
    users.each(function (user) {
        if (user.id == UID) return;
        var div = new Element('div'),
            input = new Element('input', {type:'checkbox', id:'chk_'+user.id, value:user.id}),
            label = new Element('label', {'for':'chk_'+user.id});
        input.set('checked', (sharedNoteUsers[user.id] !== undefined));
        input.addEvent('click', selectUser);
        label.set('html', user.nickname + ' (' + user.username + ')');
        div.adopt(input, label);
        container.grab(div);
    });
}

/**
 * Adds/removes user to/from shares
 */
function selectUser()
{
    if (this.checked) {
        sharedNoteUsers[this.value] = this.getNext('label').get('html');
    } else {
        delete sharedNoteUsers[this.value];
    }
    updateShareUsers();
}

/**
 * Updates list of note users
 */
function updateShareUsers()
{
    var list = $('note_users').empty();
    Object.each(sharedNoteUsers, function(name, id) {
        list.options[list.options.length] = new Option(name, id);
    });
}

/**
 * Submits share data
 */
function submitShare(id)
{
    NotepadAjax.callAsync(
        'UpdateShare',
        {'id':id, 'users':Object.keys(sharedNoteUsers).join(',')}
    );
}

var NotepadAjax = new JawsAjax('Notepad', NotepadCallback),
    usersByGroup = {},
    sharedNoteUsers = {};
