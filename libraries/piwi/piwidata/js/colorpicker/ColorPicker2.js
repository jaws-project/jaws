// ===================================================================
// Author: Matt Kruse <matt@mattkruse.com>
// WWW: http://www.mattkruse.com/
//
// NOTICE: You may use this code for any purpose, commercial or
// private, without any further permission from the author. You may
// remove this notice from your final code if you wish, however it is
// appreciated by the author if at least my web site address is kept.
//
// You may *NOT* re-distribute this code in any way except through its
// use. That means, you can include it in your product, or your web
// site, or any other form where the code is actually being used. You
// may not put the plain javascript up on your site for download or
// include it in your javascript libraries for download. 
// If you wish to share this code with others, please just point them
// to the URL instead.
// Please DO NOT link directly to my .js files from your site. Copy
// the files to your server and use them there. Thank you.
// ===================================================================

/* 
Last modified: 02/24/2003

DESCRIPTION: This widget is used to select a color, in hexadecimal #RRGGBB 
form. It uses a color "swatch" to display the standard 216-color web-safe 
palette. The user can then click on a color to select it.

COMPATABILITY: See notes in AnchorPosition.js and PopupWindow.js.
Only the latest DHTML-capable browsers will show the color and hex values
at the bottom as your mouse goes over them.

USAGE:
// Create a new ColorPicker object using DHTML popup
var cp = new ColorPicker();

// Create a new ColorPicker object using Window Popup
var cp = new ColorPicker('window');

// Add a link in your page to trigger the popup. For example:
<A HREF="#" onClick="cp.show('pick');return false;" NAME="pick" ID="pick">Pick</A>

// Or use the built-in "select" function to do the dirty work for you:
<A HREF="#" onClick="cp.select(document.forms[0].color,'pick');return false;" NAME="pick" ID="pick">Pick</A>

// If using DHTML popup, write out the required DIV tag near the bottom
// of your page.
<SCRIPT LANGUAGE="JavaScript">cp.writeDiv()</SCRIPT>

// Write the 'pickColor' function that will be called when the user clicks
// a color and do something with the value. This is only required if you
// want to do something other than simply populate a form field, which is 
// what the 'select' function will give you.
function pickColor(color) {
	field.value = color;
	}

NOTES:
1) Requires the functions in AnchorPosition.js and PopupWindow.js

2) Your anchor tag MUST contain both NAME and ID attributes which are the 
   same. For example:
   <A NAME="test" ID="test"> </A>

3) There must be at least a space between <A> </A> for IE5.5 to see the 
   anchor tag correctly. Do not do <A></A> with no space.

4) When a ColorPicker object is created, a handler for 'onmouseup' is
   attached to any event handler you may have already defined. Do NOT define
   an event handler for 'onmouseup' after you define a ColorPicker object or
   the color picker will not hide itself correctly.
*/ 
ColorPicker_targetInput = null;
function ColorPicker_writeDiv() {
	document.writeln("<div id=\"colorPickerDiv\" style=\"position:absolute;visibility:hidden;\"> </div>");
}

function ColorPicker_show(anchorname) {
    this.showPopup(anchorname);
}

function ColorPicker_pickColor(color,obj) {
    obj.hidePopup();
    pickColor(color);
}

// A Default "pickColor" function to accept the color passed back from popup.
// User can over-ride this with their own function.
function pickColor(color) {
    if (ColorPicker_targetInput==null) {
        alert("Target Input is null, which means you either didn't use the 'select' function or you have no defined your own 'pickColor' function to handle the picked color!");
        return;
    }
    ColorPicker_targetInput.value = color;
}

// This function is the easiest way to popup the window, select a color, and
// have the value populate a form field, which is what most people want to do.
function ColorPicker_select(inputobj,linkname) {
    if (inputobj.type!="text" && inputobj.type!="hidden" && inputobj.type!="textarea") { 
        alert("colorpicker.select: Input object passed is not a valid form input object"); 
        window.ColorPicker_targetInput=null;
        return;
    }
    window.ColorPicker_targetInput = inputobj;
    this.show(linkname);
}
	
// This function runs when you move your mouse over a color block, if you have a newer browser
function ColorPicker_highlightColor(c) {
    var thedoc = (arguments.length>1)?arguments[1]:window.document;
    var d = thedoc.getElementById("colorPickerSelectedColor");
    d.style.backgroundColor = c;
    d = thedoc.getElementById("colorPickerSelectedColorValue");
    d.innerHTML = c;
}

function ColorPicker() {
    var windowMode = false;
    var pickerProperties = [];
    // Create a new PopupWindow object
    if (arguments.length==0) {
        var divname = "colorPickerDiv";
    }
    else if (arguments[0] == "window") {
        var divname = '';
        windowMode = true;
    }
    else {
        var divname = "colorPickerDiv";
    }
    
    if (divname != "") {        
        var cp = new PopupWindow(divname);
    }
    else {
        var cp = new PopupWindow();
        cp.setSize(181,158);    
    }
    
    if (arguments.length == 2) {
        pickerProperties = arguments[1];
    }
    // Object variables
    cp.currentValue = "#FFFFFF";
    
    // Method Mappings
    cp.writeDiv = ColorPicker_writeDiv;
    cp.highlightColor = ColorPicker_highlightColor;
    cp.show = ColorPicker_show;
    cp.select = ColorPicker_select;
    
    // Code to populate color picker window
    var colors = new Array("#000000","#000033","#000066","#000099","#0000CC","#0000FF",
                           "#330066","#330099","#3300CC","#3300FF","#660000","#660033",
                           "#6600CC","#6600FF","#990000","#990033","#990066","#990099",
                           "#9900CC","#9900FF","#CC0000","#CC0033","#CC0066","#CC0099",
                           "#CC00CC","#CC00FF","#FF0000","#FF0033","#FF0066","#FF0099",
                           "#FF00CC","#FF00FF","#003300","#003333","#003366","#003399",
                           "#0033CC","#0033FF","#333300","#333333","#333366","#333399",
                           "#3333CC","#3333FF","#663300","#663333","#663366","#663399",
                           "#6633CC","#6633FF","#993300","#993333","#993366","#993399",
                           "#9933CC","#9933FF","#CC3300","#CC3333","#CC3366","#CC3399",
                           "#CC33CC","#CC33FF","#FF3300","#FF3333","#FF3366","#FF3399",
                           "#FF33CC","#FF33FF","#006600","#006633","#006666","#006699",
                           "#0066CC","#0066FF","#336600","#336633","#336666","#336699",
                           "#3366CC","#3366FF","#666600","#666633","#666666","#666699",
                           "#6666CC","#6666FF","#996600","#996633","#996666","#996699",
                           "#9966CC","#9966FF","#CC6600","#CC6633","#CC6666","#CC6699",
                           "#CC66CC","#CC66FF","#FF6600","#FF6633","#FF6666","#FF6699",
                           "#FF66CC","#FF66FF","#009900","#009933","#009966","#009999",
                           "#0099CC","#0099FF","#339900","#339933","#339966","#339999",
                           "#3399CC","#3399FF","#669900","#669933","#669966","#669999",
                           "#6699CC","#6699FF","#999900","#999933","#999966","#999999",
                           "#9999CC","#9999FF","#CC9900","#CC9933","#CC9966","#CC9999",
                           "#CC99CC","#CC99FF","#FF9900","#FF9933","#FF9966","#FF9999",
                           "#FF99CC","#FF99FF","#00CC00","#00CC33","#00CC66","#00CC99",
                           "#00CCCC","#00CCFF","#33CC00","#33CC33","#33CC66","#33CC99",
                           "#33CCCC","#33CCFF","#66CC00","#66CC33","#66CC66","#66CC99",
                           "#66CCCC","#66CCFF","#99CC00","#99CC33","#99CC66","#99CC99",
                           "#99CCCC","#99CCFF","#CCCC00","#CCCC33","#CCCC66","#CCCC99",
                           "#CCCCCC","#CCCCFF","#FFCC00","#FFCC33","#660066","#660099",
                           "#FFCC66","#FFCC99","#FFCCCC","#FFCCFF","#00FF00","#00FF33",
                           "#00FF66","#00FF99","#00FFCC","#00FFFF","#33FF00","#33FF33",
                           "#33FF66","#33FF99","#33FFCC","#33FFFF","#66FF00","#66FF33",
                           "#66FF66","#66FF99","#66FFCC","#66FFFF","#330000","#330033",
                           "#99FF00","#99FF33","#99FF66","#99FF99","#99FFCC","#99FFFF",
                           "#CCFF00","#CCFF33","#CCFF66","#CCFF99","#CCFFCC","#CCFFFF",
                           "#FFFF00","#FFFF33","#FFFF66","#FFFF99","#FFFFCC","#FFFFFF");
    var total = colors.length;
    var width = 18;
    var cp_contents = "";
    var windowRef = (windowMode)?"window.opener.":"";
    if (windowMode) {
        cp_contents += '<html>\n<head>\n<title>' + pickerProperties.windowname + '</title>\n</head>\n';
        cp_contents += '<body marginwidth="0" marginheight="0" leftmargin="0" topmargin="0">\n<center>\n';
    }
    cp_contents += '<table style="border: 1px #000; border-spacing: 1px; padding: 0px; background-color: #000;">\n';
    var use_highlight = (document.getElementById || document.all)?true:false;
    for (var i=0; i<total; i++) {
        if ((i % width) == 0) { 
            cp_contents += "<tr>\n"; 
        }
        if (use_highlight) { 
            var mo = 'onMouseOver="'+windowRef+'ColorPicker_highlightColor(\''+colors[i]+'\',window.document)"'; 
        }
        else { 
            mo = ""; 
        }
   
        cp_contents += '<td ' + mo + 'onclick="'+windowRef+'ColorPicker_pickColor(\''+colors[i]+'\','+windowRef+'window.popupWindowObjects['+cp.index+']); ' + windowRef + 'window.ExecutePingBackOf' + pickerProperties.fieldID + '(); return false;" style="background-color: ' + colors[i] + '; text-decoration: none; width: 7px; height: 10px; font-size: 2px;"></td>';
        if ( ((i+1)>=total) || (((i+1) % width) == 0)) { 
            cp_contents += "</tr>";
        }
    }
    // If the browser supports dynamically changing TD cells, add the fancy stuff
    if (document.getElementById) {
        var width1 = Math.floor(width/2);
        var width2 = width = width1;
        cp_contents += "<tr>";
        cp_contents += '<td colspan="' + width1 + '" style=" background-color: #fff;" id="colorPickerSelectedColor">';
        cp_contents += '&nbsp;</td><td colspan="' + width2 + '" style="background-color: #fff; font: ' + pickerProperties.fontStyle + '; text-align: center;" id="colorPickerSelectedColorValue">';
        cp_contents += '#FFFFFF</td></tr>';
    }
    cp_contents += "</table>";
    if (windowMode) {
        cp_contents += "</center></body></html>";
    }
    // end populate code
    // Write the contents to the popup object
    cp.populate(cp_contents+"\n");
    // Move the table down a bit so you can see it
    cp.offsetY = 25;
    cp.autoHide();
    return cp;
}
