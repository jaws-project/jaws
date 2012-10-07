/**
 * HTML_AJAX_Serialize_HA  - custom serialization
 *
 * This class is used with the JSON serializer and the HTML_AJAX_Action php class
 * to allow users to easily write data handling and dom manipulation related to
 * ajax actions directly from their php code
 *
 * See Main.js for Author/license details
 */
function HTML_AJAX_Serialize_HA() { }
HTML_AJAX_Serialize_HA.prototype =
{
    /**
     *  Takes data from JSON - which should be parseable into a nice array
     *  reads the action to take and pipes it to the right method
     *
     *  @param   string payload incoming data from php
     *  @return   true on success, false on failure
     */
    unserialize: function(payload)
    {
        var actions = eval(payload);
        for(var i = 0; i < actions.length; i++)
        {
            var action = actions[i];
            switch(action.action)
            {
                case 'prepend':
                    this._prependAttr(action.id, action.attributes);
                    break;
                case 'append':
                    this._appendAttr(action.id, action.attributes);
                    break;
                case 'assign':
                    this._assignAttr(action.id, action.attributes);
                    break;
                case 'clear':
                    this._clearAttr(action.id, action.attributes);
                    break;
                case 'create':
                    this._createNode(action.id, action.tag, action.attributes, action.type);
                    break;
                case 'replace':
                    this._replaceNode(action.id, action.tag, action.attributes);
                    break;
                case 'remove':
                    this._removeNode(action.id);
                    break;
                case 'script':
                    this._insertScript(action.data);
                    break;
                case 'alert':
                    this._insertAlert(action.data);
                    break;
            }
        }
    },
    /* Dispatch Methods */
    _prependAttr: function(id, attributes)
    {
        var node = document.getElementById(id);
        this._setAttrs(node, attributes, 'prepend');
    },
    _appendAttr: function(id, attributes)
    {
        var node = document.getElementById(id);
        this._setAttrs(node, attributes, 'append');
    },
    _assignAttr: function(id, attributes)
    {
        var node = document.getElementById(id);
        this._setAttrs(node, attributes);
    },
    _clearAttr: function(id, attributes)
    {
        var node = document.getElementById(id);
        for(var i = 0; i < attributes.length; i++)
        {
            if(attributes[i] == 'innerHTML')
            {
                HTML_AJAX_Util.setInnerHTML(node, '');
            }
            // value can't be removed
            else if(attributes[i] == 'value')
            {
                node.value = '';
            }
            // I'd use hasAttribute first but IE is stupid stupid stupid
            else
            {
                try
                {
                    node.removeAttribute(attributes[i]);
                }
                catch(e)
                {
                    node[i] = undefined;
                }
            }
        }
    },
    _createNode: function(id, tag, attributes, type)
    {
        var newnode = document.createElement(tag);
        this._setAttrs(newnode, attributes);
        switch(type)
        {
            case 'append':
                document.getElementById(id).appendChild(newnode);
                break
            case 'prepend':
                var parent = document.getElementById(id);
                var sibling = parent.firstChild;
                parent.insertBefore(newnode, sibling);
                break;
            case 'insertBefore':
                var sibling = document.getElementById(id);
                var parent = sibling.parentNode;
                parent.insertBefore(newnode, sibling);
                break;
            //this one is tricky, if it's the last one we use append child...ewww
            case 'insertAfter':
                var sibling = document.getElementById(id);
                var parent = sibling.parentNode;
                var next = sibling.nextSibling;
                if(next == null)
                {
                    parent.appendChild(newnode);
                }
                else
                {
                    parent.insertBefore(newnode, next);
                }
                break;
        }
    },
    _replaceNode: function(id, tag, attributes)
    {
        var node = document.getElementById(id);
        var parent = node.parentNode;
        var newnode = document.createElement(tag);
        this._setAttrs(newnode, attributes);
        parent.replaceChild(newnode, node);
    },
    _removeNode: function(id)
    {
        var node = document.getElementById(id);
        if(node)
        {
            var parent = node.parentNode;
            parent.removeChild(node);
        }
    },
    _insertScript: function(data)
    {
        eval(data);
    },
    _insertAlert: function(data)
    {
        alert(data);
    },
    /* Helper Methods */
    // should we move this to HTML_AJAX_Util???, just does the - case which we need for style
    _camelize: function(instr)
    {
        var p = instr.split('-');
        var out = p[0];
        for(var i = 1; i < p.length; i++) {
            out += p[i].charAt(0).toUpperCase()+p[i].substring(1);
        }
        return out;
    },
    _setAttrs: function(node, attributes, type)
    {
        switch(type)
        {
            case 'prepend':
                for (var i in attributes)
                {
                    // innerHTML is extremely flakey - use util method for it
                    if(i == 'innerHTML')
                    {
                        HTML_AJAX_Util.setInnerHTML(node, attributes[i], 'append');
                    }
                    //IE doesn't support setAttribute on style so we need to break it out and set each property individually
                    else if(i == 'style')
                    {
                        var styles = [];
                        if(attributes[i].indexOf(';'))
                        {
                            styles = attributes[i].split(';');
                        }
                        else
                        {
                            styles.push(attributes[i]);
                        }
                        for(var i = 0; i < styles.length; i++)
                        {
                            var r = styles[i].match(/^\s*(.+)\s*:\s*(.+)\s*$/);
                            if(r)
                            {
                                node.style[this._camelize(r[1])] = r[2] + node.style[this._camelize(r[1])];
                            }
                        }
                    }
                    else
                    {
                        try
                        {
                            node[i] = attributes[i] + node[i];
                        }
                        catch(e){}
                        node.setAttribute(i, attributes[i] + node[i]);
                    }
                }
                break;
            case 'append':
            {
                for (var i in attributes)
                {
                    // innerHTML is extremely flakey - use util method for it
                    if(i == 'innerHTML')
                    {
                        HTML_AJAX_Util.setInnerHTML(node, attributes[i], 'append');
                    }
                    //IE doesn't support setAttribute on style so we need to break it out and set each property individually
                    else if(i == 'style')
                    {
                        var styles = [];
                        if(attributes[i].indexOf(';'))
                        {
                            styles = attributes[i].split(';');
                        }
                        else
                        {
                            styles.push(attributes[i]);
                        }
                        for(var i = 0; i < styles.length; i++)
                        {
                            var r = styles[i].match(/^\s*(.+)\s*:\s*(.+)\s*$/);
                            if(r)
                            {
                                node.style[this._camelize(r[1])] = node.style[this._camelize(r[1])] + r[2];
                            }
                        }
                    }
                    else
                    {
                        try
                        {
                            node[i] = node[i] + attributes[i];
                        }
                        catch(e){}
                        node.setAttribute(i, node[i] + attributes[i]);
                    }
                }
                break;
            }
            default:
            {
                for (var i in attributes)
                {
                    //innerHTML hack bailout
                    if(i == 'innerHTML')
                    {
                        HTML_AJAX_Util.setInnerHTML(node, attributes[i]);
                    }
                    else if(i == 'style')
                    {
                        var styles = [];
                        if(attributes[i].indexOf(';'))
                        {
                            styles = attributes[i].split(';');
                        }
                        else
                        {
                            styles.push(attributes[i]);
                        }
                        for(var i = 0; i < styles.length; i++)
                        {
                            var r = styles[i].match(/^\s*(.+)\s*:\s*(.+)\s*$/);
                            if(r)
                            {
                                node.style[this._camelize(r[1])] = r[2];
                            }
                        }
                    }
                    else
                    {
                        try
                        {
                            node[i] = attributes[i];
                        }
                        catch(e){}
                        node.setAttribute(i, attributes[i]);
                    }
                }
            }
        }
    }
}
