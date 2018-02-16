/**
 * General Javascript methods
 *
 * @category    Ajax
 * @package     Core
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2004-2015 Jaws Development Group
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
    }
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
    }
})(jQuery);


/**
 * Ajax base class
 *
 * @param   string  gadget      Gadget Name
 * @param   object  callback    Callback functions
 * @param   string  baseScript  Base URL
 * @return  void
 */
function JawsAjax(gadget, callbackFunctions, callbackObject, baseScript)
{
    this.gadget = gadget;
    this.callbackObject = callbackObject;
    this.callbackFunctions = callbackFunctions;
    this.loadingMessage = '';
    var reqValues = $('meta[name=application-name]').attr('content').split(':');
    this.mainRequest = {'base': reqValues[0], 'gadget': reqValues[1], 'action': reqValues[2]};
    this.baseScript  = (baseScript === undefined)? this.mainRequest['base'] : baseScript;
    this.baseURL = this.baseScript + '?gadget=' + this.gadget + '&restype=json&action=';
    this.msgBox  = "#"+(this.mainRequest['gadget']+'_'+ this.mainRequest['action']+'_'+'response').toLowerCase();

    /**
     * Performs asynchronous Ajax request
     *
     * @param   string  action  Name of the new JawsAjax( to be executed
     * @param   object  data    Parameters passed to the function (optional)
     * @return  void
     */
    this.callAsync = function (action, data, done) {
        var options  = {};
        options.done = done? $.proxy(done, this.callbackObject) : undefined;
        options.url  = this.baseURL + action;
        options.type = 'POST';
        options.async  = true;
        options.action = action;
        options.data = $.encodeJSON((/boolean|number|string/).test(typeof data)? [data] : data);
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
    this.callSync = function (action, data, done) {
        var options = {};
        options.done = done? $.proxy(done, this.callbackObject) : undefined;
        options.url = this.baseURL + action;
        options.type = 'POST';
        options.async = false;
        options.action = action;
        options.data = $.encodeJSON((/boolean|number|string/).test(typeof data)? [data] : data);
        options.contentType = 'application/json; charset=utf-8';
        options.beforeSend = this.onSend.bind(this, options);
        //options.complete = this.onComplete.bind(this, options);
        var result = $.ajax(options);
        // hide loading
        this.showLoading(false);
        return eval('(' + result.responseText + ')');
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
    this.uploadFile = function (action, file, done, progress) {
        var fd = new FormData();
        fd.append('file', file);
        var options = {
            async: true,
            type: 'POST',
            data: fd,
            dataType: 'text',
            url: this.baseURL + action,
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
        options.beforeSend = this.onSend.bind(this, options);
        options.success = this.onSuccess.bind(this, options);
        options.error = this.onError.bind(this, options);
        options.complete = this.onComplete.bind(this, options);
        return $.ajax(options);
    };

    this.onSend = function () {
        // start show loading indicator
        this.showLoading(true);
    };

    this.onSuccess = function (reqOptions, data, textStatus, jqXHR) {
        // ----
    };

    this.onError = function (reqOptions, jqXHR, textStatus, errorThrown) {
        // TODO: alert error message
    };

    this.onComplete = function (reqOptions, jqXHR, textStatus) {
        // hide loading
        this.showLoading(false);

        response = eval('(' + jqXHR.responseText + ')');
        // call inline user define function
        if (reqOptions.done) {
            reqOptions.done(response, jqXHR.status);
        }

        if (this.callbackFunctions && this.callbackFunctions[reqOptions.action]) {
            this.callbackFunctions[reqOptions.action].call(
                this.callbackObject,
                response,
                jqXHR.status
            );
        }
    };

    /*
     * show response message
     */
    this.showResponse = function (response, element) {
        if (Array.isArray(response)) {
            // only show first response
            response = response[0];
        }

        if (!response.text.trim()) {
            return;
        }

        element = element || $(this.msgBox);
        element.html(response.text).attr('class', response.type);
        element.stop(true, true).fadeIn().delay(4000).fadeOut(1000, function() {$(this).removeClass();});
    };

    /*
     * show loading indicator
     */
    this.showLoading = function (show) {
        if ($(this.msgBox)) {
            if (show) {
                if (this.loadingMessage) {
                    loadingMessage = this.loadingMessage;
                } else {
                    loadingMessage = jaws.Defines.loadingMessage || '...';
                }
                $(this.msgBox).html(loadingMessage).attr('class', 'response_loading alert-info');
                $(this.msgBox).stop(true, true).fadeIn();
            } else {
                $(this.msgBox).fadeOut(0, function() {$(this).removeClass();});
            }
        }
    };
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
            this.storage.setItem(key, $.encodeJSON(value));
        } else {
            this.storage.write(key, $.encodeJSON(value));
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

/* Copyright (c) 2005 JSON.org */
$.encodeJSON = function(v) {
    var m = {
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"' : '\\"',
            '\\': '\\\\'
        },
        s = {
            'boolean': function (x) {
                return String(x);
            },
            number: function (x) {
                return isFinite(x) ? String(x) : 'null';
            },
            string: function (x) {
                if (new RegExp("[\x00-\x1f\\\"]").test(x)) {
                    x = x.replace(new RegExp("([\x00-\x1f\\\"])", 'g'), function(a, b) {
                        var c = m[b];
                        if (c) {
                            return c;
                        }
                        c = b.charCodeAt();
                        return '\\u00' +
                            Math.floor(c / 16).toString(16) +
                            (c % 16).toString(16);
                    });
                }
                return '"' + x + '"';
            },
            object: function (x) {
                if (x) {
                    var a = [], b, f, i, l, v;
                    if ((x instanceof Object) || ((typeof x) == 'object') || (x instanceof Array)) {
                        a[0] = '{';
                        for (i in x) {
                            if (!x.hasOwnProperty(i)) {
                                continue;
                            }
                            v = x[i];
                            f = s[typeof v];
                            if (f) {
                                v = f(v);
                                if (typeof v == 'string') {
                                    if (b) {
                                        a[a.length] = ',';
                                    }
                                    a.push(s.string(i), ':', v);
                                    b = true;
                                }
                            }
                        }
                        a[a.length] = '}';
                    } else {
                        return;
                    }
                    return a.join('');
                }
                return 'null';
            },
            'undefined': function (x) {
                return 'null';
            }
        };

    var f = s[typeof v];
    if (f) {
        v = f(v);
        if (typeof v == 'string') {
            return v;
        }
    }
    return null;
}

var Jaws_Gadget = (function () {
    var instances = {};

    function newInstance(gadget) {
        var objGadget = new (window['Jaws_Gadget_'+gadget] || Object.constructor);
        objGadget.gadget = objGadget;
        objGadget.gadget.name = gadget;
        objGadget.gadget.defines = jaws[gadget].Defines;
        objGadget.gadget.actions = jaws[gadget].Actions;
        // ajax interface method
        objGadget.gadget.ajax = new JawsAjax(gadget, objGadget.AjaxCallback, objGadget);
        // shout interface method
        objGadget.gadget.shout = function(event, data, destGadget, broadcast) {
            dstGadget = typeof dstGadget !== 'undefined'? dstGadget : '';
            broadcast = typeof broadcast !== 'undefined'? broadcast : true;
            return Jaws_Gadget.shout(objGadget, event, data, dstGadget, broadcast);
        }

        return objGadget;
    }
 
    return {
        // return gadget js object instance
        getInstance: function(gadget) {
            if (!instances[gadget]) {
                instances[gadget] = newInstance(gadget);
            }

            return instances[gadget];
        },

        // call gadget initialize method
        init: function() {
            $.each(instances, function(index, instance) {
                if (instance.init) {
                    instance.init(jaws.Defines.mainGadget, jaws.Defines.mainAction);
                }
            });
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

/**
 * on document ready
 */
$(document).ready(function() {
    $('textarea[role="editor"]').each(function(index) {
        initEditor(this)
    });

    $.each(jaws.Gadgets, function(index, gadget) {
        Jaws_Gadget.getInstance(gadget);
    });

    Jaws_Gadget.init();
});