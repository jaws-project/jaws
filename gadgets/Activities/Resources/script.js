/**
 * Activities Javascript actions
 *
 * @category    Ajax
 * @package     Activities
 */
/**
 * Use async mode, create Callback
 */
var ActivitiesCallback = {
    DeleteActivities: function(response) {
        if (response['type'] == 'alert-success') {
            unselectGridRow('datagrid');
            getDG('datagrid', $('#datagrid')[0].getCurrentPage(), true);
        }
    },
    DeleteAllActivities: function(response) {
        if (response['type'] == 'alert-success') {
            unselectGridRow('datagrid');
            getDG('datagrid', $('#datagrid')[0].getCurrentPage(), true);
        }
    }
};

/**
 * Get activities
 *
 */
function getActivities(name, offset, reset)
{
    var filters = {
        'from_date' : $('#from_date').val(),
        'to_date'   : $('#to_date').val(),
        'gadget'    : $('#filter_gadget').val(),
        'domain'    : $('#filter_domain').val()
    };

    var result = ActivitiesAjax.callSync('GetActivities', {
        'offset': offset,
        'order': $('#order_type').val(),
        'filters': filters
    });

    if (reset) {
        var total = ActivitiesAjax.callSync('GetActivitiesCount', {
            'filters': filters
        });
    }
    resetGrid(name, result, total);
}

/**
 * Executes an action on activities
 */
function activitiesDGAction(combo)
{
    var rows = $('#datagrid')[0].getSelectedRows();

    if (combo.val() == 'delete') {
        if (rows.length < 1) {
            return;
        }
        var confirmation = confirm(jaws.Activities.Defines.confirmActivitiesDelete);
        if (confirmation) {
            ActivitiesAjax.callAsync('DeleteActivities', rows);
        }
    } else if (combo.val() == 'deleteAll') {
        var confirmation = confirm(jaws.Activities.Defines.confirmActivitiesDelete);
        if (confirmation) {
            ActivitiesAjax.callAsync('DeleteAllActivities');
        }
    }}

/**
 * Search activities
 */
function searchActivities()
{
    getActivities('datagrid', 0, true);
}

$(document).ready(function() {
    initDatePicker('from_date');
    initDatePicker('to_date');
    initDataGrid('datagrid', ActivitiesAjax, getActivities);
});

var ActivitiesAjax = new JawsAjax('Activities', ActivitiesCallback);
