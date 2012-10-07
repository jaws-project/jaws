/***************************************************************************

    These functions can be used to serialize and unserialize data in a
    format compatible with PHP's native serialization functions.
    
    Copyright (C) 2005 Arpad Ray <arpad@rajeczy.com>

    This library is free software; you can redistribute it and/or
    modify it under the terms of the GNU Lesser General Public
    License as published by the Free Software Foundation; either
    version 2.1 of the License, or (at your option) any later version.

    This library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
    Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public
    License along with this library; if not, write to:
    Free Software Foundation, Inc.,
    51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

***************************************************************************/

// {{{ var HTML_AJAX_Serialize_PHP
var HTML_AJAX_Serializer_PHP = {
    
    var defaultEncoding = 'UTF-8';
    // {{{ serialize
    /**
     *  Serializes a variable
     *
     *  @param     mixed  inp the variable to serialize
     *  @return    string   a string representation of the input, 
     *                      which can be reconstructed by unserialize()
     *  @author Arpad Ray <arpad@rajeczy.com>
     *  @author David Coallier <davidc@php.net>
     *  @version 0.0.2
     */
    serialize: function(inp) {
        var type = HTML_AJAX_Serializer_PHP.gettype(inp);
        var val;
        switch (type) {
        case "undefined":
            val = "N";
            break;
        case "boolean":
            val = "b:" + (inp ? "1" : "0");
            break;
        case "number":
            val = (Math.round(inp) == inp ? "i" : "d") + ":" + inp;
            break;
        case "string":
            val = "s:" + inp.length + ":\"" + inp + "\"";
            break;
        case "array":
            val = "a";
        case "object":
            if (type == "object") {
                var objname = inp.constructor.toString().match(/(\w+)\(\)/);
                if (objname == undefined) {
                    return;
                }
                objname[1] = HTML_AJAX_Serializer_PHP.serialize(objname[1]);
                val = "O" + objname[1].substring(1, objname[1].length - 1);
            }
            var count = 0;
            var vals = "";
            var okey;
            for (key in inp) {
                okey = (key.match(/^[0-9]+$/) ? parseInt(key) : key);
                vals += HTML_AJAX_Serializer_PHP.serialize(okey) + 
                        HTML_AJAX_Serializer_PHP.serialize(inp[key]);
                count++;
            }
            val += ":" + count + ":{" + vals + "}";
            break;
        }
        if (type != "object" && type != "array") val += ";";
        return val;
    }
    // }}}
    // {{{ unserialize
    /**
     *  Reconstructs a serialized variable
     *
     *  @param    mixed inp the string to reconstruct
     *  @return   mixed the variable represented by the input string
     *  @author Arpad Ray <arpad@rajeczy.com>
     *  @version 0.0.2
     */
    unserialize: function(inp) {

        if (inp == "" || inp.length < 2) {
            unserializer.raiseError("input is too short");
            return;
        }
        var val, kret, vret, cval;
        var type = inp.charAt(0);
        var cont = inp.substring(2);
        var size = 0;
        var divpos = 0;
        var endcont = 0;
        var rest = "";
        var next = "";

        switch (type) {
        case "N": // null
            if (inp.charAt(1) != ";") {
                unserializer.raiseError("missing ; for null", cont);
            }
            // leave val undefined
            rest = cont;
            break;
        case "b": // boolean
            if (!/[01];/.test(cont.substring(0,2))) {
                unserializer.raiseError("value not 0 or 1, or missing ; for boolean", cont);
            }
            val = (cont.charAt(0) == "1");
            rest = cont.substring(1);
            break;
        case "s": // string
            val = "";
            divpos = cont.indexOf(":");
            if (divpos == -1) {
                unserializer.raiseError("missing : for string", cont);
                break;
            }
            size = parseInt(cont.substring(0, divpos));
            if (size == 0) {
                if (cont.length - divpos < 4) {
                    unserializer.raiseError("string is too short", cont);
                    break;
                }
                rest = cont.substring(divpos + 4);
                break;
            }
            if ((cont.length - divpos - size) < 4) {
                unserializer.raiseError("string is too short", cont);
                break;
            }
            if (cont.substring(divpos + 2 + size, divpos + 4 + size) != "\";") {
                unserializer.raiseError("string is too long, or missing \";", cont);
            }
            val = cont.substring(divpos + 2, divpos + 2 + size);
            rest = cont.substring(divpos + 4 + size);
            break;
        case "i": // integer
        case "d": // float
            var dotfound = 0;
            for (var i = 0; i < cont.length; i++) {
                cval = cont.charAt(i);
                if (isNaN(parseInt(cval)) && !(type == "d" && cval == "." && !dotfound++)) {
                    endcont = i;
                    break;
                }
            }
            if (!endcont || cont.charAt(endcont) != ";") {
                unserializer.raiseError("missing or invalid value, or missing ; for int/float", cont);
            }
            val = cont.substring(0, endcont);
            val = (type == "i" ? parseInt(val) : parseFloat(val));
            rest = cont.substring(endcont + 1);
            break;
        case "a": // array
            if (cont.length < 4) {
                unserializer.raiseError("array is too short", cont);
                return;
            }
            divpos = cont.indexOf(":", 1);
            if (divpos == -1) {
                unserializer.raiseError("missing : for array", cont);
                return;
            }
            size = parseInt(cont.substring(1, divpos - 1));
            cont = cont.substring(divpos + 2);
            val = new Array();
            if (cont.length < 1) {
                unserializer.raiseError("array is too short", cont);
                return;
            }
            for (var i = 0; i + 1 < size * 2 && !unserializer.error; i += 2) {
                kret = unserialize(cont, 1);
                if (unserializer.error || kret[0] == undefined || kret[1] == "") {
                    unserializer.raiseError("missing or invalid key, or missing value for array", cont);
                    break;
                }
                vret = unserialize(kret[1], 1);
                if (unserializer.error) {
                    unserializer.raiseError("invalid value for array", cont);
                    break;
                }
                val[kret[0]] = vret[0];
                cont = vret[1];
            }
            if (unserializer.error) {
                return;
            }
            if (cont.charAt(0) != "}") {
                unserializer.raiseError("missing ending }, or too many values for array", cont);
                return; 
            }
            rest = cont.substring(1);
            break;
        case "O": // object
            divpos = cont.indexOf(":");
            if (divpos == -1) {
                unserializer.raiseError("missing : for object", cont);
                return;
            }
            size = parseInt(cont.substring(0, divpos));
            var objname = cont.substring(divpos + 2, divpos + 2 + size);
            if (cont.substring(divpos + 2 + size, divpos + 4 + size) != "\":") {
                unserializer.raiseError("object name is too long, or missing \":", cont);
                return;
            }
            var objprops = unserialize("a:" + cont.substring(divpos + 4 + size), 1);
            if (unserializer.error) {
                unserializer.raiseError("invalid object properties", cont);
                return;
            }
            rest = objprops[1];
            var objout = "function " + objname + "(){";
            for (key in objprops[0]) {
                objout += "this." + key + "=objprops[0]['" + key + "'];";
            }
            objout += "}val=new " + objname + "();";
            eval(objout);
            break;
        default:
            unserializer.raiseError("invalid input type", cont);
        }
        return (arguments.length == 1 ? val : [val, rest]);
    }
    // }}}
    // {{{ gettype
    /**
     *  Returns the type of a variable or its primitive equivalent
     *
     *  @param      inp
     *    the input variable
     *  @return     mixed
     *    a string as returned by typeof
     *  @author
     *    Arpad Ray <arpad@rajeczy.com>
     *  @version
     *    2005/9/29
     */
    gettype: function(inp) {
        var type = typeof inp;
        if (type == "object") {
            var cons = inp.constructor.toString().toLowerCase();
            var types = ["boolean", "number", "string", "array"];
            for (key in types) {
                if (cons.indexOf(types[key]) != -1) {
                    type = types[key];
                    break;
                }
            }
        }
        return type;
    }
    // }}}
}
// }}}
function Unserializer()
{
    this.error = 0;
    this.message = "";
    this.cont = "";
}

Unserializer.prototype.unserialize = function(inp)
{
    this.error = 0;
    return unserialize(inp);
}

Unserializer.prototype.getError = function()
{
    return this.message + "\n" + this.cont;
}

Unserializer.prototype.raiseError = function(message, cont)
{
    this.error = 1;
    this.message = message;
    this.cont = cont;
}

