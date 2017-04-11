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
    CreateEvent: function(response) {
        if (response.type && response.type === 'alert-success') {
            $('#eventModal').modal('hide');
            $('#eventsGrid').repeater('render');
            EventsCalendarAjax.showResponse(response);
        } else {
            EventsCalendarAjax.showResponse(response, $('.gadget_response > div'));
        }
    },
    UpdateEvent: function(response) {
        if (response.type && response.type === 'alert-success') {
            $('#eventModal').modal('hide');
            $('#eventsGrid').repeater('render');
            EventsCalendarAjax.showResponse(response);
        } else {
            EventsCalendarAjax.showResponse(response, $('.gadget_response > div'));
        }
    },
    DeleteEvents: function(response) {
        if (response.type && response.type === 'alert-success') {
            $('#eventsGrid').repeater('render');
            EventsCalendarAjax.showResponse(response);
        } else {
            EventsCalendarAjax.showResponse(response, $('.gadget_response > div'));
        }
    }
};

// Define the data to be displayed in the repeater.
function eventsDataSource(options, callback) {

    // define the columns for the grid
    var columns = [
        {
            'label': CONST.subject,
            'property': 'subject',
            'sortable': true
        },
        {
            'label': CONST.from,
            'property': 'start_time',
            'sortable': true
        },
        {
            'label': CONST.to,
            'property': 'stop_time',
            'sortable': true
        },
        {
            'label': CONST.shared,
            'property': 'shared',
            'sortable': true
        }
    ];

    var filters = {
        'subject'       : $('#filter_subject').val(),
        'location'      : $('#filter_location').val(),
        'description'   : $('#filter_description').val(),
        'shared'        : $('#filter_shared').val(),
        'type'          : $('#filter_type').val(),
        'priority'      : $('#filter_priority').val(),
        'start_time'    : $('#filter_start_date').val(),
        'stop_time'     : $('#filter_stop_date').val(),
    };

    // set options
    var pageIndex = options.pageIndex;
    var pageSize = options.pageSize;
    var options = {
        'user': (CONST.mode === 'public')? 0 : CONST.user,
        'pageIndex': pageIndex,
        'pageSize': pageSize,
        'sortDirection': options.sortDirection,
        'sortBy': options.sortProperty,
        'search': filters
    };

    var rows = EventsCalendarAjax.callSync('GetEvents', options);

    var items = rows.records;
    var totalItems = rows.total;
    var totalPages = Math.ceil(totalItems / pageSize);
    var startIndex = (pageIndex * pageSize) + 1;
    var endIndex = (startIndex + pageSize) - 1;

    if(endIndex > items.length) {
        endIndex = items.length;
    }

    // configure datasource
    var dataSource = {
        'page':    pageIndex,
        'pages':   totalPages,
        'count':   totalItems,
        'start':   startIndex,
        'end':     endIndex,
        'columns': columns,
        'items':   items
    };

    // pass the datasource back to the repeater
    callback(dataSource);
}

/**
 * initiate friends datagrid
 */
function initiateEventsDG() {

    if (CONST.mode == 'public') {
        var actionItems = [
            {
                name: 'edit',
                html: '<span class="glyphicon glyphicon-pencil"></span> ' + CONST.edit,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editEvent(helpers.rowData.id);
                    callback();
                }

            },
            {
                name: 'delete',
                html: '<span class="glyphicon glyphicon-trash"></span> ' + CONST.delete,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();

                    // detect multi select
                    var ids = new Array();
                    if (helpers.length > 1) {
                        helpers.forEach(function (entry) {
                            ids.push(entry.rowData.id);
                        });

                    } else {
                        ids.push(helpers.rowData.id);
                    }

                    deleteEvents(ids);
                    callback();
                }
            }
        ];
    } else {
        var actionItems = [
            {
                name: 'view',
                html: '<span class="glyphicon glyphicon-eye-open"></span> ' + CONST.viewEvent,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editEvent(helpers.rowData.id);
                    callback();
                }

            },
        ];
    }

    var list_actions = {
        width: 50,
        items: actionItems
    };

    $('#eventsGrid').repeater({
        // setup your custom datasource to handle data retrieval;
        // responsible for any paging, sorting, filtering, searching logic
        dataSource: eventsDataSource,
        staticHeight: 400,
        list_actions: list_actions,
        list_selectable: 'multi',
        list_direction: $('.repeater-canvas').css('direction')
    });

    $('#eventModal').on('hidden.bs.modal', function (e) {
        stopAction();
    })
}

/**
 * Search events
 */
function searchEvents() {
    $('#eventsGrid').repeater('render');
}

/**
 * Add or update an event
 */
function saveEvent() {
    if (!$('#events-form #subject').val() || !$('#events-form #location').val() ||
         !$('#events-form #start_date').val() || !$('#events-form #stop_date').val()) {
        alert(CONST.incompleteFields);
        return;
    }

    var data = $.unserialize($('form#events-form').serialize());
    if (selectedEvent == 0) {
        EventsCalendarAjax.callAsync(
            'CreateEvent', data
        );
    } else {
        data.id = selectedEvent;
        EventsCalendarAjax.callAsync(
            'UpdateEvent', data
        );
    }
}

/**
 * Delete events
 */
function deleteEvents(ids)
{
    if (confirm(CONST.confirmDelete)) {
        EventsCalendarAjax.callAsync('DeleteEvents', {'ids': ids});
    }
}


/**
 * Edit a event
 */
function editEvent(id)
{
    selectedEvent = id;
    $('#friendModalLabel').html(CONST.editEvent);
    var eInfo = EventsCalendarAjax.callSync('GetEvent', {'id': selectedEvent});
    if (eInfo) {
        var $form = $('form#events-form'),
            form = $form.get(0);
        for (var field in eInfo) {
            if (eInfo.hasOwnProperty(field) && form[field]) {
                form[field].value = eInfo[field];
            }
        }
        form['public'].value = eInfo['public']? 1 : 0;
        $form.find('select[name=recurrence]').trigger('change');

        // disable form for user events
        if (CONST.mode === 'user') {
            $form.find('input, select, textarea').attr('disabled', true);
            $('#eventModal .modal-footer').hide();
        } else {
            $('#eventModal .modal-footer').show();
        }

        $('#eventModal').modal('show');
    }
}

$('#eventModal').on('hidden.bs.modal', function (e) {
    stopAction();
})


function stopAction() {
    selectedEvent = 0;
    $('form#events-form')[0].reset();
    $('#eventModalLabel').val(CONST.newEvent);
}

/**
 * Updates event repeat UI
 */
function updateRepeatUI(type)
{
    var $form = $('#events-form'),
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
    CONST = jaws.EventsCalendar.Defines.CONST;
    initiateEventsDG();
    updateRepeatUI();
    initDatePicker('filter_start_date');
    initDatePicker('filter_stop_date');

});

var EventsCalendarAjax = new JawsAjax('EventsCalendar', EventsCalendarCallback);
var Datagrid = null;
var selectedEvent = 0;
