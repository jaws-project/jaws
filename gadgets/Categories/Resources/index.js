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
        $('[data-role=select2]').each(
            $.proxy(
                function(index, elSelect) {
                    let options = {
                        createTag: function (params) {
                            let term = $.trim(params.term);
                            if (term === '') {
                                return null;
                            }

                            return {
                                id: '__' + term + '__',
                                text: term
                            }
                        },
                        language: {
                            noResults: function() {
                                return jaws.Categories.Defines.no_results;
                            }
                        }
                    };

                    // ajax based?
                    if (!$(elSelect).data('prefetch')) {
                        options.ajax = {
                            transport: $.proxy(
                                function (params, success, failure) {
                                    this.gadget.ajax.callAsync(
                                        'getCategories',
                                        {
                                            'interface': {
                                                gadget: $(elSelect).data('gadget'),
                                                action: $(elSelect).data('action'),
                                                reference: 1
                                            },
                                            'options': {
                                                term: params.data.term
                                            }
                                        },
                                        function (response) {
                                            let data = [];
                                            if (response.type == 'alert-success') {
                                                $.each(response.data.categories, function (key, category) {
                                                    data.push({text: category.title, id: category.id});
                                                });
                                            } else {
                                                data.push({text: '', id: ''});
                                            }

                                            success(
                                                {
                                                    'results': data,
                                                    'pagination': {'more': false}
                                                }
                                            );
                                        }
                                    );
                                },
                                this
                            )
                        }
                    }

                    // initiate
                    $(elSelect).select2(options);
                },
                this
            )
        );
    },

}};
