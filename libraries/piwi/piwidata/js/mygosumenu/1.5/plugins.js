function DynamicTreePlugins() {
    this.importFromHtml = function(html) {
        // dirty hack for ie (automatic conversion to absolute paths problem), see also DynamicTreeBuilder.parse()
        html = html.replace(/href=["']([^"']*)["']/g, 'href="dynamictree://dynamictree/$1"');
        document.getElementById(this.id).innerHTML = html;
        this.reset();
    };
    this.exportToHtml = function(node) {
        var ret = "";
        if (node) {
            if (node.isDoc) {
                ret += '?<div class="doc"><a href="?"??>?</a></div>\n'.format(
                    " ".repeat(4*(node.getLevel()-1)),
                    node.href,
                    (node.title ? ' title="?"'.format(node.title) : ""),
                    (node.target ? ' target="?"'.format(node.target) : ""),
                    node.text
                );
            }
            if (node.isFolder) {
                ret += '?<div class="folder">?\n'.format(
                    " ".repeat(4*(node.getLevel()-1)),
                    node.text
                );
                for (var i = 0; i < node.childNodes.length; ++i) {
                    ret += this.exportToHtml(node.childNodes[i]);
                }
                ret += '?</div>\n'.format(" ".repeat(4*(node.getLevel()-1)));
            }
        } else {
            var nodes = this.tree.childNodes;
            for (var i = 0; i < nodes.length; ++i) {
                ret += this.exportToHtml(nodes[i]);
            }
        }
        return ret;
    };
    this.exportToPhp = function(node) {
        var ret = "";
        if (node) {
            if (node.childNodes) {
                ret += "?'?' => array(\n".format(
                    " ".repeat(4*node.getLevel()),
                    node.id
                );
                for (var i = 0; i < node.childNodes.length; ++i) {
                    ret += this.exportToPhp(node.childNodes[i]);
                }
                ret += "?)?\n".format(
                    " ".repeat(4*node.getLevel()),
                    node.isLast() ? "" : ","
                );
            } else {
                ret += "?'?' => null?\n".format(
                    " ".repeat(4*node.getLevel()),
                    node.id,
                    node.isLast() ? "" : ","
                );
            }
        } else {
            var nodes = this.tree.childNodes;
            ret += "$tree = array(\n";
            for (var i = 0; i < nodes.length; ++i) {
                ret += this.exportToPhp(nodes[i]);
            }
            ret += ");\n\n";
            ret += "$data = array(\n";
            var cnt = 0, current = 0;
            for (var p in this.allNodes) { 
                if (!this.allNodes[p]) { continue; }
                cnt++;
            }
            for (var p in this.allNodes) {
                if (!this.allNodes[p]) { continue; }
                current++;
                var node = this.allNodes[p];
                ret += "    '?' => array('parent' => '?', 'type' => '?', 'text' => '?', 'href' => '?', 'title' => '?', 'target' => '?')?\n".format(
                    node.id,
                    node.parentNode.id,
                    node.isDoc ? "doc" : "folder",
                    node.text,
                    node.href,
                    node.title,
                    node.target,
                    cnt != current ? "," : ""
                );
            }
            ret += ");";
        }
        return ret;
    };
    this.exportToSql = function() {
        var ret = "";
        for (var p in this.allNodes) {
            if (!this.allNodes[p]) { continue; }
            var node = this.allNodes[p];
            ret += 'INSERT INTO menu (id, parent, type, text, href, title, target) VALUES ("?", "?", "?", "?", "?", "?", "?");\n'.format(
                node.id,
                node.parentNode.id,
                node.isDoc ? "doc" : "folder",
                node.text,
                node.href,
                node.title,
                node.target
            );
        }
        return ret;
    };
}

/* Repeat string n times */
if (!String.prototype.repeat) {
    String.prototype.repeat = function(n) {
        var ret = "";
        for (var i = 0; i < n; ++i) {
            ret += this;
        }
        return ret;
    };
}