/**
 * General Javascript methods
 *
 * @category    Ajax
 * @package     Core
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2004-2014 Jaws Development Group
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

(function($){
    var loadedScripts = {};
    jQuery.loadScript = function(path, fn) {
        if (loadedScripts[path]) {
            fn();
            return;
        }

        $('<script>').appendTo('head').on('load readystatechange', function() {
            $(this).off('load readystatechange');
            loadedScripts[path] = true;
            fn();
        }).on('errot', function() {
            //on error
        }).prop('async', true).attr('src', path);
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
function JawsAjax(gadget, callback, baseScript)
{
    this.gadget = gadget;
    this.callback = callback;
    this.loadingMessage = '...';
    var reqValues = $('meta[name=application-name]').attr('content').split(':');
    this.mainRequest = {'base': reqValues[0], 'gadget': reqValues[1], 'action': reqValues[2]};
    this.baseScript  = (baseScript === undefined)? this.mainRequest['base'] : baseScript;
    this.baseURL = this.baseScript + '?gadget=' + this.gadget + '&restype=json&action=';
    this.msgBox  = "#"+(this.mainRequest['gadget']+'_'+ this.mainRequest['action']+'_'+'response').toLowerCase();

    /**
     * Supports backward ajax mechanism
     *
     * @param   string  baseScript  Base URL
     * @return  void
     */
    this.backwardSupport = function (baseScript) {
        baseScript = (baseScript === undefined)?$('meta[name=script]').attr('content') : baseScript;
        this.baseURL = baseScript + '?gadget=' + this.gadget + '&restype=json&action=';
    };

    /**
     * Performs asynchronous Ajax request
     *
     * @param   string  action  Name of the new JawsAjax( to be executed
     * @param   object  data    Parameters passed to the function (optional)
     * @return  void
     */
    this.callAsync = function (action, data, done) {
        var options  = {};
        options.done = done;
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
        options.done = done;
        options.url = this.baseURL + action;
        options.type = 'POST';
        options.async = false;
        options.action = action;
        options.data = $.encodeJSON((/boolean|number|string/).test(typeof data)? [data] : data);
        options.contentType = 'application/json; charset=utf-8';
        options.beforeSend = this.onSend.bind(this, options);
        options.complete = this.onComplete.bind(this, options);
        var result = $.ajax(options);
        return eval('(' + result.responseText + ')');
    };

    this.onSend = function () {
        // TODO: start loading..
    };

    this.onSuccess = function (reqOptions, data, textStatus, jqXHR) {
        response = eval('(' + jqXHR.responseText + ')');
        // call inline user define function
        if (reqOptions.done) {
            reqOptions.done(response);
        }
        var reqMethod = this.callback[reqOptions.action];
        if (reqMethod) {
            reqMethod(response);
        }
    };

    this.onError = function (reqOptions, jqXHR, textStatus, errorThrown) {
        // TODO: alert error message
    };

    this.onComplete = function (reqOptions, jqXHR, textStatus) {
        // TODO: stop loading..
    };

    this.showResponse = function (response) {
        $(this.msgBox).parent().css({'position': 'absolute', 'display': 'block'});
        $(this.msgBox).html(response.text).attr('class', response.type);
        $(this.msgBox).fadeIn().delay(4000).fadeOut(1000, function() {$(this).removeClass();});
    };

    this.showLoading = function (show) {
        if ($(this.msgBox)) {
            if (show) {
                $(this.msgBox).parent().css({'position': 'absolute', 'display': 'block'});
                $(this.msgBox).html(this.loadingMessage).attr('class', 'response_loading');
                $(this.msgBox).fadeIn();
            } else {
                $(this.msgBox).removeClass();
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
 * Change the value of the editor/textarea
 */
function changeEditorValue(name, value)
{
    var usingMCE = typeof tinyMCE  == 'undefined' ? false : true;
    var usingCKE = typeof CKEDITOR == 'undefined' ? false : true;
    if (usingMCE) {
        var editor = tinyMCE.get(name);
        if (editor) {
            editor.setContent(value);
         } else {
            $('#'+name)[0].value = value;
         }
    } else if (usingCKE) {
        var editor = CKEDITOR.instances[name];
        if (editor.status == 'unloaded') {
            $('#'+name)[0].value = value;
        } else {
            editor.setData(value);
        }
    } else {
        $('#'+name)[0].value = value;
    }
}

/**
 * Get the value of the editor/textarea
 */
function getEditorValue(name)
{
    var usingMCE = typeof tinyMCE  == 'undefined' ? false : true;
    var usingCKE = typeof CKEDITOR == 'undefined' ? false : true;
    if (usingMCE) {
        var editor = tinyMCE.get(name);
        return editor.getContent();
    } else if (usingCKE) {
        var editor = CKEDITOR.instances[name];
        if (editor.status != 'unloaded') {
            return editor.getData();
        }
    }

    return $('#'+name)[0].value;
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
        var firstValues = $(this.name).getFirstPagerValues();
        var ajaxObject  = $(this.name).objectName;
        var result      = ajaxObject.callSync('getData', firstValues, $(this.name).id);
        resetGrid($(this.name), result);
        $(this.name).firstPage();
    },

    /**
     * Get the previous Values and prepares the datagrid
     */
    getPreviousValues: function() {
        var previousValues = $(this.name).getPreviousPagerValues();
        var ajaxObject     = $(this.name).objectName;
        var result         = ajaxObject.callSync('getData', previousValues, $(this.name).id);
        resetGrid($(this.name), result);
        $(this.name).previousPage();
    },

    /**
     * Get the next Values and prepares the datagrid
     */
    getNextValues: function() {
        var nextValues     = $(this.name).getNextPagerValues();
        var ajaxObject     = $(this.name).objectName;
        var result         = ajaxObject.callSync('getData', nextValues, $(this.name).id);
        resetGrid($(this.name), result);
        $(this.name).nextPage();
    },

    /**
     * Get the last Values and prepares the datagrid
     */
    getLastValues: function() {
        var lastValues = $(this.name).getLastPagerValues();
        var ajaxObject = $(this.name).objectName;
        var result     = ajaxObject.callSync('getData', lastValues, $(this.name).id);
        resetGrid($(this.name), result);
        $(this.name).lastPage();
    },

    /**
     * Only retrieves information with the current page the pager has and prepares the datagrid
     */
    getData: function() {
        var currentPage = $(this.name).getCurrentPage();
        var ajaxObject  = $(this.name).objectName;
        var result      = ajaxObject.callSync('getData', currentPage, $(this.name).id);
        resetGrid($(this.name), result);
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
        dataFunc(name, offset, reset);
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
    dpTable = $('#' + name + '_table')[0];
    var script = dpTable.nextSibling;
    var newScript = document.createElement('script');
    newScript.type = "text/javascript";
    newScript.text = script.text;
    $(script).remove();
    dpTable.parentNode.appendChild(newScript);
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
function hideResponseBox(name, timehide)
{
    if (typeof(timehide) == 'undefined') {
        timehide = '3000';
    }

    setTimeout('hideResponseBoxCallback("' + name.id + '")', timehide);
}

/**
 * Hide response boxes - JS Action (callback)
 */
function hideResponseBoxCallback(name)
{
    $(name).fade('out');
}

/**
 * Show working notification.
 */
function showWorkingNotification(msg)
{
    if (!msg) {
        msg = default_loading_message;
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