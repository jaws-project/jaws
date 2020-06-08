/**
 * Menu Javascript actions
 *
 * @category   Ajax
 * @package    Files
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
function Jaws_Gadget_Menu() { return {
    // ASync callback method
    AjaxCallback : {
    },

    /**
     * initialize gadget actions
     */
    init: function(mainGadget, mainAction)
    {
        $('.drilldown').drilldown({
            show_submenu_icon   : false,
            parent_class        : '',
            parent_class_link   : '',
            active_class        : 'active',
            header_wrapper      : 'breadcrumbwrapper',
            speed               : 0
        });
    },

}};