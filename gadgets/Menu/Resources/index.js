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
            wrapper_class	    : 'drilldown panel panel-success',
            menu_class		    : 'drilldown-menu',
            submenu_class	    : 'nav ',
            parent_class		: 'dd-parent',
            parent_class_link	: 'dd-parent-a',
            active_class		: 'active',
            header_class_list   : 'breadcrumb',
            header_class		: 'breadcrumbwrapper',
            speed       	    : 200,
            save_state		    : true,
            default_text	    : this.gadget.defines.title,
            show_end_nodes      : true, // drill to final empty nodes
            header_tag		    : 'div',// h3
            header_tag_class    : 'list-group-item active', // hidden list-group-item active
            storage             : this.gadget.storage
        });
    },

}};