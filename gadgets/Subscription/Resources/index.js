/**
 * Subscription Javascript front-end actions
 *
 * @category    Ajax
 * @package     Subscription
 */

/**
 * Use async mode, create Callback
 */
var SubscriptionCallback = {
    UpdateSubscription: function(response) {
        SubscriptionAjax.showResponse(response);
    }
};

/**
 * Update subscription
 */
function updateSubscription()
{
    var result = SubscriptionAjax.callAsync(
        'UpdateSubscription',
        $.unserialize($('form[name=subscription]').serialize())
    );
    return false;
}

var SubscriptionAjax = new JawsAjax('Subscription', SubscriptionCallback);
