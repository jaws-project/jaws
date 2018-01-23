/**
 * ControlPanel Javascript actions
 *
 * @category    Ajax
 * @package     ControlPanel
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2013-2018 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
function Jaws_Gadget_ControlPanel() { return {
    // web storage
    storage : null,

    // ASync callback method
    AjaxCallback : {
        JawsVersion: function(response) {
            $('#latest_jaws_version').html(response);
            if (!response.blank() && response !== $('#jaws_version').val()) {
                $('#div.notify_version').css('display', 'block');
            }
        },
    },

    /**
     * initialize sidebar
     */
    initSidebar: function() {
        $('#sidebar h2').on('click', $.proxy(function (event) {
            $(event.target).toggleClass('collapsed');
            this.storage.update(
                $('#sidebar').children().index($(event.target).parent()),
                $(event.target).attr('class')
            );
            $(event.target).next('div').toggle();
        }, this));

        $('#sidebar').children().each($.proxy(function(i, el) {
            if (this.storage.fetch(i)) {
                $(el).children('h2').trigger('click');
            }
        }, this));
    },

    /**
     * check Jaws version
     */
    checkVersion: function() {
        // compare current version with latest jaws version
        if (!$('#latest_jaws_version').text() &&
            $('#latest_jaws_version').text() !== $('#jaws_version').val())
        {
            $('#div.notify_version').css('display', 'block');
        }

        // check jaws project website for latest version
        if ($('#do_checking').val() == 1) {
            this.gadget.ajax.callAsync('JawsVersion');
        }
    },

    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction) {
        // default action
        if (this.gadget.actions.indexOf('DefaultAction') >= 0) {
            this.storage = new JawsStorage('ControlPanel');
            this.initSidebar();
            this.checkVersion();
        }
    },

}};
