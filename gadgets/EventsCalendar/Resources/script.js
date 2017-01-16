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
        if (response.type && response.type !== 'alert-success') {
            EventsCalendarAjax.showResponse(response);
        } else {
        }
    },
    GetEvent: function(response) {
        if (response.id) {
            w2ui['datagrid'].unlock();
            editEvent(response);
        } else {
            EventsCalendarAjax.showResponse(response);
        }
    },
    CreateEvent: function(response) {
        if (response.type && response.type === 'alert-success') {
            w2popup.close();
            w2ui['datagrid'].reload();
            EventsCalendarAjax.showResponse(response);
        } else {
            EventsCalendarAjax.showResponse(response, $('.gadget_response > div'));
        }
    },
    UpdateEvent: function(response) {
        if (response.type && response.type === 'alert-success') {
            w2popup.close();
            w2ui['datagrid'].reload();
            EventsCalendarAjax.showResponse(response);
        } else {
            EventsCalendarAjax.showResponse(response, $('.gadget_response > div'));
        }
    },
    DeleteEvent: function(response) {
        if (response.type && response.type === 'alert-success') {
            w2ui['datagrid'].reload();
            EventsCalendarAjax.showResponse(response);
        } else {
            EventsCalendarAjax.showResponse(response, $('.gadget_response > div'));
        }
    }
};

/**
 * Initiates events calendar
 */
function initEventsCalendar() {
    CONST = jQuery.parseJSON(jaws.gadgets.EventsCalendar.CONST);

    w2utils.settings.dataType = 'JSON';
    if (CONST.calendar === 'Jalali') {
        w2utils.locale('libraries/w2ui/fa-pe.json');
    }
    w2utils.settings.dateFormat = 'yyyy-m-d';

    initDatagrid('#events_datagrid');
}

/**
 * Prepares events datagrid
 */
function initDatagrid(targetEl) {
    var components = {
        toolbar: true,
        footer: true,
        selectColumn: (CONST['mode'] === 'public')
    };
    if (CONST['mode'] === 'public') {
        components.toolbarAdd = true;
        components.toolbarEdit = true;
        components.toolbarDelete = true;
    }
    $(targetEl).w2grid({
        name: 'datagrid',
        recid: 'id',
        method: 'POST',
        limit: CONST.eventsLimit,
        multiSelect: true,
        multiSearch: true,
        url: {get: EventsCalendarAjax.baseURL + 'GetEvents'},
        show: components,
        columns: [
            {field: 'subject', caption: CONST.subject, size: '40%', sortable: true},
            {field: 'start_time', caption: CONST.from, size: '15%', sortable: true},
            {field: 'stop_time', caption: CONST.to, size: '15%', sortable: true},
            {field: 'shared', caption: CONST.shared, size: '15%', sortable: true}
        ],
        searches: [
            {field: 'subject', caption: CONST.subject, type: 'text'},
            {field: 'location', caption: CONST.location, type: 'text'},
            {field: 'description', caption: CONST.description, type: 'text'},
            {field: 'shared', caption: CONST.shared, type: 'list', options: {items: {1: CONST.yes, 0: CONST.no}}},
            {field: 'type', caption: CONST.type, type: 'list', options: {items: CONST.types}},
            {field: 'priority', caption: CONST.priority, type: 'list', options: {items: CONST.priorities}},
            {field: 'date', caption: CONST.date, type: 'date'}
        ],
        records: [],
        onRequest: function (event) {
            switch (event.postData.cmd) {
                case 'get':
                    event.postData.user = (CONST['mode'] === 'public')? 0 : CONST['user'];
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
        onToolbar: function (event) {
            if (event.target === 'w2ui-search-advanced') {
                // set date operator to 'between'
                setTimeout(function() {
                    $('#grid_datagrid_operator_6').val('between');
                    $('#grid_datagrid_range_6').css('display', '');
                }, 100);
            }
        },
        onSearch: function (event) {
            if (event.searchField === 'all') {
                event.searchData = [{term: event.searchValue}];
            }
        },
        onDblClick: function (event) {
            getEvent(event.recid);
        },
        onAdd: function (event) {
            newEvent();
        },
        onEdit: function (event) {
            getEvent(event.recid);
        },
        onDelete: function (event) {
            if (event.force) {
                EventsCalendarAjax.callAsync('DeleteEvent', {events: w2ui['datagrid'].getSelection()});
            }
        }
    });
}

function initForm($form) {
    if (w2ui['frm_event']) {
        w2ui['frm_event'].destroy();
    }
    $form.w2form({
        name: 'frm_event',
        fields: [
            {name: 'subject', type: 'text', required: true},
            {name: 'location', type: 'text', required: true},
            {name: 'start_date', type: 'date', format: 'yyyy-m-d', required: true},
            {name: 'stop_date', type: 'date', format: 'yyyy-m-d', required: true},
            {name: 'description', type: 'text'}
        ]
    });
}

function getEvent(id) {
    SelectedEvent = id;
    w2ui['datagrid'].lock('', true);
    EventsCalendarAjax.callAsync('GetEvent', {event_id: id});
}

function newEvent() {
    SelectedEvent = null;
    $('.w2ui-form').w2popup({
        title: CONST.newEvent
    });
    initForm($('#w2ui-popup').find('form'));
    w2ui['frm_event'].clear();
    updateRepeatUI();
}

function editEvent(data) {
    $('.w2ui-form').w2popup({
        title: (CONST['mode'] === 'public')? CONST['editEvent'] : CONST['viewEvent'],
        modal: true
    });
    var $form = $('#w2ui-popup').find('form'),
        form = $form.get(0);
    initForm($form);
    for (var field in data) {
        if (data.hasOwnProperty(field) && form[field]) {
            form[field].value = data[field];
        }
    }
    form['public'].value = data['public']? 1 : 0;
    $form.find('select[name=recurrence]').trigger('change');

    // disable form for user events
    if (CONST['mode'] === 'user') {
        $form.find('input, select, textarea').attr('disabled', true);
        $form.find('.w2ui-buttons').hide();
    }
}

function fromAction(button) {
    switch (button) {
        case 'cancel':
            w2popup.close();
            break;

        case 'save':
            var $form = $('#w2ui-popup').find('form'),
                data = $.unserialize($form.serialize()),
                action = (data.id === '')? 'CreateEvent' : 'UpdateEvent';
            // console.log($form, data);
            EventsCalendarAjax.callAsync(action, data);
            break;
    }
}

/**
 * Updates event repeat UI
 */
function updateRepeatUI(type)
{
    var $form = $('#w2ui-popup').find('form'),
        $day = $('select[name=day]').hide(),
        $wday = $('select[name=wday]').hide(),
        $month = $('select[name=month]').hide();

    switch (type) {
        case '1':
            $day.val(1);
            $wday.val(1);
            $month.val(1);
            break;
        case '2':
            $wday.show('inline');
            $day.val(1);
            $month.val(1);
            break;
        case '3':
            $day.show('inline');
            $wday.val(1);
            $month.val(1);
            break;
        case '4':
            $day.show('inline');
            $month.show('inline');
            $wday.val(1);
            break;
    }
}

$(document).ready(function () {
    initEventsCalendar();
});

var EventsCalendarAjax = new JawsAjax('EventsCalendar', EventsCalendarCallback);
var Datagrid = null;
var SelectedEvent = null;
