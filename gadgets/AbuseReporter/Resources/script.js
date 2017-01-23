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
        AbuseReporterAjax.showResponse(response);
    },
    UpdateReport: function(response) {
        if (response.type == 'alert-success') {
            stopAction();
            $('#reportsGrid').repeater('render');
        }
        AbuseReporterAjax.showResponse(response);
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
    $('#reportModalLabel').html(jaws.gadgets.AbuseReporter.lbl_editReport);
    var reportInfo = AbuseReporterAjax.callSync('GetReport', {'id': selectedReport});
    if (reportInfo) {
        $('#report-form input, #report-form select, #report-form textarea').each(
            function () {
                $(this).val(reportInfo[$(this).attr('name')]);
            }
        );

        $('#url').prop('href', reportInfo['url']);
        $('#url').html(reportInfo['url']);
        $('#reportModal').modal('show');
    }
}

/**
 * Update the report
 */
function saveReport()
{
    AbuseReporterAjax.callAsync(
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
    if (confirm(jaws.gadgets.AbuseReporter.confirmDelete)) {
        AbuseReporterAjax.callAsync('DeleteReport', {'id': id});
    }
}

/**
 * Define the data to be displayed in the users datagrid
 */
function reportsDataSource(options, callback) {
    // define the columns for the grid
    var columns = [
        {
            'label': jaws.gadgets.AbuseReporter.lbl_gadget,
            'property': 'gadget',
            'sortable': true
        },
        {
            'label': jaws.gadgets.AbuseReporter.lbl_action,
            'property': 'action',
            'sortable': true
        },
        {
            'label': jaws.gadgets.AbuseReporter.lbl_type,
            'property': 'type',
            'sortable': true
        },
        {
            'label': jaws.gadgets.AbuseReporter.lbl_priority,
            'property': 'priority',
            'sortable': true
        },
        {
            'label': jaws.gadgets.AbuseReporter.lbl_status,
            'property': 'status',
            'sortable': true
        }
    ];

    // set options
    var pageIndex = options.pageIndex;
    var pageSize = options.pageSize;
    var filters = {
        gadget: $('#filter_gadget').val(),
        action: $('#filter_action').val(),
        priority: $('#filter_priority').val(),
        status: $('#filter_status').val()
    };
    var options = {
        'offset': pageIndex,
        'limit': pageSize,
        'sortDirection': options.sortDirection,
        'sortBy': options.sortProperty,
        'filters': filters
    };

    var rows = AbuseReporterAjax.callSync('GetReports', options);
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
 * initiate reports datagrid
 */
function initiateReportsDG() {
    var list_actions = {
        width: 50,
        items: [
            {
                name: 'edit',
                html: '<span class="glyphicon glyphicon-pencil"></span> ' + jaws.gadgets.AbuseReporter.lbl_edit,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editReport(helpers.rowData.id);
                    callback();
                }

            },
            {
                name: 'delete',
                html: '<span class="glyphicon glyphicon-trash"></span> ' + jaws.gadgets.AbuseReporter.lbl_delete,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    deleteReport(helpers.rowData.id);
                    callback();
                }
            }
        ]
    };

    // initialize the repeater
    var repeater = $('#reportsGrid');
    repeater.repeater({
        // setup your custom datasource to handle data retrieval;
        // responsible for any paging, sorting, filtering, searching logic
        dataSource: reportsDataSource,
        staticHeight: 500,
        list_actions: list_actions
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