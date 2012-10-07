// +---------------------------------------------------------------+
// | DO NOT REMOVE THIS                                            |
// +---------------------------------------------------------------+
// | DynamicTree 1.5.0                                             |
// | Author: Cezary Tomczak [www.gosu.pl]                          |
// | Free for any use as long as all copyright messages are intact |
// +---------------------------------------------------------------+

function DynamicTreeBuilder(id) {
    this.path = "images/";
    this.img = {
        "branch": "tree-branch.gif",
        "doc": "tree-doc.gif",
        "folder": "tree-folder.gif",
        "folderOpen": "tree-folder-open.gif",
        "leaf": "tree-leaf.gif",
        "leafEnd": "tree-leaf-end.gif",
        "node": "tree-node.gif",
        "nodeEnd": "tree-node-end.gif",
        "nodeOpen": "tree-node-open.gif",
        "nodeOpenEnd": "tree-node-open-end.gif" };
    this.cookiePath = "";
    this.cookieDomain = "";
    this.init = function() {
        var p, img;
        for (p in this.img) {
            this.img[p] = this.path + this.img[p];
        }
        for (p in this.img) {
            this.imgObjects.push(new Image());
            this.imgObjects.getLast().src = this.img[p];
            this.img[p] = this.imgObjects.getLast().src;
        }
        this.parse(document.getElementById(this.id).childNodes, this.tree);
        this.loadState();
        if (window.addEventListener) { window.addEventListener("unload", function(e) { self.saveState(); }, false); }
        else if (window.attachEvent) { window.attachEvent("onunload", function(e) { self.saveState(); }); }
        this.updateHtml();
    };
    this.reset = function() {
        this.clearState();
        this.tree = new Node("tree", "", null, new Array(), false, true);
        this.allNodes = {};
        this.opened = [];
        this.active = "";
        this.count = 0;
        this.parse(document.getElementById(this.id).childNodes, this.tree);
        this.updateHtml();
    };
    this.parse = function(nodes, tree) {
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].nodeType == 1) {
                if (!nodes[i].className) { continue; }
                nodes[i].id = this.id + "-" + (++this.count);
                var node = new Node();
                node.id = nodes[i].id;
                if (nodes[i].firstChild) {
                    if (nodes[i].firstChild.tagName == "A") {
                        var a = nodes[i].firstChild;
                        if (a.firstChild) {
                            node.text = a.firstChild.nodeValue.trim();
                        }
                        if (a.href) {
                            // dirty hack for ie (automatic conversion to absolute paths problem), see also DynamicTreePlugins.importFromHtml()
                            var s = a.parentNode.innerHTML.match(/href=["'](dynamictree:\/\/dynamictree\/)?([^"']*)["']/i);
                            if (s) { node.href = s[2]; }
                        }
                        if (a.title) {
                            node.title = a.title;
                        }
                        if (a.target) {
                            node.target = a.target;
                        }
                    } else {
                        node.text = nodes[i].firstChild.nodeValue.trim();
                    }
                }
                node.parentNode = tree;
                node.childNodes = (nodes[i].className == "folder" ? new Array() : null);
                node.isDoc      = (nodes[i].className == "doc");
                node.isFolder   = (nodes[i].className == "folder");
                tree.childNodes.push(node);
                this.allNodes[node.id] = node;
            }
            if (nodes[i].nodeType == 1 && nodes[i].childNodes) {
                this.parse(nodes[i].childNodes, tree.childNodes.getLast());
            }
        }
    };
    this.nodeClick = function(id) {
        var el = document.getElementById(id+"-section");
        var node = document.getElementById(id+"-node");
        var icon = document.getElementById(id+"-icon");
        if (el.style.display == "block") {
            el.style.display = "none";
            if (this.allNodes[id].isLast()) { node.src = this.img.nodeEnd; }
            else { node.src = this.img.node; }
            icon.src = this.img.folder;
            this.opened.removeByValue(id);
        } else {
            el.style.display = "block";
            if (this.allNodes[id].isLast()) { node.src = this.img.nodeOpenEnd; }
            else { node.src = this.img.nodeOpen; }
            icon.src = this.img.folderOpen;
            this.opened.push(id);
        }
        /* fix ie bug - images not showing */
        if (node.outerHTML) { node.outerHTML = node.outerHTML; }
        if (icon.outerHTML) { icon.outerHTML = icon.outerHTML; }
    };
    this.textClick = function(id) {
        if (this.active) {
            document.getElementById(this.active+"-text").className = "text";
        }
        document.getElementById(id+"-text").className = "text-active";
        this.active = id;
        this.textClickListener.call();
    };
    this.toHtml = function() {
        var s = "";
        var nodes = this.tree.childNodes;
        for (var i = 0; i < nodes.length; i++) {
            s += nodes[i].toHtml();
        }
        return s;
    };
    this.updateHtml = function() {
        document.getElementById(this.id).innerHTML = this.toHtml();
    };
    this.loadState = function() {
        var opened = this.cookie.get("opened");
        if (opened) {
            this.opened = opened.split("|");
            this.opened.filter(function(id) { return self.allNodes[id] && self.allNodes[id].isFolder && self.allNodes[id].childNodes.length; });
        }
    };
    this.saveState = function() {
        if (this.opened.length) {
            this.cookie.set("opened", this.opened.join("|"), 3600*24*30, this.cookiePath, this.cookieDomain);
        } else {
            this.clearState();
        }
    };
    this.clearState = function() {
        this.cookie.del("opened");
    };
    this.getActiveNode = function() {
        if (!this.active) { throw "DynamicTreeBuilder.getActiveNode() failed, there is no active node"; }
        return this.allNodes[this.active];
    }
    this.mayMoveUp = function() {
        return this.active && !this.allNodes[this.active].isFirst();
    };
    this.mayMoveDown = function() {
        return this.active && !this.allNodes[this.active].isLast();
    };
    this.mayMoveLeft = function() {
        return this.active && (this.allNodes[this.active].getLevel() > 1);
    };
    this.mayMoveRight = function() {
        if (this.active) {
            var node = this.allNodes[this.active].getNextSibling();
            while (node) {
                if (node.isFolder) { return true; }
                node = node.getNextSibling();
            }
        }
        return false;
    };
    this.mayInsertBefore = function() {
        return Boolean(this.active);
    };
    this.mayInsertAfter = function() {
        return Boolean(this.active);
    };
    this.mayInsertInside = function() {
        return this.active && this.allNodes[this.active].isFolder;
    };
    this.mayRemove = function() {
        if (this.active) {
            var node = this.allNodes[this.active];
            if (node.isDoc) { return true; }
            if (node.isFolder && !node.childNodes.length) { return true; }
        }
        return false;
    };
    this.moveUp = function() {
        var node = this.allNodes[this.active];
        var index = node.getIndex();
        var parent = node.parentNode;
        parent.removeChild(node);
        parent.appendChildAtIndex(node, index-1);
        this.updateHtml();
    };
    this.moveDown = function() {
        var node = this.allNodes[this.active];
        var index = node.getIndex();
        var parent = node.parentNode;
        parent.removeChild(node);
        parent.appendChildAtIndex(node, index+1);
        this.updateHtml();
    };
    this.moveLeft = function() {
        var node = this.allNodes[this.active];
        var left = node.parentNode;
        left.removeChild(node);
        left.parentNode.appendChildAtIndex(node, left.getIndex());
        this.updateHtml();
    };
    this.moveRight = function() {
        var node = this.allNodes[this.active];
        var next = node.getNextSibling();
        var rightId = null;
        while (next) {
            if (next.isFolder) {
                rightId = next.id;
                break;
            }
            next = next.getNextSibling();
        }
        var right = this.allNodes[rightId];
        node.parentNode.removeChild(node);
        if (right.childNodes.length) {
            right.appendChildAtIndex(node, 0);
        } else {
            right.appendChild(node);
        }
        this.updateHtml();
    };
    this.createNode = function(id, text, type, object) {
        if (!id || this.allNodes[id] || !text || (type != "doc" && type != "folder")) {
            throw this.id+'.createNode("'+id+'", "'+text+'", "'+type+'") failed, illegal action';
        }
        var node;
        if (type == "doc") {
            node = new Node(id, text, null, null, true, false);
        } else {
            node = new Node(id, text, null, new Array(), false, true);
        }
        if (object) {
            for (var p in object) {
                node[p] = object[p];
            }
        }
        this.allNodes[id] = node;
        return node;
    };
    this.insert = function(id, text, type, object) {
        var node = this.createNode(id, text, type, object);
        if (this.tree.childNodes.length) {
            this.tree.appendChildAtIndex(node, 0);
        } else {
            this.tree.appendChild(node);
        }
        this.updateHtml();
    };
    this.insertBefore = function(id, text, type, object) {
        if (!this.mayInsertBefore()) {
            throw this.id+'.insertBefore() failed, illegal action';
        }
        var node = this.createNode(id, text, type, object);
        var active = this.allNodes[this.active];
        active.parentNode.appendChildAtIndex(node, active.getIndex());
        this.updateHtml();
    };
    this.insertAfter = function(id, text, type, object) {
        if (!this.mayInsertAfter()) {
            throw this.id+'.insertAfter() failed, illegal action';
        }
        var node = this.createNode(id, text, type, object);
        var active = this.allNodes[this.active];
        if (active.parentNode.childNodes[active.getIndex()+1]) {
            active.parentNode.appendChildAtIndex(node, active.getIndex()+1);
        } else {
            active.parentNode.appendChild(node);
        }
        this.updateHtml();
    };
    this.insertInsideAtStart = function(id, text, type, object) {
        if (!this.mayInsertInside()) {
            throw this.id+'.insertInsideAtStart() failed, illegal action';
        }
        var node = this.createNode(id, text, type, object);
        var active = this.allNodes[this.active];
        if (active.childNodes.length) {
            active.appendChildAtIndex(node, 0);
        } else {
            active.appendChild(node);
        }
        this.updateHtml();
    };
    this.insertInsideAtEnd = function(id, text, type, object) {
        if (!this.mayInsertInside()) {
            throw this.id+'.insertInsideAtEnd() failed, illegal action';
        }
        var node = this.createNode(id, text, type, object);
        var active = this.allNodes[this.active];
        active.appendChild(node);
        this.updateHtml();
    };
    this.remove = function() {
        var node = this.allNodes[this.active];
        node.parentNode.removeChild(node);
        this.allNodes[this.active] = null;
        this.active = "";
        this.updateHtml();
    };
    function Node(id, text, parentNode, childNodes, isDoc, isFolder) {
        this.id = id;
        this.text = text;
        this.parentNode = parentNode;
        this.childNodes = childNodes;
        this.isDoc = isDoc;
        this.isFolder = isFolder;
        this.href = "";
        this.title = "";
        this.target = "";
        this.isFirst = function() {
            if (this.parentNode) {
                return this.parentNode.childNodes[0].id == this.id;
            }
            throw "DynamicTreeBuilder.Node.isFirst() failed, this func cannot be called for the root element";
        };
        this.isLast = function() {
            if (this.parentNode) {
                return this.parentNode.childNodes.getLast().id == this.id;
            }
            throw "DynamicTreeBuilder.Node.isLast() failed, this func cannot be called for the root element";
        };
        this.getLevel = function() {
            var level = 0;
            var node = this;
            while (node.parentNode) {
                level++;
                node = node.parentNode;
            }
            return level;
        };
        this.getNextSibling = function() {
            if (this.parentNode) {
                var nodes = this.parentNode.childNodes;
                var start = false;
                for (var i = 0; i < nodes.length; i++) {
                    if (start) { return nodes[i]; }
                    if (!start && this.id != nodes[i].id) { continue; }
                    start = true;
                }
                return false;
            }
            throw "DynamicTreeBuilder.Node.getNextSibling() failed, this func cannot be called for the root element";
        };
        this.getPreviousSibling = function() {
            if (this.parentNode) {
                var nodes = this.parentNode.childNodes;
                for (var i = 0; i < nodes.length; i++) {
                    if (nodes[i].id == this.id) {
                        if (i) { return nodes[i-1]; }
                        else { return false; }
                    }
                }
                throw "DynamicTreeBuilder.Node.getPreviousSibling() failed, unknown error";
            }
            throw "DynamicTreeBuilder.Node.getPreviousSibling() failed, this func cannot be called for the root element";
        };
        this.getIndex = function() {
            if (this.parentNode) {
                var nodes = this.parentNode.childNodes;
                for (var i = 0; i < nodes.length; i++) {
                    if (nodes[i].id == this.id) { return i; }
                }
                throw "DynamicTreeBuilder.Node.getIndex() failed, unknown error";
            }
            throw "DynamicTreeBuilder.Node.getIndex() failed, this func cannot be called for the root element";
        };
        this.removeChild = function(node) {
            this.childNodes.removeByIndex(node.getIndex());
            node.parentNode = null;
        };
        this.appendChild = function(node) {
            this.childNodes.push(node);
            node.parentNode = this;
        };
        this.appendChildAtIndex = function(node, index) {
            this.childNodes.pushAtIndex(node, index);
            node.parentNode = this;
        };
        this.toHtml = function() {
            var s = '<div class="?" id="?">'.format((this.isFolder ? "folder" : "doc"), this.id);
            if (this.isFolder) {
                var nodeIcon;
                if (this.childNodes.length) {
                    nodeIcon = (self.opened.contains(this.id) ? (this.isLast() ? self.img.nodeOpenEnd : self.img.nodeOpen) : (this.isLast() ? self.img.nodeEnd : self.img.node));
                } else {
                    nodeIcon = (this.isLast() ? self.img.leafEnd : self.img.leaf);
                }
                var icon = ((self.opened.contains(this.id) && this.childNodes.length) ? self.img.folderOpen : self.img.folder);
                if (this.childNodes.length) { s += '<a href="javascript:void(0)" onclick="?.nodeClick(\'?\')">'.format(self.id, this.id); }
                s += '<img id="?-node" src="?" width="18" height="18" alt="" />'.format(this.id, nodeIcon);
                if (this.childNodes.length) { s += '</a>'; }
                s += '<img id="?-icon" src="?" width="18" height="18" alt="" />'.format(this.id, icon);
                s += '<span id="?-text" class="text?" onclick="?.textClick(\'?\')">?</span>'.format(this.id, (self.active == this.id ? '-active' : ''), self.id, this.id, this.text);
                if (this.childNodes.length) {
                    s += '<div class="section?" id="?-section"'.format((this.isLast() ? " last" : ""), this.id);
                    if (self.opened.contains(this.id)) {
                        s += '  style="display: block;"'; }
                    s += '>';
                    for (var i = 0; i < this.childNodes.length; i++) {
                        s += this.childNodes[i].toHtml();
                    }
                    s += '</div>';
                }
            }
            if (this.isDoc) {
                s += '<img src="?" width="18" height="18" alt="" /><img src="?" />'.format((this.isLast() ? self.img.leafEnd : self.img.leaf), self.img.doc);
                s += '<span id="?-text" class="text?" onclick="?.textClick(\'?\')">?</span>'.format(this.id, (self.active == this.id ? '-active' : ''), self.id, this.id, this.text);
            }
            s += '</div>';
            return s;
        };
    }
    function Cookie() {
        this.get = function(name) {
            var cookies = document.cookie.split(";");
            for (var i = 0; i < cookies.length; ++i) {
                var a = cookies[i].split("=");
                if (a.length == 2) {
                    a[0] = a[0].trim();
                    a[1] = a[1].trim();
                    if (a[0] == name) {
                        return unescape(a[1]);
                    }
                }
            }
            return "";
        };
        this.set = function(name, value, seconds, path, domain, secure) {
            var cookie = (name + "=" + escape(value));
            if (seconds) {
                var date = new Date(new Date().getTime()+seconds*1000);
                cookie += ("; expires="+date.toGMTString());
            }
            cookie += (path    ? "; path="+path : "");
            cookie += (domain  ? "; domain="+domain : "");
            cookie += (secure  ? "; secure" : "");
            document.cookie = cookie;
        };
        this.del = function(name) {
            document.cookie = name + "=; expires=Thu, 01-Jan-70 00:00:01 GMT";
        };
    }
    function Listener() {
        this.funcs = [];
        this.add = function(func) {
            this.funcs.push(func);
        };
        this.call = function() {
            for (var i = 0; i < this.funcs.length; i++) {
                this.funcs[i]();
            }
        };
    }
    var self = this;
    this.id = id;
    this.tree = new Node("tree", "", null, new Array(), false, true);
    this.allNodes = {}; // id => object
    this.opened = []; // opened folders
    this.active = ""; // active node, text clicked
    this.cookie = new Cookie();
    this.imgObjects = [];
    this.count = 0;
    this.textClickListener = new Listener(); // other modules also may need to know when user clicks on text
}

/* Check whether array contains given string */
if (!Array.prototype.contains) {
    Array.prototype.contains = function(s) {
        for (var i = 0; i < this.length; ++i) {
            if (this[i] === s) { return true; }
        }
        return false;
    };
}

/* Remove elements with such value (mutates) */
if (!Array.prototype.removeByValue) {
    Array.prototype.removeByValue = function(value) {
        var i, indexes = [];
        for (i = 0; i < this.length; ++i) {
            if (this[i] === value) { indexes.push(i); }
        }
        for (i = indexes.length - 1; i >= 0; --i) {
            this.splice(indexes[i], 1);
        }
    };
}

/* Remove elements judged 'false' by the passed function (mutates) */
if (!Array.prototype.filter) {
    Array.prototype.filter = function(func) {
        var i, indexes = [];
        for (i = 0; i < this.length; ++i) {
            if (!func(this[i])) { indexes.push(i); }
        }
        for (i = indexes.length - 1; i >= 0; --i) {
            this.splice(indexes[i], 1);
        }
    };
}

/* Get the last element from the array */
if (!Array.prototype.getLast) {
    Array.prototype.getLast = function() {
        return this[this.length-1];
    };
}

/* Strip whitespace from the beginning and end of a string */
if (!String.prototype.trim) {
    String.prototype.trim = function() {
        return this.replace(/^\s*|\s*$/g, "");
    };
}

/* Replace ? tokens with variables passed as arguments in a string */
String.prototype.format = function() {
    if (!arguments.length) { throw "String.format() failed, no arguments passed, this = "+this; }
    var tokens = this.split("?");
    if (arguments.length != (tokens.length - 1)) { throw "String.format() failed, tokens != arguments, this = "+this; }
    var s = tokens[0];
    for (var i = 0; i < arguments.length; ++i) {
        s += (arguments[i] + tokens[i + 1]);
    }
    return s;
};

/* Remove element with given index (mutates) */
if (!Array.prototype.removeByIndex) {
    Array.prototype.removeByIndex = function(index) {
        this.splice(index, 1);
    };
}

/* Push an element at specified index */
if (!Array.prototype.pushAtIndex) {
    Array.prototype.pushAtIndex = function(el, index) {
        this.splice(index, 0, el);
    };
}