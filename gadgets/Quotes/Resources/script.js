/**
 * Quotes Javascript actions
 *
 * @category    Ajax
 * @package     Quotes
 */

function Jaws_Gadget_Quotes() { return {
    // ASync callback method
    AjaxCallback : {
    },
}};

function Jaws_Gadget_Quotes_Action_quotes() {
    return {
        selectedQuote: 0,

        // ASync callback method
        AjaxCallback: {
            insertQuote: function(response) {
                if (response.type === 'alert-success') {
                    $('#quoteModal').modal('hide');
                    $('#quotes-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },
            updateQuote: function(response) {
                if (response.type === 'alert-success') {
                    $('#quoteModal').modal('hide');
                    $('#quotes-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },
            deleteQuote: function(response) {
                if (response.type === 'alert-success') {
                    $('#quotes-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
                }
            },
        },

        /**
         * save a quote
         */
        saveQuote: function() {
            let data = $.unserialize($('form#quotes-form').serialize());
            // 'category_quotes_0': $('select[name="category_quotes_0"]').find('option:selected').val()

            if (this.selectedQuote === 0) {
                this.ajax.callAsync('insertQuote', data);
            } else {
                data.id = this.selectedQuote;
                this.ajax.callAsync('updateQuote', data);
            }
        },

        /**
         * Edit a quote
         */
        editQuote: function(id) {
            this.selectedQuote = id;
            this.ajax.callAsync('getQuote', {'id': this.selectedQuote},
                function (response, status) {
                    if (response.type === 'alert-success') {
                        const quoteInfo = response.data;
                        $('#quotes-form').find('input,select,textarea').each(
                            function () {
                                if ($(this).is('select')) {
                                    if (quoteInfo[$(this).attr('name')] === true) {
                                        $(this).val('1');
                                    } else if (quoteInfo[$(this).attr('name')] === false) {
                                        $(this).val('0');
                                    } else {
                                        $(this).val(quoteInfo[$(this).attr('name')]);
                                    }
                                } else {
                                    $(this).val(quoteInfo[$(this).attr('name')]);
                                }
                            }
                        );

                        $('select[name="category_quotes_0"]').val(quoteInfo.category);

                        $('#datepicker_ftime_input').val(
                            (quoteInfo.ftime === 0) ? '' :
                                $.dateCalendar(
                                    Jaws.defines.preferences['calendar'], quoteInfo.ftime
                                ).format('YYYY/MM/DD')
                        );
                        $('#datepicker_ttime_input').val(
                            (quoteInfo.ttime === 0) ? '' :
                                $.dateCalendar(
                                    Jaws.defines.preferences['calendar'], quoteInfo.ttime
                                ).format('YYYY/MM/DD')
                        );

                        $('#quoteModal').modal('show');
                    }
                });
        },

        /**
         * Delete quote
         */
        deleteQuote: function(id) {
            if (confirm(Jaws.t('confirm_delete'))) {
                this.ajax.callAsync('deleteQuote', {'id': id});
            }
        },

        /**
         * Define the data to be displayed in the quotes datagrid
         */
        quotesDataSource: function (options, callback) {
            var columns = {
                'title': {
                    'label': Jaws.t('title'),
                    'property': 'title',
                    'width': '30%'
                },
                'category_title': {
                    'label': this.t('group'),
                    'property': 'category_title',
                    'width': '20%'
                },
                'classification': {
                    'label': this.t('classification'),
                    'property': 'classification',
                    'width': '20%'
                },
                'published': {
                    'label': Jaws.t('published'),
                    'property': 'published',
                    'width': '10%'
                },
                'updated': {
                    'label': Jaws.t('updatetime'),
                    'property': 'updated',
                    'sortable': true,
                    'width': '20%'
                },
            };

            var filters = $.unserialize($('#quotes-grid .datagrid-filters form').serialize());

            // set sort property & direction
            if (options.sortProperty) {
                columns[options.sortProperty].sortDirection = options.sortDirection;
            }
            columns = Object.values(columns);

            this.gadget.ajax.callAsync(
                'getQuotes', {
                    'offset': options.pageIndex * options.pageSize,
                    'limit': options.pageSize,
                    'sortDirection': options.sortDirection,
                    'sortBy': options.sortProperty,
                    'filters': filters
                },
                function(response, status, callOptions) {
                    var dataSource = {};
                    if (response.type === 'alert-success') {
                        callOptions.showMessage = false;

                        // processing end item index of page
                        options.offset = options.pageIndex*options.pageSize;
                        options.end = options.offset + options.pageSize;
                        options.end = (options.end > response['data'].total)? response['data'].total : options.end;
                        dataSource = {
                            'page': options.pageIndex,
                            'pages': Math.ceil(response['data'].total/options.pageSize),
                            'count': response['data'].total,
                            'start': options.offset + 1,
                            'end':   options.end,
                            'columns': columns,
                            'items': response['data'].records
                        };
                    } else {
                        dataSource = {
                            'page': 0,
                            'pages': 0,
                            'count': 0,
                            'start': 0,
                            'end':   0,
                            'columns': columns,
                            'items': {}
                        };
                    }
                    // pass the datasource back to the repeater
                    callback(dataSource);
                }
            );
        },

        /**
         * quotes Datagrid column renderer
         */
        quotesDGColumnRenderer: function (helpers, callback) {
            const column = helpers.columnAttr;
            const rowData = helpers.rowData;
            let customMarkup = '';

            switch (column) {
                case 'classification':
                    customMarkup = this.gadget.defines.classifications[rowData.classification];
                    break;
                case 'published':
                    customMarkup = Jaws.t('noo');
                    if (rowData.published === true) {
                        customMarkup = Jaws.t('yess');
                    }
                    break;
                case 'updated':
                    helpers.item.addClass('text-right');
                    helpers.item.css('direction', 'ltr');
                    customMarkup = rowData.updated > 0 ? $.dateCalendar(
                        Jaws.defines.preferences['calendar'], rowData.updated
                    ).format('YYYY/MM/DD HH:mm:ss') : '';
                    break;
                default:
                    customMarkup = helpers.item.text();
                    break;
            }

            helpers.item.html(customMarkup);
            callback();
        },

        /**
         * initiate quotes dataGrid
         */
        initiateQuoteDG: function() {
            var list_actions = {
                width: 50,
                items: [
                    {
                        name: 'edit',
                        html: '<span class="glyphicon glyphicon-pencil"></span> ' + Jaws.t('edit'),
                        clickAction: $.proxy(function (helpers, callback, e) {
                            e.preventDefault();
                            this.editQuote(helpers.rowData.id);
                            callback();
                        }, this)

                    },
                    {
                        name: 'delete',
                        html: '<span class="glyphicon glyphicon-trash"></span> ' + Jaws.t('delete'),
                        clickAction: $.proxy(function (helpers, callback, e) {
                            e.preventDefault();
                            this.deleteQuote(helpers.rowData.id);
                            callback();
                        }, this)

                    },
                ]
            };

            // initialize the repeater
            $('#quotes-grid').repeater({
                dataSource: $.proxy(this.quotesDataSource, this),
                list_actions: list_actions,
                list_columnRendered: $.proxy(this.quotesDGColumnRenderer, this),
                list_noItemsHTML: Jaws.t('notfound'),
                list_direction: $('.repeater-canvas').css('direction')
            });

            // monitor required events
            $( ".datagrid-filters select" ).change(function() {
                $('#quotes-grid').repeater('render', {clearInfinite: true,pageIncrement: null});
            });
            $( ".datagrid-filters input" ).keypress(function(e) {
                if (e.which === 13) {
                    $('#quotes-grid').repeater('render', {clearInfinite: true,pageIncrement: null});
                }
            });
            $("#quotes-grid button.btn-refresh").on('click', function (e) {
                $('#quotes-grid').repeater('render', {clearInfinite: true, pageIncrement: null});
            });
        },

        //------------------------------------------------------------------------------------------------------------------
        /**
         * initialize gadget actions
         */
        //------------------------------------------------------------------------------------------------------------------
        init: function (mainGadget, mainAction) {
            $('#btn-save-quote').on('click', $.proxy(function (e) {
                this.saveQuote();
            }, this));

            $('#quoteModal').on('hidden.bs.modal', $.proxy(function (e) {
                $('form#quotes-form')[0].reset();
                $('#quotation').val('');
                this.selectedQuote = 0;
            }, this));

            this.initiateQuoteDG();
        }
    }
};
