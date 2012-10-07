/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('{pluginName}', '');

function TinyMCE_{pluginName}_initInstance(inst) {
}

function TinyMCE_{pluginName}_getControlHTML(control_name) {
    switch (control_name) {
    case "{pluginName}":
        var et = "{pluginElement}";
        return et;
        break;
    }
    return '';
}
