/**
 * Categories Javascript actions
 *
 * @category   Ajax
 * @package    Categories
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
function Jaws_Gadget_Categories() { return {
    // ASync callback method
    AjaxCallback : {
    },

    /**
     * initialize gadget actions
     */
    init: function (mainGadget, mainAction) {
        // initialize upload files configuration
        $('[data-initialize=select]').each(
            $.proxy(
                function(index, elSelect) {
                    $(elSelect).select2(
                        {
                            createTag: function (params) {
                                let term = $.trim(params.term);
                                if (term === '') {
                                    return null;
                                }

                                return {
                                    id: '__' + term + '__',
                                    text: term
                                }
                            }
                        }
                    );
                },
                this
            )
        );
    },

}};
