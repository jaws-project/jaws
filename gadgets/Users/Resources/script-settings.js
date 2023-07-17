/**
 * Users Javascript actions
 *
 * @category   Ajax
 * @package    Users
 */
function Jaws_Gadget_Users_Action_Settings() {
    return {

        // ASync callback method
        AjaxCallback: {
        },

        /**
         * Save settings
         */
        updateSettings: function() {
            this.ajax.call(
                'UpdateSettings',
                $.unserialize($('#users_settings input,select,textarea').serialize())
            );
        },

        //------------------------------------------------------------------------------------------------------------------
        /**
         * initialize gadget actions
         */
        //------------------------------------------------------------------------------------------------------------------
        init: function (mainGadget, mainAction) {
            $('#btnUpdateSettings').on('click', $.proxy(function (e) {
                this.updateSettings();
            }, this));
        }
    }
};