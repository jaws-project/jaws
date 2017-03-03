/**
 * Subscription Javascript actions
 *
 * @category    Ajax
 * @package     Subscription
 */
/**
 * Use async mode, create Callback
 */
var SubscriptionCallback = {
    DeleteSubscriptions: function(response) {
        if (response['type'] == 'alert-success') {
            unselectGridRow('subscription_datagrid');
            getDG('subscription_datagrid', $('#subscription_datagrid')[0].getCurrentPage(), true);
        }
        SubscriptionAjax.showResponse(response);
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
 * Get subscriptions
 *
 */
function getSubscriptions(name, offset, reset)
{
    var filters = {
        'user'      : $('#filter_user').val(),
        'email'     : $('#filter_email').val(),
        'gadget'    : $('#filter_gadget').val()
    };

    var result = SubscriptionAjax.callSync('GetSubscriptions', {
        'offset': offset,
        'order': $('#order_type').val(),
        'filters': filters
    });

    if (reset) {
        var total = SubscriptionAjax.callSync('GetSubscriptionsCount', {
            'filters': filters
        });
    }
    resetGrid(name, result, total);
}

/**
 * Executes an action on subscriptions
 */
function subscriptionDGAction(combo)
{
    var rows = $('#subscription_datagrid')[0].getSelectedRows();

    if (combo.val() == 'delete') {
        if (rows.length < 1) {
            return;
        }
        var confirmation = confirm(jaws.Subscription.Defines.confirmSubscriptionDelete);
        if (confirmation) {
            SubscriptionAjax.callAsync('DeleteSubscriptions', rows);
        }
    }
}

/**
 * Search subscriptions
 */
function searchSubscription()
{
    getSubscriptions('subscription_datagrid', 0, true);
}

$(document).ready(function() {
    switch (jaws.Defines.mainAction) {
        case 'Subscription':
            initDataGrid('subscription_datagrid', SubscriptionAjax, getSubscriptions);
            break;

    }
});

var SubscriptionAjax = new JawsAjax('Subscription', SubscriptionCallback);
