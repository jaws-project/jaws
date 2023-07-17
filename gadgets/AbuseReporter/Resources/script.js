/**
 * AbuseReporter Javascript actions
 *
 * @category    Ajax
 * @package     AbuseReporter
 */

/**
 * Use async mode, create Callback
 */
var AbuseReporterCallback = {
    DeleteReport: function(response) {
        if (response.type == 'alert-success') {
            stopAction();
            $('#reportsGrid').repeater('render');
        }
    },
    UpdateReport: function(response) {
        if (response.type == 'alert-success') {
            stopAction();
            $('#reportsGrid').repeater('render');
        }
    }
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    selectedReport = 0;
    $('#reportModal').modal('hide');
    $('form#report-form')[0].reset();

}

/**
 * Edit a report
 */
function editReport(id)
{
    selectedReport = id;
    $('#reportModalLabel').html(Jaws.gadgets.AbuseReporter.defines.lbl_editReport);
    var reportInfo = AbuseReporterAjax.call('GetReport', {'id': selectedReport}, false, {'async': false});
    if (reportInfo) {
        $('#report-form input, #report-form select, #report-form textarea').each(
            function () {
                $(this).val(reportInfo[$(this).attr('name')]);
            }
        );

        $('#url').prop('href', reportInfo['url']).html(reportInfo['url']);
        $('#reportModal').modal('show');
    }
}

/**
 * Update the report
 */
function saveReport()
{
    AbuseReporterAjax.call(
        'UpdateReport', {
            id: selectedReport,
            data: $.unserialize($('form#report-form').serialize())
        }
    );
}


/**
 * Delete report
 */
function deleteReport(id)
{
    if (confirm(Jaws.gadgets.AbuseReporter.defines.confirmDelete)) {
        AbuseReporterAjax.call('DeleteReport', {'id': id});
    }
}

/**
 * Define the data to be displayed in the users datagrid
 */
function reportsDataSource(options, callback) {
    var columns = {
        'gadget': {
            'label': Jaws.gadgets.AbuseReporter.defines.lbl_gadget,
            'property': 'gadget',
            'sortable': true
        },
        'action': {
            'label': Jaws.gadgets.AbuseReporter.defines.lbl_action,
            'property': 'action',
            'sortable': true
        },
        'type': {
            'label': Jaws.gadgets.AbuseReporter.defines.lbl_type,
            'property': 'type',
            'sortable': true
        },
        'priority': {
            'label': Jaws.gadgets.AbuseReporter.defines.lbl_priority,
            'property': 'priority',
            'sortable': true
        },
        'status': {
            'label': Jaws.gadgets.AbuseReporter.defines.lbl_status,
            'property': 'status',
            'sortable': true
        }
    };

    // set sort property & direction
    if (options.sortProperty) {
        columns[options.sortProperty].sortDirection = options.sortDirection;
    }
    columns = Object.values(columns);

    AbuseReporterAjax.call(
        'GetReports', {
            'offset': options.offset,
            'limit': options.pageSize,
            'sortDirection': options.sortDirection,
            'sortBy': options.sortProperty,
            'filters': {
                gadget: $('#filter_gadget').val(),
                action: $('#filter_action').val(),
                priority: $('#filter_priority').val(),
                status: $('#filter_status').val()
            }
        },
        function(response, status) {
            var dataSource = {};
            if (response['type'] == 'alert-success') {
                // processing end item index of page
                options.offset = options.pageIndex*options.pageSize;
                options.end = options.offset + options.pageSize;
                options.end = (options.end > response['data'].total)? response['data'].total : options.end;
                dataSource = {
                    'page': options.pageIndex,
                    'pages': Math.ceil(response['data'].total/options.pageSize),
                    'count': response['data'].total,
                    'start': options.offset + 1,
                    'end':   options.end,
                    'columns': columns,
                    'items': response['data'].records
                };
            } else {
                dataSource = {
                    'page': 0,
                    'pages': 0,
                    'count': 0,
                    'start': 0,
                    'end':   0,
                    'columns': columns,
                    'items': {}
                };
            }
            // pass the datasource back to the repeater
            callback(dataSource);
        }
    );
}

/**
 * initiate reports datagrid
 */
function initiateReportsDG() {
    var list_actions = {
        width: 50,
        items: [
            {
                name: 'edit',
                html: '<span class="glyphicon glyphicon-pencil"></span> ' + Jaws.gadgets.AbuseReporter.defines.lbl_edit,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editReport(helpers.rowData.id);
                    callback();
                }
            },
            {
                name: 'delete',
                html: '<span class="glyphicon glyphicon-trash"></span> ' + Jaws.gadgets.AbuseReporter.defines.lbl_delete,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    deleteReport(helpers.rowData.id);
                    callback();
                }
            }
        ]
    };

    // initialize the repeater
    $('#reportsGrid').repeater({
        dataSource: reportsDataSource,
        staticHeight: 500,
        list_actions: list_actions,
        list_direction: $('.repeater-canvas').css('direction')
    });

    // monitor required events
    $( ".datagrid-filters select" ).change(function() {
        $('#reportsGrid').repeater('render');
    });
    $( ".datagrid-filters input" ).keypress(function(e) {
        if (e.which == 13) {
            $('#reportsGrid').repeater('render');
        }
    });
    $('#reportModal').on('hidden.bs.modal', function (e) {
        $('form#users-form')[0].reset();
    });
}


$(document).ready(function () {
    initiateReportsDG();
});

var AbuseReporterAjax = new JawsAjax('AbuseReporter', AbuseReporterCallback),
    selectedReport = 0;