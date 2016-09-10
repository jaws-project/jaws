/**
 * EventsCalendar Javascript actions
 *
 * @category    Ajax
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var ECCallback = {
    DeleteEvent: function(response) {
        if (response.type === 'response_error') {
            ECAjax.showResponse(response);
        } else {
            location.assign(events_url);
        }
    },

    UpdateShare: function(response) {
        if (response.type === 'response_error') {
            ECAjax.showResponse(response);
        } else {
            location.assign(events_url);
        }
    }
};

/**
 * Initiates Events
 */
function initEvents()
{
}

/**
 * Updates event repeat UI
 */
function switchRepeatUI(type)
{
    var $day = $('#event_day').hide(),
        $wday = $('#event_wday').hide(),
        $month = $('#event_month').hide();

    switch (type) {
        case '1':
            $day.val(0);
            $wday.val(0);
            $month.val(0);
            break;
        case '2':
            $wday.show('inline');
            $day.val(0);
            $month.val(0);
            break;
        case '3':
            $day.show('inline');
            $wday.val(0);
            $month.val(0);
            break;
        case '4':
            $day.show('inline');
            $month.show('inline');
            $wday.val(0);
            break;
    }
}

/**
 * Selects/Deselects all rows
 */
function checkAll()
{
    $('#grid_events').find('input').prop('checked', $('#chk_all').prop('checked'));
}

/**
 * Submits search
 */
/*function searchEvents(form)
{
    if (form.query.value.length < 2) {
        alert(errorShortQuery);
        return;
    }
    form.submit();
}*/

/**
 * Submits event
 */
function submitEvent(form)
{
    if (form.subject.value === '') {
        alert(errorIncompleteData);
        form.subject.focus();
        return;
    }
    if (form.start_date.value === '') {
        alert(errorIncompleteData);
        form.start_date.focus();
        return;
    }
    if (form.stop_date.value === '') {
        alert(errorIncompleteData);
        form.stop_date.focus();
        return;
    }
    form.submit();
}

/**
 * Deletes current event
 */
function deleteEvent(id)
{
    if (confirm(confirmDelete)) {
        ECAjax.callAsync('DeleteEvent', {id_set:id});
    }
}

/**
 * Deletes selected events
 */
function deleteEvents()
{
    var id_set = $('#grid_events').find('input:checked');
    if (id_set.length === 0) {
        return;
    }
    id_set = $.map(id_set, function(input) {
        return input.value;
    });
    if (confirm(confirmDelete)) {
        ECAjax.callAsync('DeleteEvent', {id_set:id_set.join(',')});
    }
}

/**
 * Initiates Sharing
 */
function initShare()
{
    $('#sys_groups').get(0).selectedIndex = -1;
    var $users = $('#event_users').get(0);
    for (var i = 0; i < $users.options; i++) {
        sharedEventUsers[$users[i].value] = $users[i].text;
    }
}

/**
 * Fetches and displays users of selected group
 */
function toggleUsers(gid)
{
    var container = $('#sys_users').empty(),
        users = usersByGroup[gid];
    if (users === undefined) {
        users = ECAjax.callSync('GetUsers', {'gid':gid});
        usersByGroup[gid] = users;
    }
    console.log(users);
    $.each(users, function (i, user) {
        if (user.id == UID) return;
        var $div = $('<div>'),
            $input = $('<input>', {type: 'checkbox', id: 'chk_' + user.id, value: user.id}),
            $label = $('<label>', {'for': 'chk_' + user.id});
        $input.prop('checked', (sharedEventUsers[user.id] !== undefined));
        $input.click(selectUser);
        $label.html(user.nickname + ' (' + user.username + ')');
        $div.append($input, $label);
        container.append($div);
    });
}

/**
 * Adds/removes user to/from shares
 */
function selectUser()
{
    if (this.checked) {
        sharedEventUsers[this.value] = $(this).next('label').html();
    } else {
        delete sharedEventUsers[this.value];
    }
    updateShareUsers();
}

/**
 * Updates list of event users
 */
function updateShareUsers()
{
    var list = $('#event_users').empty().get(0);
    $.each(sharedEventUsers, function(id, name) {
        list.options[list.options.length] = new Option(name, id);
    });
}

/**
 * Submits share data
 */
function submitShare(id)
{
    ECAjax.callAsync(
        'UpdateShare',
        {'id':id, 'users': Object.keys(sharedEventUsers).join(',')}
    );
}

var ECAjax = new JawsAjax('EventsCalendar', ECCallback),
    usersByGroup = {},
    sharedEventUsers = {};
