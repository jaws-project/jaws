/**
 * General Javascript methods
 *
 * @category    Ajax
 * @package     Core
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2004-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/*!
 * Extra plugins for jQuery
 */
jQuery.extend({
    formArray: function($elements, removeBlanks = false, checkValidity = true) {
        let result = {};

        if (checkValidity) {
            let allElementsValidate = true;
            $.each($elements, function(index, element) {
                if (element.checkValidity()) {
                    $(element).removeClass('is-invalid');
                } else {
                    allElementsValidate = false;
                    $(element).addClass('is-invalid');
                }
            });
            // return empty object if any element is invalidate
            if (!allElementsValidate) {
                return result;
            }
        }

        $.each($elements, function(index, element) {
            let input = {
                name: $(element).attr('name'),
                value: $(element).prop('type') == 'checkbox'? Number($(element).prop('checked')) : $(element).val()
            };

            if (['', null, undefined].includes(input.name) ||
                (removeBlanks && (input.value === undefined || input.value === null || input.value.length == 0))
            ) {
                return;
            }

            let way = (input.name.match(/(\w+)|(\[(\w+)?\])/g) || []).map(x=>x.replace(/^\[+|\]+$/g, ''));
            let last = way.pop();

            let property = way.reduce(function (obj, k, i, kk) {
                return obj[k] = obj[k] || [];
            }, result);

            if (last === '') {
                if (Array.isArray(property)) {
                    property.push(...input.value);
                } else {
                    property = [input.value];
                }
            } else if (property.hasOwnProperty(last)) {
                if (Array.isArray(property[last])) {
                    property[last].push(...input.value);
                } else {
                    property[last] = [property[last], input.value];
                }
            } else {
                property[last] = input.value;
            }
        });

        return result;
    },

    unserialize: function(str) {
        let result = {};
        str = decodeURIComponent((str || document.location.search).replace(/\+/gi, " ")).replace(/(^\?)/,'');
        $.each(
            str.split("&"),
            function(index, n) {
                n=n.split("=");
                switch($.type(result[n[0]])) {
                    case "undefined":
                        result[n[0]] = n[1];
                        break;
                    case "array":
                        result[n[0]].push(n[1]);
                        break;
                    default:
                        result[n[0]] = [result[n[0]]];
                        result[n[0]].push(n[1]);
                }
            }
        );

        return result;
    },

    /**
     * Takes an object and returns a FormData object
     *
     * @return  object  FormData
     */
    formData: function(data, objFormData, parentKey) {
        if (!(objFormData instanceof FormData)) {
            objFormData = new FormData();
        }

        if (data && typeof data === 'object' &&
            !(data instanceof Date) &&
            !(data instanceof File) &&
            !(data instanceof Blob)
        ) {
            if (Object.keys(data).length) {
                $.each(Object.keys(data),
                    $.proxy(
                        function(index, key) {
                            $.formData(data[key], objFormData, parentKey? `${parentKey}[${key}]` : key);
                        },
                        this
                    )
                );
            } else {
                objFormData.append(parentKey || 0, '');
            }
        } else {
            data = (data == null)? '' : data;
            objFormData.append(parentKey || 0, data);
        }

        return objFormData;
    },

    viewport: function() {
        return {
            get: function(element) {
                let width = $(element || document).innerWidth();
                return (width < 576) ? 'xs' : ((width < 768) ? 'sm' : ((width < 992) ? 'md' : ((width < 1200)? 'lg' : 'xl')));
            },
            is: function(breakPoint, element) {
                return breakPoint == this.get(element);
            }
        };
    }()
});

(function($) {
    /*
     * wait until condition met
    */
    $.until = function(condition, interval = 100, timeout = 5000) {
        const startTime = Date.now();
        return new Promise(function(resolve, reject) {
            const timer  = setInterval(
                function () {
                    if (condition()) {
                        clearInterval(timer);
                        resolve(true);
                    } else if ((Date.now() - startTime) > timeout) {
                        clearInterval(timer);
                        reject(false);
                    }
                },
                interval
            );
        });
    };

    /*
     * https://github.com/farhadi/html5sortable
    */
    var dragging, placeholders = $();
    $.fn.sortable = function(options) {
        var method = String(options);
        options = $.extend({
            connectWith: false
        }, options);
        return this.each(function() {
            if (/^enable|disable|destroy$/.test(method)) {
                var items = $(this).children($(this).data('items')).attr('draggable', method == 'enable');
                if (method == 'destroy') {
                    items.add(this).removeData('connectWith items')
                        .off('dragstart.h5s dragend.h5s selectstart.h5s dragover.h5s dragenter.h5s drop.h5s');
                }
                return;
            }
            var isHandle, index, items = $(this).children(options.items);
            var placeholder = $('<' + (/^ul|ol$/i.test(this.tagName) ? 'li' : 'div') + ' class="sortable-placeholder">');
            items.find(options.handle).mousedown(function() {
                isHandle = true;
            }).mouseup(function() {
                isHandle = false;
            });
            $(this).data('items', options.items)
            placeholders = placeholders.add(placeholder);
            if (options.connectWith) {
                $(options.connectWith).add(this).data('connectWith', options.connectWith);
            }
            items.attr('draggable', 'true').on('dragstart.h5s', function(ev) {
                if (options.handle && !isHandle) {
                    return false;
                }
                isHandle = false;
                ev.originalEvent.dataTransfer.setData("text", ev.target.id);
                ev.originalEvent.dataTransfer.effectAllowed = "move";
                dragging = $(this).addClass('sortable-dragging');
                items.parent().trigger('start', dragging);
            }).on('dragend.h5s', function() {
                dragging.removeClass('sortable-dragging').show();
                placeholders.detach();
                items.parent().trigger('stop', dragging);
            }).not('a[href], img').on('selectstart.h5s', function() {
                this.dragDrop && this.dragDrop();
                return false;
            }).end().add([this, placeholder]).on('dragover.h5s dragenter.h5s drop.h5s', function(e) {
                if (!items.is(dragging) && options.connectWith !== $(dragging).parent().data('connectWith')) {
                    return true;
                }
                if (e.type == 'drop') {
                    e.stopPropagation();
                    placeholders.filter(':visible').after(dragging);
                    return false;
                }
                e.preventDefault();
                e.originalEvent.dataTransfer.dropEffect = 'move';
                if (items.is(this)) {
                    if (options.forcePlaceholderSize) {
                        placeholder.height(dragging.outerHeight());
                    }
                    dragging.hide();
                    $(this)[placeholder.index() < $(this).index() ? 'after' : 'before'](placeholder);
                    placeholders.not(placeholder).detach();
                } else if (!placeholders.is(this) && !$(this).children(options.items).length) {
                    placeholders.detach();
                    $(this).append(placeholder);
                }
                return false;
            });
        });
    };

    /*
     * runtime load script
    */
    var loadedScripts = {};
    jQuery.loadScript = function(path, fn, obj) {
        if (loadedScripts[path]) {
            fn.call(obj);
            return;
        }

        loadingScript = $('head script[src="'+path+'"]');
        if (loadingScript.length) {
            loadingScript.on('load readystatechange', function() {
                fn.call(obj);
            });
        } else {
            $('<script>').appendTo('head').on('load readystatechange', function() {
                $(this).off('load readystatechange');
                loadedScripts[path] = true;
                fn.call(obj);
            }).on('error', function() {
                //on error
            }).prop('async', true).attr('src', path);
        }
    };

})(jQuery);


(function($) {
    /*
     * https://github.com/aagouda/Bootstrap-DrillDownMenu
    */
    $.fn.drilldown = function(options) {
        //set default options
        var defaults = {
            parent_class       : 'dd-parent',
            parent_class_link  : 'dd-parent-a',
            active_class       : 'active',
            breadcrumb_wrapper : 'breadcrumbwrapper',
            speed              : 'slow',
            submenu_icon_class : 'glyphicon glyphicon-chevron-right',
            show_submenu_icon  : true,
            show_end_nodes     : true // drill to final empty nodes
        };

        //call in the default options
        var options = $.extend(defaults, options);

        //history.replaceState(-1, "On page load state, record initial state", "");

        //act upon the element that is passed into the design
        return this.each(function(index) {
            var $wrapper = $(this);

            $(window).on('popstate', function(event) {
                //console.log(history.state);
            });

            var $ddObj = $('.drilldown-menu', $wrapper).first();
            setUpDrilldown();

            let $li = $('li.active', $ddObj).first();
            // hide menu if not in root and not found selected menu
            if ($li.length == 0 && location.pathname.match(/^.*\//)[0] != Jaws.defines.base) {
                return false;
            }

            // active all parents of selected menu
            while ($('> a', $li).length != 0) {
                $('> a', $li).addClass(defaults.active_class);
                $li = $li.parent().parent();
            }

            resetDrilldown($wrapper, $ddObj);

            $(window).resize(function() {
                resizeDrilldown($ddObj, $wrapper);
            });

            $('li a', $ddObj).click(function(e) {
                $link = this;
                $activeLi = $(this).parent('li').stop();
                $siblingsLi = $($activeLi).siblings();

                // DrillDown action
                if ($('> ul', $activeLi).length || defaults.show_end_nodes) {
                    // push state to history
                    //$wrapper.indexWrapper
                    //history.pushState(1, "", "");
                    actionDrillDown($activeLi, $wrapper, $ddObj);
                }

                // scroll to top on click
                $($wrapper, $ddObj).scrollTop(0);

                // Prevent browsing to link if has child links
                if ($(this).next('ul').length > 0) {
                    e.preventDefault();
                    e.stopPropagation();
                    $($link).trigger('drilldown.parentclick');
                } else {
                    //$($link).trigger('click');
                    $($link).addClass(defaults.active_class);
                }

                $($link).trigger('drilldown.linklclick');
            });

            // Breadcrumbs
            $('.'+defaults.breadcrumb_wrapper, $wrapper).on('click', 'a', function(e) {
                // Get link index
                var linkIndex = $('.'+defaults.breadcrumb_wrapper+' a', $wrapper).index(this);
                if (linkIndex == 0) {
                    $('a', $ddObj).removeClass(defaults.active_class);
                } else {
                    // Select equivalent active link
                    linkIndex = linkIndex - 1;
                    $('a.'+defaults.active_class+':gt('+linkIndex+')', $ddObj).removeClass(
                        defaults.active_class
                    );
                }

                resetDrilldown($wrapper, $ddObj);
                $($ddObj).trigger('drilldown.linklclick');

                if ($(e.currentTarget).attr('href').blank()) {
                    e.preventDefault();
                }
            });

            // Set up accordion
            function setUpDrilldown() {
                if (defaults.show_submenu_icon) {
                    $submenu_icon = '<span class="'+defaults.submenu_icon_class+'"></span>';
                } else {
                    $submenu_icon = '';
                }

                // Set sub menu width and offset
                $('li',$ddObj).each(function() {
                    if($('> ul',this).length){
                        $(this).addClass(defaults.parent_class);
                        $('> a',this).addClass(defaults.parent_class_link).append($submenu_icon);
                    }
                });

                // Add css class
                $('ul',$wrapper).each(function() {
                    $('li:last',this).addClass('last');
                });

                $('> ul > li:last',$wrapper).addClass('last');

            }
        });

        // Drill Down
        function actionDrillDown(element, $wrapper, obj) {
            // breadcrumb header wrapper
            let $header = $('.'+defaults.breadcrumb_wrapper, $wrapper);

            if ($('ul li', $header).length > 1) {
                let lastBreadcrumb = $('ul li:last-child', $header).text();
                $('ul li:last-child', $header).remove();
                $('ul', $header).append('<li><a href=""><span>'+lastBreadcrumb+'</span></a></li>');
            }
            let newBreadcrumb = $('> a',element).text();
            $('ul',$header).append('<li class="active"><span>'+newBreadcrumb+'</span></li>');

            // declare child link
            var activeLink = $('> a',element);

            // add active class to link
            $(activeLink).addClass(defaults.active_class);

            // Find all sibling items & hide
            var $siblingsLi = $(element).siblings();
            $($siblingsLi).hide();

            $(activeLink).hide();
            $('> ul li', element).show();
            $('> ul',element)
                .css({'overflow-x':'hidden', 'white-space':'nowrap'})
                .show()
                .animate({"margin-right": 0}, defaults.speed);
        }

        // Reset accordion using active links
        function resetDrilldown(wrapper, $obj) {
            var $header = $('.'+defaults.breadcrumb_wrapper, wrapper);
            $('ul li', $header).not(':first').remove();
            $('li', $obj).show();
            $('a', $obj).show();
            var menuWidth = $obj.outerWidth(true);
            $('ul', $obj).hide().css('margin-right', menuWidth + 'px');

            $obj.show();
            var activeObj = $obj;
            $('a.'+defaults.active_class, $obj).each(function(i) {
                var $activeLi = $(this).parent('li').stop();
                actionDrillDown($activeLi, wrapper, $obj);
                activeObj = $(this).parent('li');
            });

        }

        /**
         *
         */
        function resizeDrilldown($ddObj, $wrapper) {
            // set wrapper to auto width to force resize
            $($wrapper).css({width: 'auto'});
            $($ddObj).css({width: 'auto'});
            resetDrilldown($wrapper, $ddObj);
        }

    };
})(jQuery);

/**
 * Ajax base class
 *
 * @param   string  gadget          Gadget Name
 * @param   object  callback        Callback functions
 * @param   object  defaultOptions  Default options(baseScript, ...)
 * @return  void
 */
function JawsAjax(gadget, callbackFunctions, callbackObject, defaultOptions)
{
    this.baseGadget = null;
    this.baseAction = null;
    this.defaultOptions = defaultOptions || {};
    this.callbackObject = callbackObject || Jaws_Gadget.getInstance(gadget);
    this.callbackFunctions = callbackFunctions;

    this.loadingMessage = '';
    var reqValues = $('meta[name=application-name]').attr('content').split(':');
    this.mainRequest = {'base': reqValues[0], 'gadget': reqValues[1], 'action': reqValues[2]};

    // owner gadget
    this.gadget = gadget;

    // base script
    this.baseScript = this.defaultOptions.hasOwnProperty('baseScript')?
        this.defaultOptions.baseScript : this.mainRequest['base'];

    // default status of showing response message
    if (!this.defaultOptions.hasOwnProperty('showMessage')) {
        this.defaultOptions.showMessage = true;
    }

    /**
     * Performs Ajax request
     *
     * @param   string      action      Gadget action name
     * @param   object      data        Parameters passed to the function
     * @param   function    done        Callback function
     * @param   object      callOptions
     * @param   function    progress    Progress callback function
     * @return  void
     */
    this.call = function (action, data, done, callOptions, progress) {
        var options = {};
        var gadget, baseScript;

        callOptions = callOptions || {};
        // response message container
        options.interface = {
            'gadget': this.baseGadget? this.baseGadget : this.mainRequest['gadget'],
            'action': this.baseAction? this.baseAction : this.mainRequest['action']
        }

        // loading container
        if (!callOptions.hasOwnProperty('loading_container')) {
            callOptions.loading_container = $(
                '[data-loading-container="' + (options.interface.gadget + '.' + options.interface.action).toLowerCase() + '"]'
            );
        }

        gadget = callOptions.hasOwnProperty('gadget')? callOptions.gadget : this.gadget;
        baseScript = callOptions.hasOwnProperty('baseScript')? callOptions.baseScript : this.baseScript;

        options.done = done? $.proxy(done, this.callbackObject) : undefined;
        // url
        options.url  = baseScript + '?reqGadget=' + gadget + '&reqAction=' + action;
        if (!callOptions.hasOwnProperty('restype')) {
            callOptions.restype = 'json';
        }
        options.url+= '&restype=' + callOptions.restype;

        options.type = 'POST';
        options.async  = callOptions.hasOwnProperty('async')? callOptions.async : true;
        options.timeout = 10 * 60 * 1000; /* 10 minutes */
        options.callOptions = callOptions;
        // prevent auto redirect, we handle it manually if required
        options.headers = {'Auto-Redirects': '0'}

        options.xhr = function() {
            let xhr = $.ajaxSettings.xhr();
            switch (callOptions.restype) {
                case 'json':
                case 'text':
                case 'print':
                    xhr.responseType = '';
                    options.dataType = 'text';
                    break;

                default:
                    xhr.responseType = 'blob';
                    options.dataType = 'binary';
            }

            if (progress) {
                xhr.upload.addEventListener(
                    'progress',
                    function(evt) {
                        if (evt.lengthComputable) {
                            progress.call(this.callbackObject, callOptions.loading_container, evt.loaded, evt.total);
                        }
                    },
                    false
                );
            }

            if (callOptions.requestType == 'http') {
                // Copy the browser's native setRequestHeader method
                let setRequestHeader = xhr.setRequestHeader;
                // Replace with a wrapper
                xhr.setRequestHeader = function(name, value) {
                    // Ignore the X-Requested-With header
                    if (name == 'X-Requested-With') return;
                    // Otherwise call the native setRequestHeader method
                    setRequestHeader.call(this, name, value);
                }
            }

            return xhr;
        }

        if (!(data instanceof FormData)) {
            // convert to FormData
            data = $.formData(data);
        }
        options.processData = false;
        options.contentType = false;
        options.data = data;

        options.beforeSend = this.onSend.bind(this, options);
        options.success = this.onSuccess.bind(this, options);
        options.error = this.onError.bind(this, options);
        if (options.async) {
            options.complete = this.onComplete.bind(this, options);
        }
        // send ajax request
        let result = $.ajax(options);

        // if sync
        if (!options.async) {
            return this.onComplete.call(this, options, result, '');
        }
    };

    this.onSend = function (reqOptions) {
        // start show loading indicator
        this.callbackObject.gadget.loading.show(reqOptions.callOptions.loading_container);
    };

    this.onSuccess = function (reqOptions, data, textStatus, jqXHR) {
        // file
        let disposition = jqXHR.getResponseHeader('content-disposition');
        if (disposition || reqOptions.xhr().responseType == 'blob') {
            let filename = '';
            let filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            let matches = filenameRegex.exec(disposition);
            if (matches != null && matches[1]) {
                filename = matches[1].replace(/['"]/g, '');
            }
            let url = URL.createObjectURL(data); 
            let link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.append(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        }

        // print
        if (reqOptions.callOptions.restype == 'print') {
            let $print = $('<iframe>').appendTo('body');
            let doc = $print[0].contentWindow.document;
            doc.open();
            doc.write(data);
            doc.close();
            $print.on('load', function(el) {
                $print.contents().find('html').get(0).ownerDocument.defaultView.print();
                $print.remove();
            });
        }
    };

    this.onError = function (reqOptions, jqXHR, textStatus, errorThrown) {
        // TODO: alert error message
    };

    this.onComplete = function (reqOptions, jqXHR, textStatus) {
        // hide loading
        this.callbackObject.gadget.loading.hide(reqOptions.callOptions.loading_container);

        let response = jqXHR.responseText;
        if (reqOptions.callOptions.restype == 'json') {
            // temporary fix json parse big numbers
            response = eval('(' + response.replace (/:(\d{15,})([,\}])/g, ':"$1"$2') + ')');
        }
        // ajax redirect
        if ([301, 302].indexOf(jqXHR.status) != -1) {
            window.location = response;
            return;
        }

        // call inline user define function
        if (reqOptions.done) {
            reqOptions.done(response, jqXHR.status, reqOptions.callOptions);
        }

        if (this.callbackFunctions && this.callbackFunctions[reqOptions.action]) {
            this.callbackFunctions[reqOptions.action].call(
                this.callbackObject,
                response,
                jqXHR.status,
                reqOptions.callOptions
            );
        }

        if (reqOptions.callOptions.hasOwnProperty('showMessage')) {
            if (reqOptions.callOptions.showMessage) {
                this.callbackObject.gadget.message.show(response, reqOptions.interface);
            }
        } else if (this.defaultOptions.showMessage) {
            this.callbackObject.gadget.message.show(response, reqOptions.interface);
        }

        return response;
    };

}

/**
 * Message base class
 *
 * @param   object  objOwner    Owner object(gadget or action object)
 * @return  void
 */
function JawsMessage($owner)
{
    this.alerts = {
        'alert-danger': {
            'class' : 'alert-danger', 'type' : 'error', 'icon': 'fa-solid fa-circle-xmark'
        },
        'alert-warning': {
            'class' : 'alert-warning', 'type' : 'warning', 'icon': 'fa-solid fa-triangle-exclamation'
        },
        'alert-success': {
            'class' : 'alert-success', 'type' : 'success', 'icon': 'fa-solid fa-circle-check'
        },
        'alert-info': {
            'class' : 'alert-info', 'type' : 'info', 'icon': 'fa-solid fa-circle-info'
        }
    };
    this.owner = $owner;

    /**
     * show response message
     *
     * @param   object  message     Jaws Response message
     * @param   object  $interface  Include (gadget, action, ...)
     * @return  void
     */
    this.show = async function (message, $interface = {}) {
        if (!message || !$.trim(message.text) || !message.type) {
            return;
        }

        // if $interface is empty
        if (!Object.keys($interface).length) {
            $interface = {
                'gadget': Jaws.defines.mainGadget,
                'action': Jaws.defines.mainAction
            }
        }
        $interface.id = ($interface.gadget + '-' + $interface.action + '-response-message').toLowerCase();

        if (Jaws.defines.script == 'index') {
            $container = $('#' + $interface.id);
            if (!$container.length) {
                // clone toast html elements
                $('#jaws-response-container').append(
                    $('#jaws-response-container').find('#jaws-response-template').html()
                ).find('.toast').last().attr('id', $interface.id);
                $container = $('#' + $interface.id);
            }
            this.$container = $container;

            // hide message if already shown
            bootstrap.Toast.getOrCreateInstance(this.$container).hide();
            await $.until($.proxy(function() {
                return !(bootstrap.Toast.getOrCreateInstance(this).isShown());
            }, this.$container));

            // define on-hidden event
            this.$container.off().on('hidden.bs.toast', $.proxy(
                function(event) {
                    $(event.target).find('[role="response.type"]').removeClass(['gadget-response-message', this.message.type]);
                    $(event.target).find('[role="response.icon"]').removeClass(this.alerts[this.message.type].icon);
                },
                this
            ));

            this.message = message;
            this.$container.find('[role="response.title"]').html(this.owner.gadget.t('title'));
            this.$container.find('[role="response.text"]').html(message.text);
            this.$container.find('[role="response.type"]').addClass(['gadget-response-message', message.type]);
            this.$container.find('[role="response.icon"]').addClass(this.alerts[message.type].icon);
            bootstrap.Toast.getOrCreateInstance(this.$container).show();
        } else {
            toastr.options = {
                closeButton: true,
                newestOnTop: true,
                positionClass: 'toast-top-end',
                preventDuplicates: true,
                onclick: null
            };
            toastr[this.alerts[message.type].type](message.text, this.owner.gadget.t('title'));
        }
    }

}

/**
 * Loading base class
 *
 * @param   object  $owner  Owner object(gadget or action object)
 * @return  void
 */
function JawsLoading($owner)
{
    this.owner = $owner;

    /**
     * show loading
     *
     * @param   object  container   jQuery DOM element message container
     * @return  void
     */
    this.show = function ($container) {
        if (!$container.length) {
            // if $container not defined for notfound
            $container = $('[data-loading-container="' + (Jaws.defines.mainGadget + '.' + Jaws.defines.mainAction).toLowerCase() + '"]');
            if (!$container.length) {
                return;
            }
        }

        if (!$container.find('[role="loading"]').length) {
            // clone loading html template
            $container.append(
                $('body').find('#jaws-loading-template').html()
            ).find('[role="loading.text"]').html(
                this.owner.gadget.defines.loadingMessage || Jaws.defines.loadingMessage || 'Loading...'
            );
        }

        $container.find('[role="loading"]').stop(true, true).css('display', 'flex').hide().fadeIn();
    }

    /**
     * hide loading
     *
     * @param   object  container   jQuery DOM object message container
     * @return  void
     */
    this.hide = function ($container) {
        if (!$container.length) {
            // if $container not defined for notfound
            $container = $('[data-loading-container="' + (Jaws.defines.mainGadget + '.' + Jaws.defines.mainAction).toLowerCase() + '"]');
            if (!$container.length) {
                return;
            }
        }
        if (!$container.find('[role="loading"]').length) {
            return;
        }

        $container.find('[role="loading"]').hide();
    }

}

/**
 * Jaws HTML5 wrapper of local storage
 *
 * @param   string  gadget  Gadget Name
 * @return  void
 */
function JawsStorage(gadget)
{
    this.gadget = gadget;
    this.html5Support = 'localStorage' in window;
    if (this.html5Support) {
        this.storage = localStorage;
    } else {
        this.storage = window.Cookie;
    }

    /**
     * Updates the storage key value
     *
     * @param   string  key     Key name
     * @param   mixed   value   Key value
     * @return  void
     */
    this.update = function (key, value, section) {
        key = (section? section : this.gadget) + '_' + key;
        if (this.html5Support) {
            this.storage.setItem(key, JSON.stringify(value));
        } else {
            this.storage.write(key, JSON.stringify(value));
        }
    },

    /**
     * fetches value of the storage key
     *
     * @param   string  key     Key name
     * @return  mixed   Stored value of key
     */
    this.fetch = function (key, section) {
        key = (section? section : this.gadget) + '_' + key;
        if (this.html5Support) {
            return $.parseJSON(this.storage.getItem(key));
        } else {
            return $.parseJSON(this.storage.read(key));
        }
    },

    /**
     * deletes a storage key
     *
     * @param   string  key     Key name
     * @return  void
     */
    this.delete = function (key, section) {
        key = (section? section : this.gadget) + '_' + key;
        if (this.html5Support) {
            this.storage.removeItem(key);
        } else {
            this.storage.dispose(key);
        }
    }

};

/**
 * Repaints a combo
 */
function paintCombo(combo, oddColor, evenColor)
{
    if (evenColor == undefined) {
        evenColor = '#fff';
    }

    var color = evenColor;
    for(var i=0; i<combo.length; i++) {
        combo.options[i].style.backgroundColor = color;;
        if (i % 2 == 0) {
            color = oddColor;
        } else {
            color = evenColor;
        }
    }
}

/**
 * TinyMCE file picker callback
 */
function tinymce_file_picker_callback(callback, value, meta)
{
    var browser = '';
    switch (meta.filetype) {
        case 'media':
            browser = Jaws.defines.editorMediaBrowser || '';
            break;
        case 'image':
            browser = Jaws.defines.editorImageBrowser || '';
            break;
        case 'file':
            browser = Jaws.defines.editorFileBrowser || '';
            break;
    }

    if (browser != '') {
        tinyMCE.activeEditor.windowManager.open({
                url: browser,
                title: 'Jaws File Browser',
                width: 640,
                height: 480,
                resizable: 'yes',
                scrollbars: 'yes',
                inline: 'yes',
                close_previous: 'no'
            }, {
                oninsert: function(url, title, desc, height, width) {
                    switch (meta.filetype) {
                        case 'media':
                            callback(url, {});
                            break;

                        case 'image':
                            callback(url, {'alt': title});
                            break;

                        case 'file':
                            callback(url, {'title': title, 'text': desc});
                            break;

                        default:
                            callback(url, {'text': title, 'title': title, 'alt': desc});
                    }
                }
            }
        );
    }

    return false;
}

/**
 * Prepares the editor with basic data
 */
function initEditor(selector)
{
    var objEditor = $(selector);
    var editorType = objEditor.data('editor') || Jaws.defines.editor || 'textarea';

    switch(editorType) {
        case 'ckeditor':
        case 'CKEditor':
            $.loadScript('libraries/ckeditor/ckeditor.js', function() {
                objEditor.ckeditor({
                    'baseHref': $('base').attr('href'),
                    'contentsLangDirection': objEditor.data('direction') || Jaws.defines.direction || 'ltr',
                    'language': objEditor.data('language') || Jaws.defines.language || 'en',
                    'AutoDetectLanguage': false,
                    'skin': 'moono',
                    'theme': 'default',
                    'readOnly': objEditor.data('readonly') == '1',
                    'resize_enabled': objEditor.data('resizable') == '1',
                    'toolbar': Jaws.defines.editorToolbar,
                    'extraPlugins': Jaws.defines.editorPlugins,
                    'filebrowserBrowseUrl': Jaws.defines.editorFileBrowser || '',
                    'filebrowserImageBrowseUrl': Jaws.defines.editorImageBrowser || '',
                    'filebrowserFlashBrowseUrl': Jaws.defines.editorMediaBrowser || '',
                    'removePlugins': '',
                    'autoParagraph': false,
                    'indentUnit': 'em',
                    'indentOffset': '1'
                });
            });
            break;

        case 'tinymce':
        case 'TinyMCE':
            $.loadScript('libraries/tinymce/tinymce.min.js', function() {
                // find and remove old tinymce editor instance
                tinyMCE.editors.forEach(function(editor, index) {
                    if ($(editor.targetElm).is(selector)) {
                        editor.remove();
                    }
                });

                setTimeout(
                    function() {
                        objEditor.tinymce({
                            'document_base_url': '',
                            'directionality': objEditor.data('direction') || Jaws.defines.direction || 'ltr',
                            'language': objEditor.data('language') || Jaws.defines.language || 'en',
                            'theme': 'modern',
                            'plugins': Jaws.defines.editorPlugins,
                            'toolbar1': Jaws.defines.editorToolbar,
                            'toolbar2': '',
                            'toolbar_items_size': 'small',
                            'template_external_list_url': 'libraries/tinymce/templates.js',
                            'theme_modern_toolbar_location': 'top',
                            'theme_modern_toolbar_align': 'center',
                            'theme_modern_path_location': 'bottom',
                            'theme_modern_resizing': true,
                            'theme_modern_resize_horizontal': false,
                            'tab_focus': ':prev,:next',
                            'dialog_type': 'window',
                            'entity_encoding':    'raw',
                            'relative_urls':      true,
                            'remove_script_host': false,
                            'force_p_newlines':   true,
                            'force_br_newlines':  false,
                            'remove_linebreaks':  true,
                            'nowrap':             false,
                            'automatic_uploads':  false,
                            'convert_newlines_to_brs': false,
                            'apply_source_formatting': true,
                            'file_picker_types': 'file image media',
                            'file_picker_callback': tinymce_file_picker_callback,
                            'extended_valid_elements': 'iframe[class|id|marginheight|marginwidth|align|frameborder=0|scrolling|align|name|src|height|width]',
                            'invalid_elements': '',
                            'menubar': false // must enabled for admin side
                        });
                    },
                    1000
                );
            });
            break;

        default:
            break;
    }
}

/**
 * Javascript blank string prototype
 */
String.prototype.blank = function() {
    return /^\s*$/.test(this);
};

/**
 * Javascript htmlspecialchars
 *
 * @see https://github.com/hirak/phpjs/blob/master/functions/strings/htmlspecialchars.js
 */
String.prototype.filter = function(quote_style, charset, double_encode) {
    var optTemp = 0,
        i = 0,
        noquotes = false;

    if (typeof quote_style === 'undefined' || quote_style === null) {
        quote_style = 2;
    }
    string = this.toString();
    if (double_encode !== false) {
        // Put this first to avoid double-encoding
        string = string.replace(/&/g, '&amp;');
    }
    string = string.replace(/</g, '&lt;').replace(/>/g, '&gt;');

    var OPTS = {
        'ENT_NOQUOTES'          : 0,
        'ENT_HTML_QUOTE_SINGLE' : 1,
        'ENT_HTML_QUOTE_DOUBLE' : 2,
        'ENT_COMPAT'            : 2,
        'ENT_QUOTES'            : 3,
        'ENT_IGNORE'            : 4
    };
    if (quote_style === 0) {
        noquotes = true;
    }
    if (typeof quote_style !== 'number') {
        // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i = 0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'ENT_IGNORE' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            } else if (OPTS[quote_style[i]]) {
                optTemp = optTemp | OPTS[quote_style[i]];
            }
        }
        quote_style = optTemp;
    }
    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
        string = string.replace(/'/g, '&#039;');
    }
    if (!noquotes) {
        string = string.replace(/"/g, '&quot;');
    }

    return string;
}

/**
 * Javascript htmlspecialchars_decode
 *
 * @see https://github.com/hirak/phpjs/blob/master/functions/strings/htmlspecialchars_decode.js
 */
String.prototype.defilter = function(quote_style) {
    var optTemp = 0,
        i = 0,
        noquotes = false;

    if (typeof quote_style === 'undefined') {
        quote_style = 2;
    }
    string = this.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
    var OPTS = {
        'ENT_NOQUOTES'          : 0,
        'ENT_HTML_QUOTE_SINGLE' : 1,
        'ENT_HTML_QUOTE_DOUBLE' : 2,
        'ENT_COMPAT'            : 2,
        'ENT_QUOTES'            : 3,
        'ENT_IGNORE'            : 4
    };
    if (quote_style === 0) {
        noquotes = true;
    }
    if (typeof quote_style !== 'number') {
        // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i = 0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            } else if (OPTS[quote_style[i]]) {
                optTemp = optTemp | OPTS[quote_style[i]];
            }
        }
        quote_style = optTemp;
    }
    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
        string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
        // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
    }
    if (!noquotes) {
        string = string.replace(/&quot;/g, '"');
    }
    // Put this in last place to avoid escape being double-decoded
    string = string.replace(/&amp;/g, '&');

    return string;
};

/**
 * Implement Object.values for older browsers
 */
if (!Object.values) {
    Object.values = function(obj) {
        return $.map(obj, function(value, index) {
            return value;
        });
    }
}

/**
 * Reset a (piwi)datagrid:
 *  - Clean all data
 *  - Set the new data
 *  - Repaint
 */
function resetGrid(name, data, rowsSize)
{
    $('#'+name)[0].reset();
    $('#'+name)[0].fillWithArray(data);
    if (rowsSize != undefined) {
        $('#'+name)[0].rowsSize = rowsSize;
    }
    $('#'+name)[0].updatePageCounter();
    $('#'+name)[0].repaint();
}

//Which row selected in DataGrid
var selectedRows = [];
var selectedRowsColor = [];

/**
 * Selects a (piwi)datagrid row
 */
function selectGridRow(name, rowElement)
{
    if (selectedRows[name]) {
        if (typeof(selectedRows[name]) == 'object') {
            selectedRows[name].style.backgroundColor = selectedRowsColor[name];
        } else {
            $(selectedRows[name]).style.backgroundColor = selectedRowsColor[name];
        }
    }

    if (typeof(rowElement) == 'object') {
        selectedRowsColor[name] = rowElement.style.backgroundColor;
        rowElement.style.backgroundColor = '#ffffcc';
    } else {
        selectedRowsColor[name] = $(rowElement).style.backgroundColor;
        $(rowElement).style.backgroundColor = '#ffffcc';
    }

    selectedRows[name] = rowElement;
}

/**
 * Unselect a (piwi)datagrid row
 *
 */
function unselectGridRow(name)
{
    if (selectedRows[name]) {
        if (typeof(selectedRows[name]) == 'object') {
            selectedRows[name].style.backgroundColor = selectedRowsColor[name];
        } else {
            $(selectedRows[name]).style.backgroundColor = selectedRowsColor[name];
        }
    }

    selectedRows[name] = null;
    selectedRowsColor[name] = null;
}

/**
 * Class JawsDatagrid
 */
var JawsDataGrid = {

    /**
     * Get the first Values and prepares the datagrid
     */
    getFirstValues: function() {
        var result;
        var firstValues = $('#' + this.name)[0].getFirstPagerValues();
        var objGadget  = $('#' + this.name)[0].objectName;
        if (objGadget instanceof JawsAjax) {
            result = objGadget.call('getData', [firstValues, $('#' + this.name)[0].id], false, {'async': false});
        } else {
            result = objGadget.ajax.call('getData', [firstValues, $('#' + this.name)[0].id], false, {'async': false});
        }
        resetGrid(this.name, result);
        $('#' + this.name)[0].firstPage();
    },

    /**
     * Get the previous Values and prepares the datagrid
     */
    getPreviousValues: function() {
        var result;
        var previousValues = $('#' + this.name)[0].getPreviousPagerValues();
        var objGadget  = $('#' + this.name)[0].objectName;
        if (objGadget instanceof JawsAjax) {
            result = objGadget.call('getData', [previousValues, $('#' + this.name)[0].id], false, {'async': false});
        } else {
            result = objGadget.ajax.call('getData', [previousValues, $('#' + this.name)[0].id], false, {'async': false});
        }

        resetGrid(this.name, result);
        $('#' + this.name)[0].previousPage();
    },

    /**
     * Get the next Values and prepares the datagrid
     */
    getNextValues: function() {
        var result;
        var nextValues     = $('#' + this.name)[0].getNextPagerValues();
        var objGadget  = $('#' + this.name)[0].objectName;
        if (objGadget instanceof JawsAjax) {
            result = objGadget.call('getData', [nextValues, $('#' + this.name)[0].id], false, {'async': false});
        } else {
            result = objGadget.ajax.call('getData', [nextValues, $('#' + this.name)[0].id], false, {'async': false});
        }
        
        resetGrid(this.name, result);
        $('#' + this.name)[0].nextPage();
    },

    /**
     * Get the last Values and prepares the datagrid
     */
    getLastValues: function() {
        var result;
        var lastValues = $('#' + this.name)[0].getLastPagerValues();
        var objGadget  = $('#' + this.name)[0].objectName;
        if (objGadget instanceof JawsAjax) {
            result = objGadget.call('getData', [lastValues, $('#' + this.name)[0].id], false, {'async': false});
        } else {
            result = objGadget.ajax.call('getData', [lastValues, $('#' + this.name)[0].id], false, {'async': false});
        }

        resetGrid(this.name, result);
        $('#' + this.name)[0].lastPage();
    },

    /**
     * Only retrieves information with the current page the pager has and prepares the datagrid
     */
    getData: function() {
        var result;
        var currentPage = $('#' + this.name)[0].getCurrentPage();
        var objGadget  = $('#' + this.name)[0].objectName;
        if (objGadget instanceof JawsAjax) {
            result = objGadget.call('getData', [currentPage, $('#' + this.name)[0].id], false, {'async': false});
        } else {
            result = objGadget.ajax.call('getData', [currentPage, $('#' + this.name)[0].id], false, {'async': false});
        }

        resetGrid(this.name, result);
    }
};

/**
 * Prepares the datagrid with basic data
 */
function initDataGrid(name, objectName, dataFunc)
{
    if ($('#'+name)[0] == undefined || objectName == undefined) {
        return true;
    }

    $('#'+name)[0].objectName = objectName;
    if (dataFunc == undefined) {
        JawsDataGrid.name = name;
        $('#' + name + '_pagerFirstAnchor')[0].onclick = function() {
            JawsDataGrid.getFirstValues();
        };

        $('#' + name + '_pagerPreviousAnchor')[0].onclick = function() {
            JawsDataGrid.getPreviousValues();
        };

        $('#' + name + '_pagerNextAnchor')[0].onclick = function() {
                JawsDataGrid.getNextValues();
        };

        $('#' + name + '_pagerLastAnchor')[0].onclick = function() {
                JawsDataGrid.getLastValues();
        };

        $('#'+name)[0].onLoadingData = showWorkingNotification;
        $('#'+name)[0].onLoadedData = hideWorkingNotification;
        getDG();
    } else {
        $('#'+name)[0].dataFunc = dataFunc;

        $('#' + name + '_pagerFirstAnchor')[0].onclick = function() {
            var offset = $('#'+name)[0].getFirstPagerValues();
            getDG(name, offset);
            $('#'+name)[0].firstPage();
        };

        $('#' + name + '_pagerPreviousAnchor')[0].onclick = function() {
            var offset = $('#'+name)[0].getPreviousPagerValues();
            getDG(name, offset);
            $('#'+name)[0].previousPage();
        };

        $('#' + name + '_pagerNextAnchor')[0].onclick = function() {
            var offset = $('#'+name)[0].getNextPagerValues();
            getDG(name, offset);
            $('#'+name)[0].nextPage();
        };

        $('#' + name + '_pagerLastAnchor')[0].onclick = function() {
            var offset = $('#'+name)[0].getLastPagerValues();
            getDG(name, offset);
            $('#'+name)[0].lastPage();
        };

        $('#'+name)[0].onLoadingData = showWorkingNotification;
        $('#'+name)[0].onLoadedData = hideWorkingNotification;
        getDG(name);
    }
}

/**
 * Fast method to retrieve datagrid data
 */
function getDG(name, offset, reset)
{
    if (name == undefined) {
        JawsDataGrid.getData();
    } else {
        dataFunc = eval($('#'+name)[0].dataFunc);

        if (offset == undefined) {
            var offset = $('#'+name)[0].getCurrentPage();
        }

        reset = (reset == true) || ($('#'+name)[0].rowsSize == 0);
        var objGadget  = $('#'+name)[0].objectName;
        if (objGadget instanceof JawsAjax) {
            dataFunc(name, offset, reset);
        } else {
            dataFunc.call(objGadget, name, offset, reset);
        }

        if (reset && offset == undefined) {
            $('#'+name)[0].setCurrentPage(0);
        }
    }
}


/**
 * Changes the text of a button with a stock
 */
function changeButtonText(button, message)
{
    var buttonInner = button.innerHTML.substr(0, button.innerHTML.indexOf("&nbsp;"));
    button.innerHTML = buttonInner + "&nbsp;" + message;
    button.value = message;
}

/**
 * Creates an image link
 *
 *   <a href="link"><img src="imgSrc" border="0" title="text" /></a>
 */
function createImageLink(imgSrc, link, text, space)
{
    var linkElement = document.createElement('a');
    linkElement.href = link;
    if (space == true) {
        linkElement.style.paddingRight = '3px';
    }

    var image = document.createElement('img');
    image.border = '0';
    image.src = imgSrc;
    image.title = text;

    linkElement.appendChild(image);

    return linkElement;
}

/**
 * Prepares the datepicker with basic data
 */
function initDatePicker(name)
{
    Calendar.setup({
        inputField: name,
        ifFormat: $('#'+name).data('format'),
        dateType: $('#'+name).data('cal'),
        button: name+'_button',
        singleClick: true,
        weekNumbers: false,
        showsTime: true,
        multiple: false
    });
}

/**
 * Show Dialog Box
 */
function showDialogBox(name, dTitle, url, dHeight, dWidth)
{
    var dRect = {x: $(window).width(), y: $(window).height()};
    var dLeft = (dWidth  > dRect.x )? 0 : Math.round(dRect.x  / 2 - dWidth  / 2) + 'px';
    var dTop  = (dHeight > dRect.y)? 0 : Math.round(dRect.y / 2 - dHeight / 2) + 'px';

    if ($('#' + name).length == 0) {
        var overlay = $('<div>')
            .attr({'id':name+'_overlay', 'class':'dialog_box_overlay'})
            .hide()
            .on('click', function() {hideDialogBox(name);});
        var iframe  = $('<iframe>')
            .attr({'src': url, 'id': name + '_iframe'})
            .css({'height': dHeight+'px', 'width': dWidth+'px', 'border': 'none'})
            .on('load', function() {
                hideWorkingNotification();
                $(this).parent().css('display', 'block');
                $(this).contents().keypress($.proxy(function(e) {
                    if (e.keyCode == 27) {
                        hideDialogBox(this.attr('id'));
                    }
                }, $(this).parent()));
            });

        var close   = $('<span>').addClass('dialog_box_close').on('click', function() {hideDialogBox(name);});
        var title   = $('<div>').addClass('dialog_box_title').text(dTitle).append(close);
        var dialog  = $('<div>').attr({'id':name, 'class':'dialog_box'}).append(title).append(iframe).hide();
        
        $(document).keypress($.proxy(function(e) {
            if (e.keyCode == 27) {
                hideDialogBox(this.attr('id'));
            }
        }, dialog));
        $('body').append(overlay);
        $('body').append(dialog);
        dialog.css({'left': dLeft, 'top': dTop, 'position': 'fixed'});
    }

    $('#' + name + '_overlay').css('display', 'block');
    showWorkingNotification();
    if ($('#' + name + '_iframe').attr('src') == url) {
        $('#' + name + '_iframe').trigger('load');
    } else {
        $('#' + name + '_iframe').attr('src', url);
    }
}

/**
 * Hide Dialog Box
 */
function hideDialogBox(name)
{
    $('#' + name).hide();
    $('#' + name + '_overlay').hide();
}

/**
 * Server error handler
 */
function Jaws_Ajax_ServerError(error) 
{
    //Take the error and parse to see if it's a JawsServerError or a bug in the code
    var errorMessage = error.message;
    //JawsServerError pattern
    var pattern = /^\[(.*?)\]\s+(-)\s+(.*?)/;
    //Test..
    if (pattern.test(errorMessage)) {
        var errorSplitted = errorMessage.split(pattern);
        var errorCode     = errorSplitted[1];
        errorMessage      = errorSplitted[4];
        switch(errorCode) {
        case 'NOPERMISSION': //Not granted?
            alert(errorMessage);
            break;
        case 'NOSESSION': //No session?
            // FIXME, using href of base tag instead of admin.php or if empty parsing URL
            window.location = 'admin.php';
            break;
        case 'NOTLOGGED': //Session expired?
            alert(errorMessage + '...');
            // FIXME, using href of base tag instead of admin.php or if empty parsing URL
            window.location = 'admin.php';
            break;            
        }
    }
}

/**
 * Hide response boxes - Fast Code
 */
function hideResponseBox(name, timeout)
{
    if (typeof(timeout) == 'undefined') {
        timeout = '3000';
    }
    $(name).fadeOut(timeout);
}

/**
 * Show working notification.
 */
function showWorkingNotification(msg)
{
    if (!msg) {
        msg = Jaws.defines.loadingMessage;
    }
    $('#working_notification').html(msg);
    $('#working_notification').css('visibility', 'visible');
}

/**
 * Hide working notification
 */
function hideWorkingNotification()
{
    $('#working_notification').css('visibility', 'hidden');
}

var Jaws_Gadget = (function () {

    return {
        // return singleton instance object of gadget
        getInstance: function(gadget) {
            let objGadget;
            if (!Jaws.gadgets.hasOwnProperty(gadget) ||
                !Jaws.gadgets[gadget].hasOwnProperty('name')
            ) {
                objGadget = new Object;
                objGadget.app = Jaws;
                objGadget.name = gadget;
                objGadget.loaded = false;
                objGadget.gadget = objGadget;

                if (Jaws.gadgets[gadget]) {
                    objGadget.defines = Jaws.gadgets[gadget].defines;
                    objGadget.actions = Jaws.gadgets[gadget].actions;
                } else {
                    objGadget.defines = [];
                    objGadget.actions = {};
                }

                // shout interface method
                objGadget.shout = function(event, data, destGadget, broadcast) {
                    dstGadget = typeof dstGadget !== 'undefined'? dstGadget : '';
                    broadcast = typeof broadcast !== 'undefined'? broadcast : true;
                    return Jaws.shout(objGadget, event, data, dstGadget, broadcast);
                }

                objGadget.permission = function(name, subkey = '') {
                    return Jaws.permission(name, subkey, gadget);
                }

                objGadget.t = function(string, params) {
                    return Jaws.t(string, params, gadget);
                }

                // load action base class
                objGadget.action = new (window['Jaws_Gadget_Action']);
                objGadget.action.gadget = objGadget;

                // ajax interface
                objGadget.ajax = new JawsAjax(gadget, objGadget.AjaxCallback, objGadget);
                // local storage interface
                objGadget.storage = new JawsStorage(gadget);
                // show/hide message interface
                objGadget.message = new JawsMessage(objGadget);
                // show/hide loading interface
                objGadget.loading = new JawsLoading(objGadget);

                Jaws.gadgets[gadget] = objGadget;
            } else {
                objGadget = Jaws.gadgets[gadget];
            }

            if (!objGadget.loaded) {
                if (window['Jaws_Gadget_'+gadget]) {
                    let objGadgetOverload = new window['Jaws_Gadget_'+gadget];
                    objGadget = Object.assign(objGadget, new window['Jaws_Gadget_'+gadget]);
                    objGadget.loaded = true;
                    // load gadget initialize method if exist
                    if (objGadget.init) {
                        objGadget.init(Jaws.defines.mainGadget, Jaws.defines.mainAction);
                    }

                    Jaws.gadgets[gadget] = objGadget;
                }
            }

            return Jaws.gadgets[gadget];
        },

    };

})();

/*
 * Jaws javascript gadget action class
 */
function Jaws_Gadget_Action() { return {
    // return gadget js object instance
    load: function(action) {
        if (!this.gadget.actions.hasOwnProperty(action) ||
            !this.gadget.actions[action].hasOwnProperty('name')
        ) {
            let objAction = new (window['Jaws_Gadget_'+this.gadget.name+'_Action_'+action] || Object);
            objAction.app = Jaws;
            objAction.name = action;
            objAction.gadget = this.gadget;
            // ajax interface
            objAction.ajax = new JawsAjax(this.gadget.name, objAction.AjaxCallback, objAction);
            objAction.ajax.baseGadget = this.gadget.name;
            objAction.ajax.baseAction = action;

            // show/hide message interface
            objAction.message = new JawsMessage(objAction);
            // show/hide loading interface
            objAction.loading = new JawsLoading(objAction);

            objAction.t = function(string, params) {
                return Jaws.t(string, params, this.gadget.name);
            }

            // load action initialize method if exist
            if (objAction.init) {
                objAction.init(Jaws.defines.mainGadget, Jaws.defines.mainAction);
            }

            this.gadget.actions[action] = objAction;
        }

        return this.gadget.actions[action];
    }
}};

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (var i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

/**
 * Update WebPush Subscription
 */
function updateWebPushSubscription(pushSubscription) {
    pushSubscription = eval('(' + JSON.stringify(pushSubscription) + ')');
    pushSubscription.contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
    Jaws_Gadget.getInstance('Notification').gadget.ajax.call(
        'UpdateWebPushSubscription',
        pushSubscription,
        false,
        {baseScript: 'index.php'}
    );
}

/**
 * on document ready
 */
$(document).ready(function() {
    // detect running in full-screen/standalone mode
    Jaws.standalone =
        window.navigator.standalone ||
        window.matchMedia('(display-mode: standalone)').matches ||
        window.matchMedia('(display-mode: fullscreen)').matches;

    Jaws.navigated = false;
    if ('navigation' in window.performance) {
        Jaws.navigated = window.performance.navigation.type == 0;
    } else {
        let navigations = window.performance.getEntriesByType('navigation');
        if (navigations.length > 0) {
            Jaws.navigated = (navigations[0].type == 'navigate') || (navigations[0].type == 'prerender');
        }
    }

    // decode base64 encoded pubkey key 
    if (Jaws.defines.pubkey) {
        Jaws.defines.pubkey = window.atob(Jaws.defines.pubkey);
    }

    // grab hash change event
    $(window).on('hashchange', function() {
        if (!window.location.hash.blank() && Jaws.defines.requestedURL == '') {
            try {
                let reqRedirectURL = Jaws.filters.hex2bin(window.location.hash.substr(1));
                if (!reqRedirectURL.blank()) {
                    reqRedirectURL = new URL(
                        reqRedirectURL,
                        window.location.origin + $('base').first().attr('href')
                    ).pathname;

                    if (reqRedirectURL.indexOf($('base').first().attr('href')) === 0) {
                        reqRedirectURL = reqRedirectURL.substr($('base').first().attr('href').length);
                    }
                    // for security reason all slash and backslashed will removed from beginning of url
                    setTimeout(
                        function() {
                            window.location = reqRedirectURL.replace(/^(\/|\\)+/g, "");
                        },
                        1000
                    );
                }
            } catch (e) {
                // do nothing
            }
        }
    });

    // a solution for exit PWA app when press back-button in home page
    if (Jaws.standalone && history.length > 1 && Jaws.defines.requestedURL == '') {
        history.go(-(history.length - 1));
    }

    if (Jaws.defines.service_worker_enabled && ('serviceWorker' in navigator)) {
        navigator.serviceWorker.register('service-worker.js', {}).then(
            function(registration) {
                if (registration.active) {
                    // send configuration to service worker
                    registration.active.postMessage(
                        {
                            type: '', // message type
                            base: $('base').first().attr('href'),
                            script: Jaws.defines.script,
                            standalone: Jaws.standalone
                        }
                    );
                }

                registration.addEventListener('updatefound', () => {
                    registration.update();
                    if (navigator.serviceWorker.controller) {
                        if (confirm(Jaws.defines.reloadMessage)) {
                            location.reload(true);
                        }
                    }
                });
            }
        ).catch (
            function (error) {
                console.log('service-worker registration error: ', error);
            }
        );

        navigator.serviceWorker.ready.then(
            function(serviceWorkerRegistration) {
                if (Jaws.gadgets.Notification.defines.webpush_enabled &&
                    typeof serviceWorkerRegistration.pushManager !== 'undefined'
                ) {
                    var options = {
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(Jaws.gadgets.Notification.defines.webpush_pub_key)
                    };

                    serviceWorkerRegistration.pushManager.getSubscription().then(
                        function(pushSubscription) {
                            if (pushSubscription) {
                                if (!Jaws.gadgets.Notification.defines.webpush_subscription) {
                                    // update webpush subscription
                                    updateWebPushSubscription(pushSubscription);
                                }
                            } else {
                                if (Jaws.gadgets.Notification.defines.webpush_anonymouse || Jaws.defines.logged) {
                                    serviceWorkerRegistration.pushManager.subscribe(options).then(
                                        function (pushSubscription) {
                                            // update webpush subscription
                                            updateWebPushSubscription(pushSubscription);
                                        }
                                    ).catch (
                                        function(error) {
                                            console.log(error);
                                        }
                                    );
                                }
                            }
                        }
                    ).catch (
                        function(error) {
                            console.log(error);
                        }
                    );
                }
            }
        );

        navigator.serviceWorker.ready.then(
            function(registration) {
                //
            }
        );

        // Listen to messages coming from the service worker
        navigator.serviceWorker.addEventListener('message', function(event) {
            console.log(event);
        });
    }

    $('textarea[role="editor"]').each(function(index) {
        initEditor(this)
    });

    // disable anchor tag click when one of parents are disabled 
    $('a').closest(":disabled, .disabled").find('a').click(function (event) {
        event.preventDefault();
    });

    // toggle password between hide and show
    $(".input-group input[type='password'] + .input-group-text").click(
        function() {
            if ($(this).prev().attr('type') == 'password') {
                $(this).prev().attr('type', 'text');
            } else {
                $(this).prev().attr('type', 'password');
            }
            $(this).find('i').toggleClass('fa-eye fa-eye-slash');
        }
    );

    $('[data-bs-toggle="popover"], [data-bs-toggle="tooltip"]').map(function() {
        if ($(this).data('bs-toggle') == 'tooltip') {
            return new bootstrap.Tooltip(this, {});
        } else {
            return new bootstrap.Popover(this, {});
        }
    });

    // check input, select, textarea validity
    $('input,select,textarea').change(
        function(event, initialize = false) {
            if (initialize) {
                return;
            }
            if (this.checkValidity()) {
                $(this).removeClass('is-invalid');
            } else {
                $(this).addClass('is-invalid');
            }
        }
    );

    // initializing
    Jaws.init();

});

/**
 * on document loaded
 */
$(window).on('load', function() {
    // if url hash not empty trigger hash change event
    if (!window.location.hash.blank()) {
        $(window).trigger('hashchange');
    }

});

/**
 * Jaws object
 *
 */
Jaws = {
    gadgets: [],
    actions: [],
    defines: {},
    session: {},
    permissions: {},
    filters: {},
    translations: {
        0:{},
        1:{},
        2:{},
    },

    // define acl method for get permissions
    permission: function(name, subkey, gadget) {
        // is in forbidden acls?
        if (Jaws.permissions.forbiddens.indexOf((gadget+':'+name).toLowerCase()) >= 0) {
            return 0;
        }
        // session in restricted mode, only permissions of defined gadgets will be checked
        if ((Jaws.permissions.restricteds.length > 0) &&
            (Jaws.permissions.restricteds.indexOf(gadget.toLowerCase()) < 0)
        ) {
            return 0;
        }
        // don't check permissions for administrators
        if (Jaws.session.user.superadmin) {
            return 0xff;
        }
        // gadget
        if (!Jaws.permissions.components.hasOwnProperty(gadget)) {
            return 0;
        }
        // key name
        if (!Jaws.permissions.components[gadget].hasOwnProperty(name)) {
            return 0;
        }
        // subkey
        if (!Jaws.permissions.components[gadget][name].hasOwnProperty(subkey) ||
            Jaws.permissions.components[gadget][name][subkey].length == 0
        ) {
            return 0;
        }
        let perm = Jaws.permissions.components[gadget][name][subkey];
        let perm_user = null;   // null is important
        let perm_group = null;  // null is important
        let perm_default = 0;
        perm.forEach(function(item) {
            // user permission
            if (item.user) {
                perm_user = item.value;
            }
            // combine group permissions
            if (item.group) {
                perm_group = perm_group | item.value;  // bitwise or
            }
            // default permission
            if (!item.user && !item.group) {
                perm_default = item.value;
            }
        });

        return (perm_user !== null)? perm_user : (perm_group !== null? perm_group : perm_default);
    },

    // define t method
    t: function(string, params, module) {
        if (string.indexOf('.') >= 0) {
            [module , string] = string.split('.');
        }

        string = string.toUpperCase();
        module = module? module.toUpperCase() : '';
        type = module? 1 : 0;
        if (Jaws.translations[type].hasOwnProperty(module) &&
            Jaws.translations[type][module].hasOwnProperty(string)
        ) {
            string = Jaws.translations[type][module][string];
            string = string.replace(/\\n/g, "\n").replace(/\\"/g, '"');
            $.map(params, function(val, key) {
                string = string.replace('{'+key+'}', val);
            });
            string = string.replace(/\s*\{[0-9]+\}/g, '');
        }

        return string;
    },

    // call methods that listening the shouted event
    shout: function(shouter, event, data, dstGadget, broadcast) {
        dstGadget = typeof dstGadget !== 'undefined'? dstGadget : '';
        broadcast = typeof broadcast !== 'undefined'? broadcast : true;

        var result = null;
        $.each(Jaws.gadgets, function(gadget, objGadget) {
            if (objGadget.listen && objGadget.listen[event]) {
                // check event broadcasting
                if (!broadcast && objGadget.gadget.name !== dstGadget) {
                    return true; //continue;
                }

                var ret = objGadget.listen[event](shouter, data);
                if (dstGadget === objGadget.gadget.name) {
                    result = ret;
                }
            }
        });

        return result;
    },

    initGadgets: function() {
        // initialize date/time pickers
        $('[role="datepicker"]').each(function(index, el) {
            // picker type (date & time, date, time)
            let $picker = {};
            let $pickerType = $(el).data('picker-type') || 'date';
            if ($pickerType == 'datetime') {
                $picker = {
                    timepicker : true
                };
            } else if ($pickerType == 'time') {
                $picker = {
                    timepicker : true,
                    onlyTimepicker: true
                };
            }

            // calendar type
            let $calendar = ($(el).data('calendar') || Jaws.defines.calendar).toLowerCase();
            // inline view
            let $inline = ($(el).data('picker-view') || '') == 'inline';
            // date format
            let $dateFormat = $(el).data('date-format');
            $dateFormat = ($dateFormat === null || $dateFormat === undefined)? 'yyyy/MM/dd' : $dateFormat;
            // time format
            let $timeFormat = $(el).data('time-format') || 'HH:mm';
            // months string name
            let $months = [];
            let $monthsShort = [];
            for (let i = 0; i <= 11; i++) {
                $months.push(Jaws.t($calendar + '_month_' + i));
                $monthsShort.push(Jaws.t($calendar + '_month_short_' + i));
            }
            // days string name
            let $days = [];
            let $daysShort = [];
            for (let i = 0; i <= 6; i++) {
                $days.push(Jaws.t('day_' + i));
                $daysShort.push(Jaws.t('day_short_' + i));
            }
            // picker direction
            let $direction = Jaws.t('lang_direction');
            // picker position
            let $position = 'bottom ' + ($direction == 'ltr'? 'left' : 'right');

            new AirDatepicker(el, Object.assign({
                inline: $inline,
                calendar: $calendar,
                //startDate: '',
                //multipleDates: true,
                //selectedDates: [],
                direction: $direction,
                position: $position,
                parent: $(el).closest('div.modal-body, body').get(0),
                onBeforeShow: function(dpInstance) {
                    let _startDate = Date.now();
                    let _selectedDates = [];
                    if (!$(dpInstance.$el).val().blank()) {
                        _selectedDates = $.map($(dpInstance.$el).val().toString().split(','), $.trim);
                        if (dpInstance.opts.onlyTimepicker) {
                            _startDate = new Date();
                            let _time = _selectedDates[0].match(/([0-9]+):([0-9]+)\s*([ap]m)?/) || [0,0,''];
                            if (_time[3] == 'pm' && _time[1] != 12) {
                                _time[1] = parseFloat(_time[1]) + 12;
                            }
                            _startDate.setHours(_time[1], _time[2]);
                            _selectedDates = [_startDate];
                        } else {
                            _startDate = _selectedDates[0];
                        }
                    }
                    dpInstance.clear(true);
                    dpInstance.setViewDate(_startDate);
                    dpInstance.selectDate(_selectedDates, {updateTime: true});
                },
                locale: {
                    days: $days,
                    daysShort: $daysShort,
                    daysMin: $daysShort,
                    months: $months,
                    monthsShort: $monthsShort,
                    today: Jaws.t('today'),
                    clear: Jaws.t('clear'),
                    dateFormat: $dateFormat,
                    timeFormat: $timeFormat,
                    firstDay: ($calendar == 'gregorian')? 1 : 6
                }
            }, $picker));
        });

        // initialize gadgets
        $.each(this.gadgets, function(gadget) {
            let objGadget = Jaws_Gadget.getInstance(gadget);
            if (objGadget instanceof Object) {
                $.each(objGadget.actions, function(action) {
                    objGadget.action.load(action);
                });
            }
        });
    },

    loadTranslate: function(gadgets, callback) {
        let modules = '';
        if (Object.keys(this.translations[0]).length === 0) {
            modules = '0:';
        }

        gadgets.forEach(function(gadget, key) {
            if (!Jaws.translations[1].hasOwnProperty(gadget.toUpperCase())) {
                modules+= ',1:'+ gadget;
            }
        }.bind(this));

        if (!modules) {
            return callback? callback() : true;
        }

        let urlTranslates  = 'index.php?reqGadget=Settings' +
            '&reqAction=getTranslates&modules=' + modules +
            '&language='+ Jaws.defines.language +
            '&restype=gzjson&resexpr=86400&buildnumber=' + Jaws.defines.buildnumber;
        $.getJSON(urlTranslates, $.proxy(
            function(data) {
                // pure javascript not support deep merge
                $.extend(true, this.translations, data);
                return callback? callback() : true;
            },
            this
        ));
    },

    init: function() {
        // load translations
        this.loadTranslate(Object.keys(this.gadgets), this.initGadgets.bind(this));
    },

};

Jaws.filters = {
    /**
     *
     */
    join: function(input, glue = ',') {
        return Array.isArray(input)? input.join(glue) : input;
    },

    /**
     *
     */
    split: function(input, pattern = ',') {
        return [null, undefined, ''].includes(input)? [] : input.toString().split(pattern);
    },

    /**
     *
     */
    upcase: function(input) {
        return input.toUpperCase();
    },

    /**
     *
     */
    downcase: function(input) {
        return input.toLowerCase();
    },

    /**
     *
     */
    json_encode: function(input) {
        return JSON.stringify(input);
    },

    /**
     *
     */
    json_decode: function(input) {
        return JSON.parse(input);
    },

    /**
     * UTF-8 encoding
     */
    utf8_encode : function (input) {
        input = input.replace(/\r\n/g,"\n");
        let result = '';
        let c = 0;

        for (let n = 0; n < input.length; n++) {
            c = input.charCodeAt(n);

            if (c < 128) {
                result += String.fromCharCode(c);
            } else if((c > 127) && (c < 2048)) {
                result += String.fromCharCode((c >> 6) | 192);
                result += String.fromCharCode((c & 63) | 128);
            } else {
                result += String.fromCharCode((c >> 12) | 224);
                result += String.fromCharCode(((c >> 6) & 63) | 128);
                result += String.fromCharCode((c & 63) | 128);
            }
        }

        return result;
    },

    /**
     * UTF-8 decoding
     */
    utf8_decode: function (input) {
        let result = '';
        let i = 0;
        let c = c1 = c2 = 0;

        while ( i < input.length ) {
            c = input.charCodeAt(i);

            if (c < 128) {
                result += String.fromCharCode(c);
                i++;
            } else if((c > 191) && (c < 224)) {
                c2 = input.charCodeAt(i+1);
                result += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            } else {
                c2 = input.charCodeAt(i+1);
                c3 = input.charCodeAt(i+2);
                result += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }
        }

        return result;
    },

    /**
     *
     */
    base64_encode: function(input) {
        const keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
        let result = '';
        let chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        let i = 0;

        input = this.utf8_encode(input);
        while (i < input.length) {
            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }

            result = result +
                keyStr.charAt(enc1) + keyStr.charAt(enc2) +
                keyStr.charAt(enc3) + keyStr.charAt(enc4);
        }

        return result;
    },

    /**
     *
     */
    base64_decode: function(input) {
        const keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
        let result = '';
        let chr1, chr2, chr3;
        let enc1, enc2, enc3, enc4;
        let i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
        while (i < input.length) {
            enc1 = keyStr.indexOf(input.charAt(i++));
            enc2 = keyStr.indexOf(input.charAt(i++));
            enc3 = keyStr.indexOf(input.charAt(i++));
            enc4 = keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            result = result + String.fromCharCode(chr1);
            if (enc3 != 64) {
                result = result + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                result = result + String.fromCharCode(chr3);
            }
        }

        return this.utf8_decode(result);
    },

    /**
     * Javascript PHP hex2bin string prototype
     *
     */
    hex2bin: function(input) {
        let i = 0, n = 0, l = input.length - 1, bytes = [];

        try {
            for (i; i < l; i += 2) {
                n = parseInt(input.substr(i, 2), 16);
                if (isNaN(n)) {
                    throw 'invalid hex string!';
                }

                bytes.push(n);
            }
            return this.utf8_decode(String.fromCharCode.apply(String, bytes));
        } catch (e) {
            return '';
        }
    },

    /**
     * Javascript PHP bin2hex string prototype
     *
     */
    bin2hex: function(input) {
        input = this.utf8_encode(input);
        let i = 0, l = input.length, chr, hex = '';

        for (i; i < l; i++) {
            chr = input.charCodeAt(i).toString(16);
            hex += chr.length < 2 ? '0' + chr : chr;
        }

        return hex;
    },

    /**
     * Convert special characters to HTML entities
     */
    ent_encode: function(input, noquotes = false) {
        input = [null, undefined].includes(input)? '' : input.toString();
        // Put this first to avoid double-encoding
        input = input.replace(/&/g, '&amp;');

        input = input.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        if (!noquotes) {
            input = input.replace(/"/g, '&quot;');
        }

        return input;
    },

    /**
     * Convert special HTML entities back to characters
     */
    ent_decode: function(input, noquotes = false) {
        input = [null, undefined].includes(input)? '' : input.toString();
        input = input.replace(/&lt;/g, '<').replace(/&gt;/g, '>');
        if (!noquotes) {
            input = input.replace(/&quot;/g, '"');
        }

        // Put this in last place to avoid escape being double-decoded
        input = input.replace(/&amp;/g, '&');

        return input;
    },

    /**
     * Javascript format number prototype
     */
    formatNumber : function(input, unit = '') {
        if ([undefined, null, ''].indexOf(input) >= 0) {
            return null;
        }

        let num = Number.parseFloat(input);
        let units = {
            'length': {
                'step': 1000,
                'symbol': ['m', 'km', 'Mm', 'Gm', 'Tm', 'Pm']
            },
            'power': {
                'step': 1000,
                'symbol': ['W', 'kW', 'MW', 'GW', 'TW', 'PW']
            },
            'weight': {
                'step': 1000,
                'symbol': ['g', 'Kg', 'Mg', 'Gg', 'Tg', 'Pg']
            },
            'currency': {
                'step': 1000,
                'symbol': ['',  'K',  'M',  'B',  'T',  'Q']
            },
            'filesize': {
                'step': 1024,
                'symbol': ['B', 'KB', 'MB', 'GB', 'TB', 'PB']
            },
            'hashrate': {
                'step': 1000,
                'symbol': ['H', 'KH', 'MH', 'GH', 'TH', 'PH']
            }
        };

        let symbol = '';
        if (units.hasOwnProperty(unit)) {
            let i = 0;
            while (num >= units[unit]['step']) {
                i++;
                num = num/units[unit]['step'];
            }
            symbol = ' '+ units[unit]['symbol'][i];
        }

        let args = [].slice.call(arguments);
        args.shift()
        num = num.toFixed(args.shift());
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + symbol;
    },

    /**
     * UTC timestamp to string
     */
    date2str: function(input, format = '', calendar = '') {
        if ([undefined, null, ''].indexOf(input) >= 0) {
            return null;
        }

        // if input is numeric, multiple by 1000 for convert second to millisecond
        input = parseFloat(input) == input? input*1000 : input;

        format = format || 'yyyy/MM/dd';
        calendar = (calendar || Jaws.defines.calendar).toLowerCase();
        // make "locale" static
        if (typeof this.date2str.locale == 'undefined' ) {
            this.date2str.locale = {};
        }

        if (!this.date2str.locale.hasOwnProperty(calendar)) {
            let _months = [];
            let _monthsShort = [];
            for (let i = 0; i <= 11; i++) {
                _months.push(Jaws.t(calendar + '_month_' + i));
                _monthsShort.push(Jaws.t(calendar + '_month_short_' + i));
            }
            // days string name
            let _days = [];
            let _daysShort = [];
            for (let i = 0; i <= 6; i++) {
                _days.push(Jaws.t('day_' + i));
                _daysShort.push(Jaws.t('day_short_' + i));
            }

            this.date2str.locale[calendar] = {
                days: _days,
                daysShort: _daysShort,
                daysMin: _daysShort,
                months: _months,
                monthsShort: _monthsShort,
                firstDay: (calendar == 'gregorian')? 1 : 6
            };
        }

        return AirDatepicker.formatDate(input, format, this.date2str.locale[calendar], calendar);
    },

    /**
     * Time(seconds since midnight) to string
     */
    time2str: function(input, format = '') {
        format = format || 'HH:mm';
        return this.date2str(this.utc2local(input), format);
    },

    /**
     * UTC to local date/time timestamp
     */
    utc2local: function(input) {
        return input + (new Date()).getTimezoneOffset()*60;
    },

    /**
     * local date/time timestamp to UTC
     */
    local2utc: function(input) {
        return input - (new Date()).getTimezoneOffset()*60;
    },

    /**
     * call filter(s) indirect as variable
     */
    apply: function($filter, input) {
        $filters = Array.isArray($filter)? $filter : [$filter];
        for (let i = 0; i < $filters.length; i++) {
            // split filter and parameters (for example: function: param1, param2, ...)
            let filterArgs = [];
            let filter = $filters[i];
            let indexColon = $filters[i].indexOf(':');
            if (indexColon != -1) {
                filter = $filters[i].slice(0, indexColon);
                filterArgs = ($filters[i].slice(indexColon + 1) || '').split(
                    /,(?=(?:(?:[^"']*["'][^"']*["'])*[^"']*$))/
                ).map(s => s.trim().replace(/^["']|["']$/g, ''));
            }

            input = Jaws.filters[filter].call(this, input, ...filterArgs);
        }
        return input;
    },

}