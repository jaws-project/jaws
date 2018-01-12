/**
 * Settings Javascript actions
 *
 * @category   Ajax
 * @package    Settings
 * @author     Jonathan Hernandez <ion@gluch.org.mx>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2017 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
function Jaws_Gadget_Settings() { return {
    // ASync callback method
    AjaxCallback : {
        UpdateBasicSettings: function(response) {
            this.ajax.showResponse(response);
        },

        UpdateAdvancedSettings: function(response) {
            this.ajax.showResponse(response);
        },

        UpdateMetaSettings: function(response) {
            this.ajax.showResponse(response);
        },

        UpdateMailSettings: function(response) {
            this.ajax.showResponse(response);
        },

        UpdateFTPSettings: function(response) {
            this.ajax.showResponse(response);
        },

        UpdateProxySettings: function(response) {
            this.ajax.showResponse(response);
        }
    },

    /**
     * Update basic settings
     */
    submitBasicForm: function() {
        this.ajax.callAsync(
            'UpdateBasicSettings',
            $.unserialize($('#settingsForm input,select,textarea').serialize())
        );
    },

    /**
     * Update advanced settings
     */
    submitAdvancedForm: function() {
        this.ajax.callAsync(
            'UpdateAdvancedSettings',
            $.unserialize($('#settingsForm input,select,textarea').serialize())
        );
    },

    /**
     * Adds new custom meta
     */
    addCustomMeta: function() {
        var div = $('<div>', {'class': 'fields'}),
            label = $('<label>').html(custom_meta),
            inputName  = $('<input>', {type:'text', title:'Meta Name', 'class':'meta-name'}),
            inputValue = $('<input>', {type:'text', title:'Meta Content', 'class':'meta-value'});

        div.append(label);
        div.append(inputName);
        div.append(inputValue);
        $('#customMeta').append(div);
    },

    /**
     * Update meta
     */
    submitMetaForm: function() {
        var customMeta   = [],
            customInputs = $('#customMeta input.meta-name');

        customInputs.each(function(index, input) {
            if (!$(input).val()) {
                $(input).parent().empty();
                return;
            }
            customMeta[index] = [$(input).val(), $(input).next().val()];
        });

        var settings = $.unserialize($('#settingsForm input,select,textarea').serialize());
        settings["site_custom_meta"] = customMeta;
        this.ajax.callAsync('UpdateMetaSettings', settings);
    },

    /**
     * Update mail-server settings
     */
    submitMailSettingsForm: function() {
        this.ajax.callAsync(
            'UpdateMailSettings',
            $.unserialize($('#settingsForm input,select,textarea').serialize())
        );
    },

    /**
     * Update proxy settings
     */
    submitProxySettingsForm: function() {
        this.ajax.callAsync(
            'UpdateProxySettings',
            $.unserialize($('#settingsForm input,select,textarea').serialize())
        );
    },

    toggleGR: function() {
        if ($('#use_gravatar').val() == 'yes') {
            $('#gravatar_rating').prop('disabled', false);
        } else {
            $('#gravatar_rating').prop('disabled', true);
        }
    },

    changeMailer: function() {
        $('#settingsForm input,select,textarea').not('#mailer').prop("disabled", true);
        switch($('#mailer').val()) {
            case 'phpmail':
                $('#settingsForm #gate_email,#gate_title').prop("disabled", false);
                break;
            case 'sendmail':
                $('#settingsForm #gate_email,#gate_title,#sendmail_path').prop("disabled", false);
                break;
            case 'smtp':
                $('#settingsForm input,select,textarea').not('#mailer').prop("disabled", false);
                $('#settingsForm #sendmail_path').prop("disabled", true);
                break;
        }
    },

    /**
     * Update ftp-server settings
     */
    submitFTPSettingsForm: function() {
        this.ajax.callAsync(
            'UpdateFTPSettings',
            $.unserialize($('#settingsForm input,select,textarea').serialize())
        );
    },

    init: function() {
        if ($('#mailer').length) {
            this.changeMailer();
        }
    },

}};

