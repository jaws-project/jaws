/**
 * Jms Javascript actions
 *
 * @category   Ajax
 * @package    Jms
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var JmsCallback = {
    installgadget: function(response) {
        if (response[0]['css'] == 'notice-message') {
            $('only_show').selectedIndex = 0;
            updateView();
            editGadget(selectedGadget);
        }
        showResponse(response);
    },

    installplugin: function(response) {
        if (response[0]['css'] == 'notice-message') {
            $('only_show').selectedIndex = 0;
            updateView();
            editPlugin(selectedPlugin);
        }
        showResponse(response);
    },

    uninstallgadget: function(response) {
        if (response[0]['css'] == 'notice-message') {
            $('only_show').selectedIndex = 1;
            updateView();
            editGadget(selectedGadget);
        }
        showResponse(response);
    },

    uninstallplugin: function(response) {
        if (response[0]['css'] == 'notice-message') {
            $('only_show').selectedIndex = 1;
            updateView();
            editPlugin(selectedPlugin);
        }
        showResponse(response);
    },

    purgegadget: function(response) {
        if (response[0]['css'] == 'notice-message') {
            $('only_show').selectedIndex = 1;
            updateView();
            editGadget(selectedGadget);
        }
        showResponse(response);
    },

    updategadget: function(response) {
        if (response[0]['css'] == 'notice-message') {
            $('only_show').selectedIndex = 0;
            updateView();
            editGadget(selectedGadget);
        }
        showResponse(response);
    },

    updatepluginusage: function(response) {
        stopPluginUsage();
        showResponse(response);
    }
}

/**
 * Show the buttons depending on the current tab and
 * the items to show
 */
function showButtons()
{
    if (pluginsMode == false) {
        switch($('only_show').value) {
        case 'notinstalled':
            $('install_button').style.display   = 'block';
            break;
        case 'installed':
            $('uninstall_button').style.display = 'block';
            $('purge_button').style.display     = 'block';
            break;
        case 'outdated':
            $('update_button').style.display    = 'block';
            break;
        }
    } else {
        switch($('only_show').value) {
        case 'notinstalled':
            $('install_button').style.display   = 'block';
            break;
        case 'installed':
            if (editingPlugins == true) {
                $('plugin_saveusage').style.display = 'block';
                $('plugin_stopusage').style.display = 'block';
            } else {
                $('uninstall_button').style.display = 'block';
                $('plugin_usage').style.display = 'block';
            }
            break;
        }
    }
}

/**
 * Edits a gadget showing basic info about it
 */
function editGadget(gadget)
{
    if (gadget.blank()) {
        return false;
    }

    if (gadget == '-') {
        return false;
    }

    cleanWorkingArea(true);

    var gadgetInfo = jmsSync.getgadgetinfo(gadget);
    if (gadgetInfo == null) {
        return false; //Check
    }
    selectedGadget = gadget;
    $('work_area').innerHTML = gadgetInfo;
    showButtons();
}

/**
 * Shows basic plugin info
 */
function editPlugin(plugin)
{
    if (plugin.blank()) {
        return false;
    }

    editingPlugins = false;
    cleanWorkingArea(true);
    if (plugin == '-') {
        return false;
    }

    var pluginInfo = jmsSync.getplugininfo(plugin);
    if (pluginInfo == null) {
        return false; //Check
    }

    selectedPlugin = plugin;
    $('work_area').innerHTML = pluginInfo;
    showButtons();
}

/**
 * Clean the working area
 */
function cleanWorkingArea(hideButtons)
{
    $('work_area').innerHTML = '';
    if (hideButtons != undefined) {
        if (hideButtons == true) {
            var buttons = new Array('purge_button', 'uninstall_button',
                                    'install_button', 'update_button',
                                    'plugin_usage', 'plugin_saveusage',
                                    'plugin_stopusage'
                                    );
            for(var i=0; i<buttons.length; i++) {
                if ($(buttons[i]) != undefined) {
                    $(buttons[i]).style.display = 'none';
                }
            }
        }
    }
}

/**
 * Check which gadget has been selected, if 'use_always' was checked
 * and user decided to also select a gadget then 'use_always' become off
 */
function selectGadgetPlugin(checkbox)
{
    if (checkbox.checked == true) {
        $('use_always').checked = false;
        return true;
    }
}

/**
 * Check if user has selected all gadgets to be used
 */
function checkAllGadgets(checkbox)
{
    if (checkbox.checked == true) {
        //inputs should be located in second container (0 => first, 1 => second)
        var gadgetsCont = $$('.webfx-tree-container')[1];
        if (gadgetsCont != undefined) {
            var inputs  = gadgetsCont.getElementsByTagName('input');
            for (var i=0; i<inputs.length; i++) {
                inputs[i].checked = false;
            }
        }
        aItem.collapseAll();
    } else {
        aItem.expandAll();
    }
}

/**
 * show the plugin usage UI
 */
function pluginUsage()
{
    cleanWorkingArea(true);

    tree = new WebFXTree(gadgetsMsg);

    tree.openIcon = 'gadgets/Jms/images/gadgets.png';
    tree.icon = 'gadgets/Jms/images/gadgets.png';

    var gadgets = jmsSync.getgadgetsofplugin($('plugins_combo').value);

    var useAllTime = jmsSync.usealways($('plugins_combo').value);

    var div = document.createElement('div');

    var chkbox = document.createElement('input');
    chkbox.setAttribute('type', 'checkbox');
    chkbox.setAttribute('name', 'use_always');
    chkbox.setAttribute('value', 'use_always');
    chkbox.setAttribute('id', 'use_always');
    if (useAllTime == true) {
        chkbox.defaultChecked = true;
        chkbox.setAttribute('checked', true);
    }
    chkbox.onclick = function() {
        checkAllGadgets(this);
    }
    chkbox.setAttribute('changed', false);

    var label = document.createElement('label');
    label.htmlFor = 'use_always';
    label.appendChild(document.createTextNode(useAlways));

    div.appendChild(chkbox);
    div.appendChild(label);

    aItem = new WebFXTreeItem(div.innerHTML);
    tree.add(aItem);

    for(gadget in gadgets) {
        if (typeof(gadgets[gadget]) == 'function') {
            continue;
        }

        //Create checkbox with its label and all that sexy stuff
        var div = document.createElement('div');

        var chkbox = document.createElement('input');
        chkbox.setAttribute('type', 'checkbox');
        chkbox.setAttribute('name', 'gadgets[]');
        chkbox.setAttribute('value', gadgets[gadget]['gadget']);
        if (gadgets[gadget]['value'] == true) {
            chkbox.defaultChecked = true;
            chkbox.setAttribute('checked', true);
        }
        //Little trick to know which values have changed their values
        chkbox.onclick = function() {
            selectGadgetPlugin(this);
        }
        chkbox.setAttribute('id', gadgets[gadget]['gadget']);

        var label = document.createElement('label');
        label.htmlFor = gadgets[gadget]['gadget'];
        label.appendChild(document.createTextNode(gadgets[gadget]['gadget_t']));

        div.appendChild(chkbox);
        div.appendChild(label);

        var gadgetItem = new WebFXTreeItem(div.innerHTML);
        aItem.add(gadgetItem);
    }

    $('work_area').innerHTML = tree.toString();
    editingPlugins = true;
    showButtons();
}

/**
 * Stops editing the plugin
 */
function stopPluginUsage()
{
    var plugin = $('plugins_combo').value;
    editPlugin(plugin)
}

/**
 * Saves the plugin usage properties
 */
function savePluginUsage()
{
    var plugin  = $('plugins_combo').value;

    if ($('use_always').checked == true) {
        jmsAsync.updatepluginusage(plugin, '*');
        return true;
    }

    var inputs = $('work_area').getElementsByTagName('input');
    var gadgets   = new Array();
    var counter = 0;
    for(var i=0; i<inputs.length; i++) {
        if (inputs[i].checked) {
            gadgets[counter] = inputs[i].value;
            counter++;
        }
    }

    jmsAsync.updatepluginusage(plugin, gadgets);
}

/**
 * Installs a component
 */
function installComponent()
{
    if (pluginsMode == false) {
        jmsAsync.installgadget(selectedGadget);
    } else {
        jmsAsync.installplugin(selectedPlugin);
    }
}

/**
 * Uninstall component
 */
function uninstallComponent()
{
    var answer = confirm(confirmUninstallComponent);
    if (answer) {
        if (pluginsMode == false) {
            jmsAsync.uninstallgadget(selectedGadget);
        } else {
            jmsAsync.uninstallplugin(selectedPlugin);
        }
    }
}

/**
 * Uninstall component
 */
function purgeComponent()
{
    if (pluginsMode == false) {
        var answer = confirm(confirmPurgeComponent);
        if (answer) {
            jmsAsync.purgegadget(selectedGadget);
        }
    }
}

/**
 * Fill the gadgets combo
 */
function getGadgets()
{
    resetCombo($('gadgets_combo'));
    var gadgetsList = jmsSync.getgadgets($('only_show').value);
    var found       = false;
    for(gadget in gadgetsList) {
        if (gadgetsList[gadget]['realname'] == undefined) {
            continue;
        }
        var op   = new Option();
        op.value = gadgetsList[gadget]['realname'];
        op.text  = gadgetsList[gadget]['name'];
        op.title = gadgetsList[gadget]['description'];
        $('gadgets_combo').options[$('gadgets_combo').options.length] = op;
        found = true;
    }

    if (found == false) {
        var op   = new Option();
        op.value = '-';
        op.text  = noAvailableData;
        $('gadgets_combo').options[$('gadgets_combo').options.length] = op;
    }
    paintCombo($('gadgets_combo'), oddColor, evenColor);
}

/**
 * Fill the plugins combo
 */
function getPlugins(reset)
{
    resetCombo($('plugins_combo'));
    var pluginsList = jmsSync.getplugins($('only_show').value);
    var found = false;
    for(plugin in pluginsList) {
        if (pluginsList[plugin]['realname'] == undefined) {
            continue;
        }
        var op   = new Option();
        op.value = pluginsList[plugin]['realname'];
        op.text  = pluginsList[plugin]['name'];
        op.title = pluginsList[plugin]['description'];
        $('plugins_combo').options[$('plugins_combo').options.length] = op;
        found = true;
    }

    if (found == false) {
        var op   = new Option();
        op.value = '-';
        op.text  = noAvailableData;
        $('plugins_combo').options[$('plugins_combo').options.length] = op;
    }
    paintCombo($('plugins_combo'), oddColor, evenColor);
}

/**
 * Updates the gadget/plugins view
 */
function updateView()
{
    cleanWorkingArea(true);
    if (pluginsMode == false) {
        getGadgets();
    } else {
        editingPlugins = false;
        getPlugins();
    }
}

var jmsAsync = new jmsadminajax(JmsCallback);
jmsAsync.serverErrorFunc = Jaws_Ajax_ServerError;
jmsAsync.onInit = showWorkingNotification;
jmsAsync.onComplete = hideWorkingNotification;

var jmsSync  = new jmsadminajax();
jmsSync.serverErrorFunc = Jaws_Ajax_ServerError;
jmsSync.onInit = showWorkingNotification;
jmsSync.onComplete = hideWorkingNotification;

var selectedGadget = null;
var selectedPlugin = null;

var pluginsMode = false;

var editingPlugins = false;

var evenColor = '#fff';
var oddColor  = '#edf3fe';

var tree = null;
var aItem = null;
