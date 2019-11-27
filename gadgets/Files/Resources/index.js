/**
 * Files Javascript actions
 *
 * @category   Ajax
 * @package    Files
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2019-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
function Jaws_Gadget_Files() { return {
    // ASync callback method
    AjaxCallback : {
    },

    /**
     * remove file
     */
    extraFile: function(element) {
        $('#file_model').clone(true).insertBefore($(element).parent()).show();
    },

    /**
     * remove file
     */
    removeFile: function(element) {
        $(element).parent().remove();
    },

    /**
     * browse file
     */
    browseFile: function(fileElement) {
        let fReader = new FileReader();
        fReader.readAsDataURL(fileElement.files[0]);
        fReader.onload = function (event) {
            $(fileElement).prev().attr('src', event.target.result);
        }
    },

    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction) {
        //
    },

}};