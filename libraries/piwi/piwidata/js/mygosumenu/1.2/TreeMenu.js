/*
 * DO NOT REMOVE THIS NOTICE
 *
 * PROJECT:   mygosuMenu
 * VERSION:   1.2.0
 * COPYRIGHT: (c) 2003,2004 Cezary Tomczak
 * LINK:      http://gosu.pl/dhtml/mygosumenu.html
 * LICENSE:   BSD (revised)
 */

function TreeMenu(id) {

    this.init = function() {
        if (!document.getElementById(this.id)) {
            alert("Element '"+this.id+"' does not exist in this document. TreeMenu cannot be initialized");
            return;
        }
        this.parse(document.getElementById(this.id).childNodes, this.tree, this.id);
    }

    this.parse = function(nodes, tree, id) {
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].nodeType != 1) {
                continue;
            }
            if (nodes[i].tagName.toLowerCase() == "li") {
                nodes[i].id = id + "-" + tree.length;
                tree[tree.length] = new Array();
                if (nodes[i].childNodes && this.hasUl(nodes[i].childNodes)) {
                    nodes[i].className = "section";
                    var a;
                    if (a = this.getA(nodes[i].childNodes)) {
                        a.id = nodes[i].id + "-a";
                        eval("document.getElementById('"+a.id+"').onclick = function() {"+
                            "self.click('"+nodes[i].id+"');"+
                        "}");
                    }
                } else {
                    nodes[i].className = "box";
                }
            }
            if (nodes[i].tagName.toLowerCase() == "ul") {
                nodes[i].style.display = "none";
                id = id + "-" + (tree.length - 1);
                nodes[i].id = id + "-section";
                tree = tree[tree.length - 1];
            }
            if (nodes[i].childNodes) {
                this.parse(nodes[i].childNodes, tree, id);
            }
        }
    }

    this.hasUl = function(nodes) {
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].nodeType != 1) {
                continue;
            }
            if (nodes[i].tagName.toLowerCase() == "ul") {
                return true;
            }
            if (nodes[i].childNodes) {
                if (this.hasUl(nodes[i].childNodes)) {
                    return true;
                }
            }
        }
        return false;
    }

    this.getA = function(nodes) {
        for (var i = 0; i < nodes.length; i++) {
            if (nodes[i].nodeType == 1) {
                if (nodes[i].tagName.toLowerCase() == "a") {
                    return nodes[i];
                }
                return false;
            }
        }
    }

    this.click = function(id) {
        e1 = document.getElementById(id + "-section");
        e2 = document.getElementById(id);
        if (e1.style.display == "none") {
            e1.style.display = "";
            e2.className = "section-open";
        } else {
            e1.style.display = "none";
            e2.className = "section";
        }
    }

    var self = this;
    this.id = id;
    this.tree = new Array();
    this.init();
}