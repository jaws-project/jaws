/**
 * This function is based on our classic JawsEditor but uses tinyMCE to replace
 * strings
 */
function insertTags(txtarea, tagOpen, tagClose, sampleText) {
    var inst     = tinyMCE.getInstanceById(txtarea);
    var focusElm = inst.getFocusElement();
    var doc      = inst.getDoc();
    
    var selectedText = inst.selection.getSelectedText();

    var the_length=tagOpen.length;
    var last_char=tagOpen.charAt(the_length-1);
   
    var tagOpenSep  = "";
    var tagCloseSep = "";
    
    if (selectedText == '') {
        tinyMCE.execInstanceCommand(txtarea, 'mceInsertContent', true, tagOpen  + "<p></p>" + tagClose, true);
    } else {
        tinyMCE.execCommand('mceReplaceContent', true, tagOpen+selectedText+tagClose);
    }
    tinyMCE.selectedInstance.repaint()
    return true;
}

/**
 * Save callback
 */
function jaws_save_callback(el, content, body) {
    content = content.replace(new RegExp('(<p[^>]+>.*?)</p>', 'mg'), '$1</p#>');
    content = content.replace(new RegExp('&amp;', 'g'), '&'); //Make valid code
    
    //Get it ready for paragraph processing
    content = content.replace(new RegExp('[\\s]*<p>[\\s]*', 'mgi'), '');
    content = content.replace(new RegExp('[\\s]*</p>[\\s]*', 'mgi'), '\n\n');
    content = content.replace(new RegExp('\\n\\s*\\n\\s*\\n*', 'mgi'), '\n\n');
    content = content.replace(new RegExp('\\s*<br ?/?>\\s*', 'gi'), '\n');
    
    //Fix some block element newline issues (autop)
    var blocklist = 'blockquote|ul|ol|li|table|thead|tr|th|td|div|h\\d|pre';
    content = content.replace(new RegExp('\\s*<(('+blocklist+') ?[^>]*)\\s*>', 'mg'), '\n<$1>');
    content = content.replace(new RegExp('\\s*</('+blocklist+')>\\s*', 'mg'), '</$1>\n');
    content = content.replace(new RegExp('<li>', 'g'), '\t<li>');
    
    //Unmark special paragraph closing tags
    content = content.replace(new RegExp('</p#>', 'g'), '</p>\n');
    content = content.replace(new RegExp('\\s*(<p[^>]+>.*</p>)', 'mg'), '\n$1');
    
    //Trim any whitespace
    content = content.replace(new RegExp('^\\s*', ''), '');
    content = content.replace(new RegExp('\\s*$', ''), '');
    
    return content;
}

function myCustomCleanup(type, content) {
    switch (type) {
    case "get_from_editor":
        rx = /\[.+?\](.*?)\[\/.+?\]/g;
        rx2 = /\[.+?\](.*?)\[\/.+?\]/;
        var contentMatches = content.match(rx);
        if (contentMatches != null) {
            for(var i=0; i < contentMatches.length; i++) {
                var bbCodeMatches = contentMatches[i].match(rx2);
                if (bbCodeMatches != null) {
                    if (bbCodeMatches[1] != undefined) {
                        //TODO: Only delete the last <br /> of each 'line' (there are no lines, no \n separator)
                        var cleanBBCode = bbCodeMatches[1].replace(/<br \/>/mg, "\n");
                        cleanBBCode = bbCodeMatches[0].replace(bbCodeMatches[1], cleanBBCode);
                        content = content.replace(bbCodeMatches[0], cleanBBCode);
                    }
                }
            }
        }
        break;
        	
    }

    return content;
}

