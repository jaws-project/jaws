function treeTooltipOn() { document.getElementById("tree-tooltip").innerHTML = treeTooltips[treeElements.indexOf(this.id)]; }
function treeTooltipOff() { document.getElementById("tree-tooltip").innerHTML = ""; }

var treeElements = ["tree-moveUp", "tree-moveDown", "tree-moveLeft", "tree-moveRight", "tree-insert", "tree-info", "tree-remove"];
var treeTooltips = ["Move Up", "Move Down", "Move Left", "Move Right", "Insert", "Info", "Delete"];

for (var i = 0; i < treeElements.length; i++) {
    document.getElementById(treeElements[i]).onmouseover = treeTooltipOn;
    document.getElementById(treeElements[i]).onmouseout = treeTooltipOff;
}

function treeMoveUp() {
    if (tree.mayMoveUp()) {
        tree.moveUp();
    }
}
function treeMoveDown() {
    if (tree.mayMoveDown()) {
        tree.moveDown();
    }
}
function treeMoveLeft() {
    if (tree.mayMoveLeft()) {
        tree.moveLeft();
    }
}
function treeMoveRight() {
    if (tree.mayMoveRight()) {
        tree.moveRight();
    }
}
function treeInsert() {
    treeHideInfo();
    document.getElementById("tree-insert-form").style.display = "block";
    document.getElementById("tree-insert-where-div").style.display = (tree.active ? "" : "none");
    if (tree.active) {
        var where = document.getElementById("tree-insert-where");
        if (tree.mayInsertInside()) {
            if (!where.options[2] && !where.options[3]) {
                where.options[2] = new Option("Inside at start", "inside_start");
                where.options[3] = new Option("Inside at end", "inside_end");
            }
        } else if (where.options[2] && where.options[3]) {
            where.options[2] = null;
            where.options[3] = null;
            where.options.length = 2;
        }
    }
}
function treeHideInsert() {
    var name = document.getElementById("tree-insert-name");
    var href = document.getElementById("tree-insert-href");
    var title = document.getElementById("tree-insert-title");
    var target = document.getElementById("tree-insert-target");
    name.value = "";
    href.value = "";
    title.value = "";
    target.value = "";
    document.getElementById("tree-insert-form").style.display = "none";
}
function treeInfo() {
    treeHideInsert();
    var name = document.getElementById("tree-info-name");
    var href = document.getElementById("tree-info-href");
    var title = document.getElementById("tree-info-title");
    var target = document.getElementById("tree-info-target");
    name.value = "";
    href.value = "";
    title.value = "";
    target.value = "";
    document.getElementById("tree-info-form").style.display = "block";
    if (tree.active) {
        var node = tree.getActiveNode();
        name.value = node.text;
        href.value = node.href;
        title.value = node.title;
        target.value = node.target;
    }
}
function treeInfoUpdate() {
    var name = document.getElementById("tree-info-name");
    var href = document.getElementById("tree-info-href");
    var title = document.getElementById("tree-info-title");
    var target = document.getElementById("tree-info-target");
    name.value = name.value.trim();
    href.value = href.value.trim();
    if (!name.value) {
        return false;
    }
    if (tree.active) {
        var node = tree.getActiveNode();
        node.text = name.value;
        node.href = href.value;
        node.title = title.value;
        node.target = target.value;
        tree.updateHtml();
    }
}
function treeHideInfo() {
    var name = document.getElementById("tree-info-name");
    var href = document.getElementById("tree-info-href");
    var title = document.getElementById("tree-info-title");
    var target = document.getElementById("tree-info-target");
    name.value = "";
    href.value = "";
    title.value = "";
    target.value = "";
    document.getElementById("tree-info-form").style.display = "none";
}

/* only event - blur */
function treeInsertExecute() {
    var where = document.getElementById("tree-insert-where");
    var type = document.getElementById("tree-insert-type");
    var name = document.getElementById("tree-insert-name");
    var href = document.getElementById("tree-insert-href");
    var title = document.getElementById("tree-insert-title");
    var target = document.getElementById("tree-insert-target");
    name.value = name.value.trim();
    href.value = href.value.trim();
    if (!name.value) {
        return false;
    }
    var o = {"href": href.value, "title": title.value, "target": target.value};
    if (tree.active) {
        switch (where.value) {
            case "before":
                tree.insertBefore("tree-"+(++tree.count), name.value, type.value, o);
                break;
            case "after":
                tree.insertAfter("tree-"+(++tree.count), name.value, type.value, o);
                break;
            case "inside_start":
                tree.insertInsideAtStart("tree-"+(++tree.count), name.value, type.value, o);
                break;
            case "inside_end":
                tree.insertInsideAtEnd("tree-"+(++tree.count), name.value, type.value, o);
                break;
        }
    } else {
        tree.insert("tree-"+(++tree.count), name.value, type.value, o);
    }
    name.value = "";
    href.value = "";
    title.value = "";
    target.value = "";
    this.blur();
}
function treeRemove() {
    if (tree.mayRemove()) {
        if (confirm("Delete current node ?")) {
            tree.remove();
            if (document.getElementById("tree-insert-form").style.display == "block") {
                treeInsert();
            }
            if (document.getElementById("tree-info-form").style.display == "block") {
                treeInfo();
            }
        }
    }
}

document.getElementById("tree-moveUp").onclick    = treeMoveUp;
document.getElementById("tree-moveDown").onclick  = treeMoveDown;
document.getElementById("tree-moveLeft").onclick  = treeMoveLeft;
document.getElementById("tree-moveRight").onclick = treeMoveRight;

if (document.all && !/opera/i.test(navigator.userAgent)) {
    document.getElementById("tree-moveUp").ondblclick    = treeMoveUp;
    document.getElementById("tree-moveDown").ondblclick  = treeMoveDown;
    document.getElementById("tree-moveLeft").ondblclick  = treeMoveLeft;
    document.getElementById("tree-moveRight").ondblclick = treeMoveRight;
}

document.getElementById("tree-insert").onclick = treeInsert;
document.getElementById("tree-info").onclick = treeInfo;
document.getElementById("tree-remove").onclick = treeRemove;

document.getElementById("tree-insert-button").onclick = treeInsertExecute;
document.getElementById("tree-insert-cancel").onclick = treeHideInsert;

document.getElementById("tree-info-button").onclick = treeInfoUpdate;
document.getElementById("tree-info-cancel").onclick = treeHideInfo;

tree.textClickListener.add(function() { if (document.getElementById("tree-insert-form").style.display == "block") { treeInsert(); } });
tree.textClickListener.add(function() { if (document.getElementById("tree-info-form").style.display == "block") { treeInfo(); } });

/* Finds the index of the first occurence of item in the array, or -1 if not found */
if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function(item) {
        for (var i = 0; i < this.length; ++i) {
            if (this[i] === item) { return i; }
        }
        return -1;
    };
}

// ---------
// ! PLUGINS
// ---------

function treePluginImportHtml() {
    document.getElementById("tree-plugin").style.display = "block";
    document.getElementById("tree-plugin-header").innerHTML = "Import from Html";
    document.getElementById("tree-plugin-button-import-html").style.display = "block";
}
function treePluginImportHtmlExecute() {
    var html = document.getElementById("tree-plugin-textarea");
    tree.importFromHtml(html.value);
}
function treePluginExportHtml() {
    var w = window.open("", "exportToHtml", "width=600,height=600,scrollbars=yes,resizable=yes");
    w.document.write('<html><body><pre>'+tree.exportToHtml().replace(/</g, "&lt;").replace(/>/g, "&gt;")+'</pre></body></html>');
}
function treePluginExportPhp() {
    var w = window.open("", "exportToPhp", "width=600,height=600,scrollbars=yes,resizable=yes");
    w.document.write('<pre>'+tree.exportToPhp().replace(/</g, "&lt;").replace(/>/g, "&gt;")+'</pre>');
}
function treePluginExportSql() {
    var w = window.open("", "exportToSql", "width=600,height=600,scrollbars=yes,resizable=yes");
    w.document.write('<pre>'+tree.exportToSql().replace(/</g, "&lt;").replace(/>/g, "&gt;")+'</pre>');
}
function treePluginHide() {
    document.getElementById("tree-plugin").style.display = "none";
    document.getElementById("tree-plugin-header").innerHTML = "";
    document.getElementById("tree-plugin-textarea").value = "";
    document.getElementById("tree-plugin-button-import-html").style.display = "none";
}

document.getElementById("tree-plugin-import-html").onclick = function() { this.blur(); treePluginHide(); treePluginImportHtml(); };
document.getElementById("tree-plugin-button-import-html").onclick = treePluginImportHtmlExecute;
document.getElementById("tree-plugin-export-html").onclick = function() { this.blur(); treePluginHide(); treePluginExportHtml(); };
document.getElementById("tree-plugin-export-php").onclick = function() { this.blur(); treePluginHide(); treePluginExportPhp(); };
document.getElementById("tree-plugin-export-sql").onclick = function() { this.blur(); treePluginHide(); treePluginExportSql(); }