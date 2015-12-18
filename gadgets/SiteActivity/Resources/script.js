/**
 * SiteActivity Javascript actions
 *
 * @category    Ajax
 * @package     SiteActivity
 */
/**
 * Use async mode, create Callback
 */
var SiteActivityCallback = {
    DeleteSiteActivities: function(response) {
        if (response['type'] == 'response_notice') {
            unselectGridRow('sa_datagrid');
            getDG('sa_datagrid', $('#sa_datagrid')[0].getCurrentPage(), true);
        }
        SiteActivityAjax.showResponse(response);
    },
    DeleteAllSiteActivities: function(response) {
        if (response['type'] == 'response_notice') {
            unselectGridRow('sa_datagrid');
            getDG('sa_datagrid', $('#sa_datagrid')[0].getCurrentPage(), true);
        }
        SiteActivityAjax.showResponse(response);
    }
};

/**
 * On term key press, for compatibility Opera/IE with other browsers
 */
function OnTermKeypress(element, event)
{
    if (event.keyCode == 13) {
        element.blur();
        element.focus();
    }
}

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

    var result = SiteActivityAjax.callSync('GetSiteActivities', {
        'offset': offset,
        'order': $('#order_type').val(),
        'filters': filters
    });

    if (reset) {
        var total = SiteActivityAjax.callSync('GetSiteActivitiesCount', {
            'filters': filters
        });
    }
    resetGrid(name, result, total);
}

/**
 * Executes an action on site activities
 */
function siteActivityDGAction(combo)
{
    var rows = $('#sa_datagrid')[0].getSelectedRows();

    if (combo.val() == 'delete') {
        if (rows.length < 1) {
            return;
        }
        var confirmation = confirm(confirmSiteActivityDelete);
        if (confirmation) {
            SiteActivityAjax.callAsync('DeleteSiteActivities', rows);
        }
    } else if (combo.val() == 'deleteAll') {
        var confirmation = confirm(confirmSiteActivityDelete);
        if (confirmation) {
            SiteActivityAjax.callAsync('DeleteAllSiteActivities');
        }
    }}

/**
 * Search site activities
 */
function searchSiteActivity()
{
    getSiteActivities('sa_datagrid', 0, true);
}

var SiteActivityAjax = new JawsAjax('SiteActivity', SiteActivityCallback);
