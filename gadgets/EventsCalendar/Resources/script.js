/**
 * EventsCalendar Javascript actions
 *
 * @category    Ajax
 * @package     Mailbox
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2016 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Use async mode, create Callback
 */
var EventsCalendarCallback = {
    GetEvents: function(response) {
        if (response.type && response.type !== 'response_notice') {
            EventsCalendarAjax.showResponse(response);
        } else {
        }
    },
    GetEvent: function(response) {
        console.log(response);
        if (response.id) {
            w2ui['datagrid'].unlock();
            editEvent(response);
        } else {
            EventsCalendarAjax.showResponse(response);
        }
    }
};

var EventsCalendarAjax = new JawsAjax('EventsCalendar', EventsCalendarCallback);
var Datagrid = null;
var SelectedEvent = null;

/**
 * Initiates events calendar
 */
function initEventsCalendar() {
    CONST = jQuery.parseJSON(CONST);

    w2utils.settings.dataType = 'JSON';
    // TODO: detect language
    w2utils.locale('libraries/w2ui/fa-pe.json');

    initForm();
    initDatagrid('#events_datagrid');
}

/**
 * Prepares events datagrid
 */
function initDatagrid(targetEl) {
    $(targetEl).w2grid({
        name: 'datagrid',
        recid: 'id',
        method: 'POST',
        // limit: CONST.rowsPerPage,
        // toolbar: MessagesToolbar,
        // multiSelect: ACL.ManageMessages? true : false,
        // multiSearch: true,
        // searches: [
        //     {field: 'subject', caption: CONST.subject, type: 'text'},
        //     {field: 'from', caption: CONST.from, type: 'text'},
        //     {field: 'to', caption: CONST.to, type: 'text'},
        //     {field: 'body', caption: CONST.body, type: 'text'},
        //     {field: 'date', caption: CONST.date, type: 'date'}
        // ],
        url: {get: EventsCalendarAjax.baseURL + 'GetEvents'},
        show: {
            toolbar: true,
            footer: true,
            selectColumn: true
        },
        columns: [
            {
                field: 'subject', caption: CONST.subject, size: '40%', sortable: true, render: function (record) {
                var isSeen = (typeof record.seen != 'undefined' && record.seen);
                return isSeen ? record.subject : '<strong>' + record.subject + '</strong>';
            }},
            {
                field: 'date', caption: CONST.date, size: '15%', sortable: true, render: function (record) {
                var isSeen = (typeof record.seen != 'undefined' && record.seen);
                return isSeen ? record.date : '<strong>' + record.date + '</strong>';
            }},
            {
                field: 'time', caption: CONST.time, size: '15%', sortable: true, render: function (record) {
                var isSeen = (typeof record.seen != 'undefined' && record.seen);
                return isSeen ? record.time : '<strong>' + record.time + '</strong>';
            }},
            {
                field: 'shared', caption: CONST.shared, size: '15%', sortable: true, render: function (record) {
                var isSeen = (typeof record.seen != 'undefined' && record.seen);
                return isSeen ? record.shared : '<strong>' + record.shared + '</strong>';
            }}
        ],
        records: [],
        onRequest: function (event) {
            switch (event.postData.cmd) {
                case 'get':
                    event.postData.user = (CONST['mode'] == 'public')? 0 : null;
                    break;

                case 'delete':
                    // event.postData = {
                    //     'ids': event.postData.selected
                    // };
                    break;

                case 'save':
                    break;

            }
        },
        onLoad: function (event) {
            event.xhr.responseText = eval('(' + event.xhr.responseText + ')');
            if (event.xhr.responseText.type) {
                event.xhr.responseText.message = event.xhr.responseText.text;
                event.xhr.responseText.status = 'error';
            }
        },
        onSearch: function (event) {
            if (event.searchField == 'all') {
                event.searchData = [{
                    field: 'text',
                    value: event.searchValue
                }];
            }
        },
        onSelect: function (event) {
            // FIXME: w2grid.getSelection() does not work properly without delay
            setTimeout(function () {
                // updateMessagesToolbar();
            }, 0);
        },
        onUnselect: function (event) {
            setTimeout(function () {
                updateMessagesToolbar();
            }, 0);
        },
        onClick: function (event) {
            console.log(event.recid);
            getEvent(event.recid);
            event.preventDefault(); // prevent 'onSelect' event
        },
        onDelete: function (event) {
            if (event.xhr) {
                event.xhr.responseText = eval('(' + event.xhr.responseText + ')');
                if (event.xhr.responseText.type != 'response_notice') {
                    event.xhr.responseText.message = event.xhr.responseText.text;
                    event.xhr.responseText.status = 'error';
                } else {
                    event.xhr.responseText = event.xhr.responseText.data;
                }
            }
        }
    });
}

function initForm() {
    $('#dlg_event').w2form({
        name   : 'myForm',
        fields : [
            {name: 'subject', type: 'text', required: true},
            {name: 'location', type: 'text', required: true},
            {name: 'start_date', type: 'date', format: 'yyyy.m.d', required: true},
            {name: 'stop_date', type: 'date', format: 'yyyy.m.d', required: true},
            {name: 'description', type: 'text'}
        ],
        actions: {
            reset: function () {
                this.clear();
            },
            save: function () {
                this.save();
            }
        }
    });
}

function getEvent(id) {
    SelectedEvent = id;
    w2ui['datagrid'].lock('', true);
    EventsCalendarAjax.callAsync('GetEvent', {event_id: id});
}

function editEvent(event) {
    $('.dlg-event').w2popup();
}
