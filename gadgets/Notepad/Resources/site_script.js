/**
 * Notepad Javascript actions
 *
 * @category    Ajax
 * @package     Notepad
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
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
    var checked = $('#chk_all').prop('checked');
    $('#grid_notes').find('input').prop('checked', checked);
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
    $('#btn_note_search_reset')[0].style.display = (value === '')? 'none' : 'inline';
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
    var id_set = $('#grid_notes').find('input:checked').map(function () {
        return this.value;
    });
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
    $('#sys_groups').prop('selectedIndex', -1);
    $.each($('#note_users')[0].options, function(opt) {
        sharedNoteUsers[opt.value] = opt.text;
    });
}

/**
 * Fetches and displays users of selected group
 */
function toggleUsers(gid)
{
    var container = $('#sys_users').empty(),
        users = usersByGroup[gid];
    if (users === undefined) {
        users = NotepadAjax.callSync('GetUsers', {'gid':gid});
        usersByGroup[gid] = users;
    }
    $.each(users, function (i, user) {
        if (user.id == UID) return;
        var div = $('<div>'),
            input = $('<input>').prop({'type':'checkbox', id:'chk_'+user.id}).val(user.id),
            label = $('<label>').prop({'for':'chk_'+user.id});
        input.prop('checked', (sharedNoteUsers[user.id] !== undefined));
        input.on('click', selectUser);
        label.html(user.nickname + ' (' + user.username + ')');
        div.append(input, label);
        container.append(div);
    });
}

/**
 * Adds/removes user to/from shares
 */
function selectUser()
{
    if (this.checked) {
        sharedNoteUsers[this.value] = $(this).next('label').html();
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
    var list = $('#note_users').empty();
    $.each(sharedNoteUsers, function(id, name) {
        list.append($('<option>').val(id).html(name));
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
