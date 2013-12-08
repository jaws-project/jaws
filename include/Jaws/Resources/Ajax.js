/**
 * General Javascript methods
 *
 * @category    Ajax
 * @package     Core
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2004-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Ajax base class
 */
var JawsAjax = new Class({
    /**
     * Initiates the Ajax
     *
     * @param   string  gadget      Gadget Name
     * @param   object  callback    Callback functions
     * @param   string  baseScript  Base URL
     * @return  void
     */
    initialize: function (gadget, callback, baseScript) {
        this.gadget = gadget;
        this.callback = callback;
        this.loadingMessage = 'Loading...';
        var reqValues = $(document).getElement('meta[name=application-name]').getProperty('content').split(':');
        this.mainRequest = {'base': reqValues[0], 'gadget': reqValues[1], 'action': reqValues[2]};
        this.baseScript  = (baseScript === undefined)? this.mainRequest['base'] : baseScript;
        this.baseURL = this.baseScript + '?gadget=' + this.gadget + '&restype=json&action=';
        this.msgBox  = (this.mainRequest['gadget']+'_'+ this.mainRequest['action']+'_'+'response').toLowerCase();
    },

    /**
     * Set message/response box
     *
     * @param   string  msgBox  Message box ID
     * @return  void
     */
    setMessageBox: function (msgBox) {
        this.msgBox = msgBox.toLowerCase();
    },

    /**
     * Performs asynchronous Ajax request
     *
     * @param   string  action  Name of the new JawsAjax( to be executed
     * @param   object  params  Parameters passed to the function (optional)
     * @return  void
     */
    callAsync: function (action, params) {
        var options = {};
        options.url = this.baseURL + action;
        options.action = action;
        options.async = true;
        if (arguments.length > 2 || (typeOf(arguments[1]) != 'object' && typeOf(arguments[1]) != 'array')) {
            params = Array.prototype.slice.call(arguments, 1);
        }
        options.data = toJSON(params);
        options.urlEncoded = false;
        options.headers = {'content-type' : 'application/json; charset=utf-8'};
        options.onRequest  = this.onRequest.bind(this);
        options.onSuccess  = this.onSuccess.bind(this, options);
        options.onFailure  = this.onFailure.bind(this, options);
        options.onComplete = this.onComplete.bind(this, options);
        new Request(options).send();
    },

    /**
     * Performs synchronous Ajax request
     *
     * @param   string  action  Name of the action to be executed
     * @param   object  params  Parameters passed to the function (optional)
     * @return  mixed   Response text on synchronous mode or void otherwise
     */
    callSync: function (action, params) {
        var options = {};
        options.async = false;
        options.url = this.baseURL + action;
        options.action = action;
        if (arguments.length > 2 || (typeOf(arguments[1]) != 'object' && typeOf(arguments[1]) != 'array')) {
            params = Array.prototype.slice.call(arguments, 1);
        }
        options.data = toJSON(params);
        options.urlEncoded = false;
        options.headers = {'content-type' : 'application/json; charset=utf-8'};
        options.onRequest = this.onRequest.bind(this);
        options.onComplete = this.onComplete.bind(this, options);
        var req = new Request(options).send();
        return eval('(' + req.response.text + ')');
    },

    onRequest: function () {
        this.showLoading(true);
    },

    onSuccess: function (reqOptions, responseText) {
        responseText = eval('(' + responseText + ')');
        var reqMethod = this.callback[reqOptions.action];
        if (reqMethod) {
            reqMethod(responseText);
        }
    },

    onFailure: function () {
        // TODO: alert failure message
    },

    onComplete: function () {
        this.showLoading(false);
    },

    showResponse: function (response) {
        $(this.msgBox).getParent().setStyles({'position': 'absolute', 'display': 'block'});
        $(this.msgBox).set({'html': response.text, 'class': response.type});
        $(this.msgBox).fade('show');
        (function(){
            this.fade('out');
            (function(){this.set('class', '')}).delay(500, this);
        }).delay(4000, $(this.msgBox));
    },

    showLoading: function (show) {
        if ($(this.msgBox)) {
            if (show) {
                $(this.msgBox).getParent().setStyles({'position': 'absolute', 'display': 'block'});
                $(this.msgBox).set({'html': this.loadingMessage, 'class': 'response_loading'});
                $(this.msgBox).fade('show');
            } else {
                $(this.msgBox).set('class', '');
            }
        }
    }

});

/**
 * Jaws HTML5 wrapper of local storage
 */
var JawsStorage = new Class({
    /**
     * Initiates the storage
     *
     * @param   string  gadget  Gadget Name
     * @return  void
     */
    initialize: function (gadget) {
        this.gadget = gadget;
        this.html5Support = 'localStorage' in window;
        if (this.html5Support) {
            this.storage = localStorage;
        } else {
            this.storage = window.Cookie;
        }
    },

    /**
     * Updates the storage key value
     *
     * @param   string  key     Key name
     * @param   mixed   value   Key value
     * @return  void
     */
    update: function (key, value, section) {
        key = (section? section : this.gadget) + '_' + key;
        if (this.html5Support) {
            this.storage.setItem(key, JSON.encode(value));
        } else {
            this.storage.write(key, JSON.encode(value));
        }
    },

    /**
     * fetchs value of the storage key
     *
     * @param   string  key     Key name
     * @return  mixed   Stored value of key
     */
    fetch: function (key, section) {
        key = (section? section : this.gadget) + '_' + key;
        if (this.html5Support) {
            return JSON.decode(this.storage.getItem(key));
        } else {
            return JSON.decode(this.storage.read(key));
        }
    },

    /**
     * deletes a storage key
     *
     * @param   string  key     Key name
     * @return  void
     */
    'delete': function (key, section) {
        key = (section? section : this.gadget) + '_' + key;
        if (this.html5Support) {
            this.storage.removeItem(key);
        } else {
            this.storage.dispose(key);
        }
    }

});

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
            $(name).value = value;
         }
    } else if (usingCKE) {
        var editor = CKEDITOR.instances[name];
        if (editor.status == 'unloaded') {
            $(name).value = value;
        } else {
            editor.setData(value);
        }
    } else {
        $(name).value = value;
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

    return $(name).value;
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
    $(name).reset();
    $(name).fillWithArray(data);
    if (rowsSize != undefined) {
        $(name).rowsSize = rowsSize;
    }
    $(name).updatePageCounter();
    $(name).repaint();
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
    if ($(name) == undefined || objectName == undefined) {
        return true;
    }

    $(name).objectName = objectName;
    if (dataFunc == undefined) {
        JawsDataGrid.name = name;
        $(name + '_pagerFirstAnchor').onclick = function() {
            JawsDataGrid.getFirstValues();
        };

        $(name + '_pagerPreviousAnchor').onclick = function() {
            JawsDataGrid.getPreviousValues();
        };

        $(name + '_pagerNextAnchor').onclick = function() {
                JawsDataGrid.getNextValues();
        };

        $(name + '_pagerLastAnchor').onclick = function() {
                JawsDataGrid.getLastValues();
        };

        getDG();
    } else {
        $(name).dataFunc = dataFunc;

        $(name + '_pagerFirstAnchor').onclick = function() {
            var offset = $(name).getFirstPagerValues();
            getDG(name, offset);
            $(name).firstPage();
        };

        $(name + '_pagerPreviousAnchor').onclick = function() {
            var offset = $(name).getPreviousPagerValues();
            getDG(name, offset);
            $(name).previousPage();
        };

        $(name + '_pagerNextAnchor').onclick = function() {
            var offset = $(name).getNextPagerValues();
            getDG(name, offset);
            $(name).nextPage();
        };

        $(name + '_pagerLastAnchor').onclick = function() {
            var offset = $(name).getLastPagerValues();
            getDG(name, offset);
            $(name).lastPage();
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
        dataFunc = eval($(name).dataFunc);

        if (offset == undefined) {
            var offset = $(name).getCurrentPage();
        }

        reset = (reset == true) || ($(name).rowsSize == 0);
        dataFunc(name, offset, reset);
        if (reset && offset == undefined) {
            $(name).setCurrentPage(0);
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
    dpTable = $(name + '_table');
    var script = dpTable.nextSibling;
    var newScript = document.createElement('script');
    newScript.type = "text/javascript";
    newScript.text = script.text;
    Element.destroy(script);
    dpTable.parentNode.appendChild(newScript);
}

/**
 * Show Dialog Box
 */
function showDialogBox(name, dTitle, url, dHeight, dWidth)
{
    var dRect = document.getSize();
    var dLeft = (dWidth  > dRect.x )? 0 : Math.round(dRect.x  / 2 - dWidth  / 2) + 'px';
    var dTop  = (dHeight > dRect.y)? 0 : Math.round(dRect.y / 2 - dHeight / 2) + 'px';

    if ($(name) == undefined) {
        var overlay = new Element('div', {'id':name+'_overlay', 'class':'dialog_box_overlay'}).hide();
        var iframe  = new IFrame({
            src : url,
            id: name + '_iframe',

            styles: {
                height: dHeight+'px',
                width: dWidth+'px',
                border: 'none'
            },

            events: {
                load: function(){
                    hideWorkingNotification();
                    this.getParent().show('block');
                }
            }
        });

        //var iframe  = new Element('iframe', {'id':name+'_iframe', frameborder:0});
        var close   = new Element('span', {'class': 'dialog_box_close'});
        var title   = new Element('div', {'class':'dialog_box_title'}).appendText(dTitle).adopt(close);
        var dialog  = new Element('div', {'id':name, 'class':'dialog_box'}).adopt(title).adopt(iframe).hide();
        // iframe.addEvent('load', function() {
            // hideWorkingNotification();
            // this.getParent().show('block');
            // Event.addEvent(iframe.contentWindow.document, 'keydown', function(e) {
                // if (e.keyCode == Event.KEY_ESC) {
                    // hideDialogBox(this.getParent());
                // }
            // });
        // });
        // iframe.addEvent('cached:load', function() {
            // hideWorkingNotification();
            // dialog.show('block');
        // });
        close.addEvent('click', function() {hideDialogBox(name);});
        overlay.addEvent('mousedown', function(e) {e.stop();});
         document.addEvent('keydown', function(e) {
            var dialog = document.body.getLast();
            if (e.keyCode == Event.KEY_ESC && dialog.isVisible()) {
                hideDialogBox(dialog.id);
            }
        });
        $(document.body).adopt(overlay);
        document.body.adopt(dialog);
        dialog.setStyles({left:dLeft, top:dTop});
    }

    $(name+'_overlay').show('block');
    showWorkingNotification();
    if ($(name+'_iframe').src == url) {
        $(name+'_iframe').fireEvent('load');
    } else {
        $(name+'_iframe').src = url;
    }
}

/**
 * Hide Dialog Box
 */
function hideDialogBox(name)
{
    $(name).hide();
    $(name+'_overlay').hide();
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
 * Show the response
 */
function showResponse(text, goTop)
{
    if (typeof(goTop) == 'undefined' || goTop) {
        $(document.body).scrollTo(0, 0);
    }

    var messages = [];
    if (text[0] == undefined) {
        messages[0] = text;
    } else {
        messages = text;
    }

    $('msgbox-wrapper').innerHTML = '';
    for(var i = 0; i < messages.length; i++) {
        var messageDiv  = new Element(
            'div',
            {'id':'msgbox_'+i, 'class':messages[i]['type']}
        ).appendText(messages[i]['text']);
        $('msgbox-wrapper').appendChild(messageDiv);
        messageDiv.fade('show');
        hideResponseBox(messageDiv);
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
    $('working_notification').innerHTML = msg;
    $('working_notification').style.visibility = 'visible';
}

/**
 * Hide working notification
 */
function hideWorkingNotification()
{
    $('working_notification').style.visibility = 'hidden';
}

/* Copyright (c) 2005 JSON.org */
function toJSON(v) {
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