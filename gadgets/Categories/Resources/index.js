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
                                return Jaws.gadgets.Categories.defines.no_results;
                            }
                        }
                    };

                    // ajax based?
                    if (!$(elSelect).data('prefetch')) {
                        options.ajax = {
                            delay: 250,
                            cache: true,

                            data: $.proxy(
                                function(params) {
                                    return {
                                        term: params.term || '',
                                        page: params.page || 0
                                    }
                                },
                                this
                            ),

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
                                                'term'  : params.data.term,
                                                'limit' : 10,
                                                'offset': params.data.page * 10,
                                                'count' : true
                                            }
                                        },
                                        function (response) {
                                            if (response.type == 'alert-success') {
                                                success({'results': response.data});
                                            } else {
                                                failure();
                                            }
                                        }
                                    );
                                },
                                this
                            ),

                            processResults: function (data, params) {
                                params.page = params.page || 0;
                                return {
                                    results: $.map(
                                        data.results.categories,
                                        function (obj) {
                                            obj.text = obj.title;
                                            return obj;
                                        }
                                    ),
                                    pagination: {
                                        more: (params.page + 1) * 10 < data.results.count
                                    }
                                };
                            }
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
