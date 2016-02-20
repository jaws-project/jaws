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
    DeleteSiteActivities: function(response) {
        if (response['type'] == 'response_notice') {
            unselectGridRow('datagrid');
            getDG('datagrid', $('#datagrid')[0].getCurrentPage(), true);
        }
        ActivitiesAjax.showResponse(response);
    },
    DeleteAllSiteActivities: function(response) {
        if (response['type'] == 'response_notice') {
            unselectGridRow('datagrid');
            getDG('datagrid', $('#datagrid')[0].getCurrentPage(), true);
        }
        ActivitiesAjax.showResponse(response);
    }
};

/**
 * Get site activities
 *
 */
function getSiteActivities(name, offset, reset)
{
    var filters = {
        'from_date' : $('#from_date').val(),
        'to_date'   : $('#to_date').val(),
        'gadget'    : $('#filter_gadget').val(),
        'domain'    : $('#filter_domain').val()
    };

    var result = ActivitiesAjax.callSync('GetSiteActivities', {
        'offset': offset,
        'order': $('#order_type').val(),
        'filters': filters
    });

    if (reset) {
        var total = ActivitiesAjax.callSync('GetSiteActivitiesCount', {
            'filters': filters
        });
    }
    resetGrid(name, result, total);
}

/**
 * Executes an action on site activities
 */
function activityDGAction(combo)
{
    var rows = $('#datagrid')[0].getSelectedRows();

    if (combo.val() == 'delete') {
        if (rows.length < 1) {
            return;
        }
        var confirmation = confirm(confirmActivitiesDelete);
        if (confirmation) {
            ActivitiesAjax.callAsync('DeleteSiteActivities', rows);
        }
    } else if (combo.val() == 'deleteAll') {
        var confirmation = confirm(confirmActivitiesDelete);
        if (confirmation) {
            ActivitiesAjax.callAsync('DeleteAllSiteActivities');
        }
    }}

/**
 * Search site activities
 */
function searchActivities()
{
    getActivities('datagrid', 0, true);
}

var ActivitiesAjax = new JawsAjax('Activities', ActivitiesCallback);
