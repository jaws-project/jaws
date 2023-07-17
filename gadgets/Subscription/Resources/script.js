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

    var result = SubscriptionAjax.callAsync('GetSubscriptions', {
        'offset': offset,
        'order': $('#order_type').val(),
        'filters': filters
    }, false, {'async': false});

    if (reset) {
        var total = SubscriptionAjax.callAsync('GetSubscriptionsCount', {
            'filters': filters
        }, false, {'async': false});
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
        var confirmation = confirm(Jaws.gadgets.Subscription.defines.confirmSubscriptionDelete);
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
    switch (Jaws.defines.mainAction) {
        case 'Subscription':
            initDataGrid('subscription_datagrid', SubscriptionAjax, getSubscriptions);
            break;

    }
});

var SubscriptionAjax = new JawsAjax('Subscription', SubscriptionCallback);
