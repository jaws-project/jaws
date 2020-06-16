/**
 * General Javascript methods
 *
 * @category    Ajax
 * @package     Core
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2004-2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */

/*!
 * Extra plugins for jquery
 */
jQuery.extend({
    unserialize: function(str) {
        str = decodeURIComponent((str || document.location.search).replace(/\+/gi, " ")).replace(/(^\?)/,'');
        return str.split("&").map(function(n) {
            n=n.split("=");
            switch($.type(this[n[0]])) {
                case "undefined":
                    this[n[0]] = n[1];
                    break;
                case "array":
                    this[n[0]].push(n[1]);
                    break;
                default:
                    this[n[0]] = [this[n[0]]];
                    this[n[0]].push(n[1]);
            }
            return this;
        }.bind({}))[0];
    },

    viewport: function() {
        return {
            get: function() {
                let dWidth = $(document).innerWidth();
                return (dWidth < 700) ? 'xs' : ((dWidth < 992) ? 'sm' : ((dWidth < 1200) ? 'md' : 'lg'));
            },
            is: function(breakPoint) {
                return breakPoint == this.get();
            }
        };
    }()
});

(function($) {
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
            if ($li.length == 0 && location.pathname.match(/^.*\//)[0] != jaws.Defines.base) {
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

    this.default_message_container = $(
        "#"+(this.mainRequest['gadget']+'_'+ this.mainRequest['action']+'_'+'response').toLowerCase()
    );

    /**
     * Performs asynchronous Ajax request
     *
     * @param   string  action  Name of the new JawsAjax( to be executed
     * @param   object  data    Parameters passed to the function (optional)
     * @return  void
     */
    this.callAsync = function (action, data, done, callOptions) {
        var options = {};
        var gadget, baseScript;

        callOptions = callOptions || {};
        // response message/loading container
        if (!callOptions.hasOwnProperty('message_container')) {
            var rc_gadget, rc_action;
            rc_gadget = this.baseGadget? this.baseGadget : this.mainRequest['gadget'];
            rc_action = this.baseAction? this.baseAction : this.mainRequest['action'];
            callOptions.message_container = $("#"+(rc_gadget+'_'+ rc_action+'_'+'response').toLowerCase());
        }

        gadget = callOptions.hasOwnProperty('gadget')? callOptions.gadget : this.gadget;
        baseScript = callOptions.hasOwnProperty('baseScript')? callOptions.baseScript : this.baseScript;

        options.done = done? $.proxy(done, this.callbackObject) : undefined;
        // url
        options.url  = baseScript + '?gadget=' + gadget + '&action=' + action;
        if (callOptions.hasOwnProperty('restype')) {
            options.url+= '&restype=' + callOptions.restype;
        } else {
            options.url+= '&restype=json';
        }

        options.type = 'POST';
        options.async  = true;
        options.action = action;
        options.callOptions = callOptions;
        options.data = JSON.stringify((/boolean|number|string/).test(typeof data)? [data] : data);
        options.contentType = 'application/json; charset=utf-8';
        options.beforeSend = this.onSend.bind(this, options);
        options.success = this.onSuccess.bind(this, options);
        options.error = this.onError.bind(this, options);
        options.complete = this.onComplete.bind(this, options);
        $.ajax(options);
    };

    /**
     * Performs synchronous Ajax request
     *
     * @param   string  action  Name of the action to be executed
     * @param   object  data    Parameters passed to the function (optional)
     * @return  mixed   Response text on synchronous mode or void otherwise
     */
    this.callSync = function (action, data, done, callOptions) {
        var options = {};
        var gadget, baseScript;

        callOptions = callOptions || {};
        // response message/loading container
        if (!callOptions.hasOwnProperty('message_container')) {
            var rc_gadget, rc_action;
            rc_gadget = this.baseGadget? this.baseGadget : this.mainRequest['gadget'];
            rc_action = this.baseAction? this.baseAction : this.mainRequest['action'];
            callOptions.message_container = $("#"+(rc_gadget+'_'+ rc_action+'_'+'response').toLowerCase());
        }

        gadget = callOptions.hasOwnProperty('gadget')? callOptions.gadget : this.gadget;
        baseScript = callOptions.hasOwnProperty('baseScript')? callOptions.baseScript : this.baseScript;

        options.done = done? $.proxy(done, this.callbackObject) : undefined;
        // url
        options.url  = baseScript + '?gadget=' + gadget + '&action=' + action;
        if (callOptions.hasOwnProperty('restype')) {
            options.url+= '&restype=' + callOptions.restype;
        } else {
            options.url+= '&restype=json';
        }

        options.type = 'POST';
        options.async = false;
        options.action = action;
        options.callOptions = callOptions;
        options.data = JSON.stringify((/boolean|number|string/).test(typeof data)? [data] : data);
        options.contentType = 'application/json; charset=utf-8';
        options.beforeSend = this.onSend.bind(this, options);
        options.success = this.onSuccess.bind(this, options);
        options.error = this.onError.bind(this, options);
        //options.complete = this.onComplete.bind(this, options);
        let result = $.ajax(options);
        let response = eval('(' + result.responseText + ')');
        // hide loading
        this.callbackObject.gadget.message.loading(false, callOptions.message_container);

        // response message
        if (callOptions.hasOwnProperty('showMessage')) {
            if (callOptions.showMessage) {
                this.callbackObject.gadget.message.show(response, callOptions.message_container);
            }
        } else if (this.defaultOptions.showMessage) {
            this.callbackObject.gadget.message.show(response, callOptions.message_container);
        }

        return response;
    };

    /**
     * Performs asynchronous file upload
     *
     * @param   {string}    action      Jaws action name
     * @param   {file}      file        File object to be uploaded
     * @param   {function}  done        Success callback function
     * @param   {function}  progress    Progress callback function
     * @return  {boolean}
     */
    this.uploadFile = function (action, formData, done, progress, callOptions) {
        var gadget, baseScript;

        callOptions = callOptions || {};
        // response message/loading container
        if (!callOptions.hasOwnProperty('message_container')) {
            var rc_gadget, rc_action;
            rc_gadget = this.baseGadget? this.baseGadget : this.mainRequest['gadget'];
            rc_action = this.baseAction? this.baseAction : this.mainRequest['action'];
            callOptions.message_container = $("#"+(rc_gadget+'_'+ rc_action+'_'+'response').toLowerCase());
        }

        gadget = callOptions.hasOwnProperty('gadget')? callOptions.gadget : this.gadget;
        baseScript = callOptions.hasOwnProperty('baseScript')? callOptions.baseScript : this.baseScript;

        var options = {
            async: true,
            type: 'POST',
            data: formData,
            dataType: 'text',
            action: action,
            timeout: 10 * 60 * 1000, /* 10 minutes */
            contentType: false,
            processData: false,
            cache: false,
            done: done? $.proxy(done, this.callbackObject) : undefined,
            xhr: function() {
                // handle the upload progress
                var xhr = $.ajaxSettings.xhr();
                if (xhr.upload) {
                    xhr.upload.addEventListener('progress', progress, false);
                    return xhr;
                }
            }
        };
        // url
        options.url  = baseScript + '?gadget=' + gadget + '&action=' + action;
        if (callOptions.hasOwnProperty('restype')) {
            options.url+= '&restype=' + callOptions.restype;
        } else {
            options.url+= '&restype=json';
        }

        options.callOptions = callOptions;
        options.beforeSend = this.onSend.bind(this, options);
        options.success = this.onSuccess.bind(this, options);
        options.error = this.onError.bind(this, options);
        options.complete = this.onComplete.bind(this, options);
        return $.ajax(options);
    };

    this.onSend = function (reqOptions) {
        // start show loading indicator
        this.callbackObject.gadget.message.loading(true, reqOptions.callOptions.message_container);
    };

    this.onSuccess = function (reqOptions, data, textStatus, jqXHR) {
        // ----
    };

    this.onError = function (reqOptions, jqXHR, textStatus, errorThrown) {
        // TODO: alert error message
    };

    this.onComplete = function (reqOptions, jqXHR, textStatus) {
        // hide loading
        this.callbackObject.gadget.message.loading(false, reqOptions.callOptions.message_container);

        response = eval('(' + jqXHR.responseText + ')');
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
                this.callbackObject.gadget.message.show(response, reqOptions.callOptions.message_container);
            }
        } else if (this.defaultOptions.showMessage) {
            this.callbackObject.gadget.message.show(response, reqOptions.callOptions.message_container);
        }
    };

}

/**
 * Message base class
 *
 * @param   object  objOwner    Owner object(gadget or action object)
 * @return  void
 */
function JawsMessage(objOwner)
{
    this.objOwner = objOwner;
    this.default_container = $(
        "#"+(jaws.Defines.mainGadget+'_'+ jaws.Defines.mainAction+'_'+'response').toLowerCase()
    );

    /**
     * show response message
     *
     * @param   object  message     Jaws Response message
     * @param   object  container   jQuery DOM element message container
     * @return  void
     */
    this.show = function (message, container) {
        if (!message || !$.trim(message.text) || !message.type) {
            return;
        }

        if (!container || !container.length) {
            container =  this.default_container;
        }

        if (container.length) {
            container.html(message.text).attr('class', message.type);
            container.stop(true, true).fadeIn().delay(4000).fadeOut(
                1000,
                function() {
                    //$(this).removeClass();
                }
            );
        }
    }

    /**
     * show response message
     *
     * @param   bool    show        show/hide loading
     * @param   object  container   jQuery DOM element message container
     * @return  void
     */
    this.loading = function (show, container) {
        if (!container || !container.length) {
            container =  this.default_container;
        }

        if (container.length) {
            if (show) {
                let loadingMessage = this.objOwner.gadget.defines.loadingMessage ||
                    jaws.Defines.loadingMessage ||
                    '...';
                container.html(loadingMessage).attr('class', 'response_loading alert-info');
                container.stop(true, true).fadeIn();
            } else {
                container.fadeOut(0, function() {$(this).removeClass();});
            }
        }
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
            browser = jaws.Defines.editorMediaBrowser || '';
            break;
        case 'image':
            browser = jaws.Defines.editorImageBrowser || '';
            break;
        case 'file':
            browser = jaws.Defines.editorFileBrowser || '';
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
    var editorType = objEditor.data('editor') || 'textarea';
    switch(editorType) {
        case 'ckeditor':
            $.loadScript('libraries/ckeditor/ckeditor.js', function() {
                objEditor.ckeditor({
                    'baseHref': $('base').attr('href'),
                    'contentsLangDirection': objEditor.data('direction') || 'ltr',
                    'language': objEditor.data('language') || 'en',
                    'AutoDetectLanguage': false,
                    'skin': 'moono',
                    'theme': 'default',
                    'readOnly': objEditor.data('readonly') == '1',
                    'resize_enabled': objEditor.data('resizable') == '1',
                    'toolbar': jaws.Defines.editorToolbar,
                    'extraPlugins': jaws.Defines.editorPlugins,
                    'filebrowserBrowseUrl': jaws.Defines.editorFileBrowser || '',
                    'filebrowserImageBrowseUrl': jaws.Defines.editorImageBrowser || '',
                    'filebrowserFlashBrowseUrl': jaws.Defines.editorMediaBrowser || '',
                    'removePlugins': '',
                    'autoParagraph': false,
                    'indentUnit': 'em',
                    'indentOffset': '1'
                });
            });
            break;

        case 'tinymce':
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
                            'directionality': objEditor.data('direction') || 'ltr',
                            'language': objEditor.data('language') || 'en',
                            'theme': 'modern',
                            'plugins': jaws.Defines.editorPlugins,
                            'toolbar1': jaws.Defines.editorToolbar,
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
 * Javascript htmlspecialchars_decode
 *
 * @see https://raw.github.com/kvz/phpjs/master/functions/strings/htmlspecialchars_decode.js
 */
String.prototype.defilter = function(quote_style) {
    var optTemp = 0,
        i = 0,
        noquotes = false;

    if (typeof quote_style === 'undefined') {
        quote_style = 3;
    }

    var str = this.replace(/&lt;/g, '<').replace(/&gt;/g, '>');
    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    };

    if (quote_style === 0) {
        noquotes = true;
    }

    // Allow for a single string or an array of string flags
    if (typeof quote_style !== 'number') {
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
        str = str.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
        // str = str.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
    }

    if (!noquotes) {
        str = str.replace(/&quot;/g, '"');
    }

    // Put this in last place to avoid escape being double-decoded
    str = str.replace(/&amp;/g, '&');
    return str;
};

/**
 * Javascript PHP hex2bin string prototype
 *
 */
String.prototype.hex2bin = function() {
    let i = 0, n = 0, l = this.length - 1, bytes = [];

    try {
        for (i; i < l; i += 2) {
            n = parseInt(this.substr(i, 2), 16);
            if (isNaN(n)) {
                throw 'invalid hex string!';
            }

            bytes.push(n);
        }
        return String.fromCharCode.apply(String, bytes);
    } catch (e) {
        return '';
    }
}

/**
 * Javascript PHP bin2hex string prototype
 *
 */
String.prototype.bin2hex = function() {
    let i = 0, l = this.length, chr, hex = '';

    for (i; i < l; i++) {
        chr = this.charCodeAt(i).toString(16);
        hex += chr.length < 2 ? '0' + chr : chr;
    }

    return hex;
}

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
            result = objGadget.callSync('getData', [firstValues, $('#' + this.name)[0].id]);
        } else {
            result = objGadget.ajax.callSync('getData', [firstValues, $('#' + this.name)[0].id]);
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
            result = objGadget.callSync('getData', [previousValues, $('#' + this.name)[0].id]);
        } else {
            result = objGadget.ajax.callSync('getData', [previousValues, $('#' + this.name)[0].id]);
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
            result = objGadget.callSync('getData', [nextValues, $('#' + this.name)[0].id]);
        } else {
            result = objGadget.ajax.callSync('getData', [nextValues, $('#' + this.name)[0].id]);
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
            result = objGadget.callSync('getData', [lastValues, $('#' + this.name)[0].id]);
        } else {
            result = objGadget.ajax.callSync('getData', [lastValues, $('#' + this.name)[0].id]);
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
            result = objGadget.callSync('getData', [currentPage, $('#' + this.name)[0].id]);
        } else {
            result = objGadget.ajax.callSync('getData', [currentPage, $('#' + this.name)[0].id]);
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
    var dRect = {x: $(document).width(), y: $(document).height()};
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
        dialog.css({'left': dLeft, 'top': dTop});
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
        msg = jaws.Defines.loadingMessage;
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
    var instances = {};

    return {
        // return singleton instance object of gadget
        getInstance: function(gadget) {
            if (!instances[gadget]) {
                var objGadget = new (window['Jaws_Gadget_'+gadget] || Object);
                objGadget.name = gadget;
                objGadget.gadget = objGadget;
                objGadget.objects = {
                    'Actions': {}
                };

                if (jaws[gadget]) {
                    objGadget.defines = jaws[gadget].Defines;
                    objGadget.actions = jaws[gadget].Actions;
                } else {
                    objGadget.defines = [];
                    objGadget.actions = [];
                }

                // shout interface method
                objGadget.shout = function(event, data, destGadget, broadcast) {
                    dstGadget = typeof dstGadget !== 'undefined'? dstGadget : '';
                    broadcast = typeof broadcast !== 'undefined'? broadcast : true;
                    return Jaws_Gadget.shout(objGadget, event, data, dstGadget, broadcast);
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

                // load gadget initialize method if exist
                if (objGadget.init) {
                    objGadget.init(jaws.Defines.mainGadget, jaws.Defines.mainAction);
                }
                instances[gadget] = objGadget;
            }

            return instances[gadget];
        },

        // call methods that listening the shouted event
        shout: function(shouter, event, data, dstGadget, broadcast) {
            dstGadget = typeof dstGadget !== 'undefined'? dstGadget : '';
            broadcast = typeof broadcast !== 'undefined'? broadcast : true;

            var result = null;
            $.each(instances, function(index, instance) {
                if (instance.listen && instance.listen[event]) {
                    // check event broadcasting
                    if (!broadcast && instance.gadget.name !== dstGadget) {
                        return true; //continue;
                    }

                    var ret = instance.listen[event](shouter, data);
                    if (dstGadget === instance.gadget.name) {
                        result = ret;
                    }
                }
            });

            return result;
        }
    };

})();

/*
 * Jaws javascript gadget action class
 */
function Jaws_Gadget_Action() { return {
    // return gadget js object instance
    load: function(action) {
        if (!this.gadget.objects['Actions'][action]) {
            var objAction = new (window['Jaws_Gadget_'+this.gadget.name+'_Action_'+action] || Object);
            objAction.name = action;
            objAction.gadget = this.gadget;
            // ajax interface
            objAction.ajax = new JawsAjax(this.gadget.name, objAction.AjaxCallback, objAction);
            objAction.ajax.baseGadget = this.gadget.name;
            objAction.ajax.baseAction = action;

            // show/hide message interface 
            objAction.message = new JawsMessage(objAction);

            // load action initialize method if exist
            if (objAction.init) {
                objAction.init(jaws.Defines.mainGadget, jaws.Defines.mainAction);
            }

            this.gadget.objects['Actions'][action] = objAction;
        }

        return this.gadget.objects['Actions'][action];
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
    Jaws_Gadget.getInstance('Notification').gadget.ajax.callAsync(
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
    jaws.standalone =
        window.navigator.standalone ||
        window.matchMedia('(display-mode: standalone)').matches ||
        window.matchMedia('(display-mode: fullscreen)').matches;

    jaws.navigated = false;
    if ('navigation' in window.performance) {
        jaws.navigated = window.performance.navigation.type == 0;
    } else {
        let navigations = window.performance.getEntriesByType('navigation');
        if (navigations.length > 0) {
            jaws.navigated = (navigations[0].type == 'navigate') || (navigations[0].type == 'prerender');
        }
    }

    // grab hash change event
    $(window).on('hashchange', function() {
        if (!window.location.hash.blank() && jaws.Defines.requestedURL == '') {
            try {
                let reqRedirectURL = window.location.hash.substr(1).hex2bin();
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
    if (jaws.standalone && history.length > 1 && jaws.Defines.requestedURL == '') {
        history.go(-(history.length - 1));
    }

    if (jaws.Defines.service_worker_enabled && ('serviceWorker' in navigator)) {
        navigator.serviceWorker.register('service-worker.js', {}).then(
            function(registration) {
                if (registration.active) {
                    // send configuration to service worker
                    registration.active.postMessage(
                        {
                            type: '', // message type
                            base: $('base').first().attr('href'),
                            script: jaws.Defines.script,
                            standalone: jaws.standalone
                        }
                    );
                }

                registration.addEventListener('updatefound', () => {
                    registration.update();
                    if (navigator.serviceWorker.controller) {
                        if (confirm(jaws.Defines.reloadMessage)) {
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
                if (jaws.Notification.Defines.webpush_enabled &&
                    typeof serviceWorkerRegistration.pushManager !== 'undefined'
                ) {
                    var options = {
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(jaws.Notification.Defines.webpush_pub_key)
                    };

                    serviceWorkerRegistration.pushManager.getSubscription().then(
                        function(pushSubscription) {
                            if (pushSubscription) {
                                if (!jaws.Notification.Defines.webpush_subscription) {
                                    // update webpush subscription
                                    updateWebPushSubscription(pushSubscription);
                                }
                            } else {
                                if (jaws.Notification.Defines.webpush_anonymouse || jaws.Defines.logged) {
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

    $.each(jaws.Gadgets, function(index, gadget) {
        Jaws_Gadget.getInstance(gadget);
        $.each(jaws[gadget].Actions, function(index, action) {
            Jaws_Gadget.getInstance(gadget).action.load(action);
        });
    });
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