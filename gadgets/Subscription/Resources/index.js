/**
 * Subscription Javascript front-end actions
 *
 * @category    Ajax
 * @package     Subscription
 */
function Jaws_Gadget_Subscription() { return {
    // ASync callback method
    AjaxCallback : {
        UpdateSubscription: function(response) {
            //
        },
    },

    /**
     * Update subscription
     */
    updateSubscription: function() {
        this.gadget.ajax.callAsync(
            'UpdateSubscription',
            $.unserialize($('form[name=subscription]').serialize())
        );

        return false;
    },

    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction) {
    },

}};
